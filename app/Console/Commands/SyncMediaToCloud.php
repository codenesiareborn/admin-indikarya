<?php

namespace App\Console\Commands;

use App\Jobs\UploadMediaChunk;
use App\Services\MediaInventory;
use App\Services\MediaStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class SyncMediaToCloud extends Command
{
    protected $signature = 'media:sync-to-cloud
        {--from= : Disk to copy from (defaults to the current media disk)}
        {--to=r2 : Disk to copy to}
        {--since= : Only consider records created on or after this date (Y-m-d)}
        {--chunk=500 : Rows to load per batch}
        {--queue : Dispatch the uploads to the queue instead of copying inline}
        {--queue-name=media : Queue to dispatch onto}
        {--batch-size=200 : Files per queued job}
        {--dry-run : Report what would be uploaded without writing anything}';

    protected $description = 'Copy every database-referenced media file to the cloud disk, skipping files already there';

    public function handle(): int
    {
        $fromName = $this->option('from') ?: config('media.disk');
        $toName = $this->option('to');
        $dryRun = (bool) $this->option('dry-run');
        $since = $this->option('since');
        $chunk = max(1, (int) $this->option('chunk'));

        if ($fromName === $toName) {
            $this->error("Source and destination disks are both [{$fromName}]. Nothing to do.");

            return self::FAILURE;
        }

        $from = Storage::disk($fromName);
        $to = Storage::disk($toName);

        $this->info(sprintf(
            '%sSyncing media from [%s] to [%s]%s.',
            $dryRun ? '[DRY RUN] ' : '',
            $fromName,
            $toName,
            $since ? " for records created since {$since}" : ''
        ));

        $this->line("Indexing [{$toName}]...");
        $destinationIndex = MediaInventory::indexDisk($toName);
        $this->line('  '.number_format(count($destinationIndex)).' objects already present.');

        if ($this->option('queue')) {
            return $this->dispatchToQueue($fromName, $toName, $destinationIndex, $since, $chunk);
        }

        $copied = $skipped = $missing = $failed = 0;

        foreach (MediaInventory::sources() as $source) {
            $total = MediaInventory::count($source, $since);
            $this->newLine();
            $this->line("<comment>{$source['label']}</comment> ({$total} rows)");

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            MediaInventory::each($source, function (string $path) use (
                $from, $to, $dryRun, &$destinationIndex, &$copied, &$skipped, &$missing, &$failed, $bar
            ): void {
                $bar->advance();

                try {
                    if (isset($destinationIndex[$path])) {
                        $skipped++;

                        return;
                    }

                    if (! $from->exists($path)) {
                        // The row points at a file that is already gone from the
                        // source disk. Pre-existing damage; a sync cannot fix it.
                        $missing++;
                        $this->newLine();
                        $this->warn("  missing on source: {$path}");

                        return;
                    }

                    if ($dryRun) {
                        $copied++;

                        return;
                    }

                    $stream = $from->readStream($path);

                    if (! is_resource($stream)) {
                        throw new \RuntimeException('Unable to open a read stream.');
                    }

                    try {
                        $to->writeStream($path, $stream, MediaStorage::writeOptions());
                    } finally {
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }

                    $destinationIndex[$path] = true;
                    $copied++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->newLine();
                    $this->error("  failed: {$path} — {$e->getMessage()}");
                }
            }, $since, $chunk);

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->table(['Result', 'Count'], [
            [$dryRun ? 'Would copy' : 'Copied', $copied],
            ['Already present', $skipped],
            ['Missing on source', $missing],
            ['Failed', $failed],
        ]);

        if ($failed > 0) {
            $this->error('Some files failed to upload. Re-run the command — it is safe to repeat.');

            return self::FAILURE;
        }

        if ($missing > 0) {
            $this->warn("{$missing} rows reference files that no longer exist on [{$fromName}]. Review before cutover.");
        }

        return self::SUCCESS;
    }

    /**
     * Hand the outstanding uploads to the queue as a trackable batch.
     *
     * Existence is resolved here, once, against the index already in memory,
     * so the queued jobs carry only work that actually needs doing and never
     * probe the destination themselves.
     *
     * @param  array<string, true>  $destinationIndex
     */
    protected function dispatchToQueue(
        string $fromName,
        string $toName,
        array $destinationIndex,
        ?string $since,
        int $chunk,
    ): int {
        $batchSize = max(1, (int) $this->option('batch-size'));
        $queueName = $this->option('queue-name');

        $pending = [];
        $skipped = 0;

        foreach (MediaInventory::sources() as $source) {
            MediaInventory::each($source, function (string $path) use ($destinationIndex, &$pending, &$skipped): void {
                if (isset($destinationIndex[$path])) {
                    $skipped++;

                    return;
                }

                $pending[$path] = true;
            }, $since, $chunk);
        }

        $pending = array_keys($pending);

        $this->line('  '.number_format($skipped).' already on the destination, '.number_format(count($pending)).' to upload.');

        if ($pending === []) {
            $this->info('Nothing to dispatch — the destination is already complete.');

            return self::SUCCESS;
        }

        $jobs = [];
        foreach (array_chunk($pending, $batchSize) as $paths) {
            $jobs[] = new UploadMediaChunk($paths, $fromName, $toName);
        }

        // allowFailures keeps one bad chunk from cancelling the rest; anything
        // that still fails after its retries lands in failed_jobs, and
        // media:verify is what decides whether the result is good enough.
        $batch = Bus::batch($jobs)
            ->name('media-sync')
            ->onQueue($queueName)
            ->allowFailures()
            ->dispatch();

        $this->newLine();
        $this->info(sprintf(
            'Dispatched %s jobs (%s files) to queue [%s].',
            number_format(count($jobs)),
            number_format(count($pending)),
            $queueName
        ));
        $this->line("  Batch ID: {$batch->id}");
        $this->newLine();
        $this->line('Start a worker if one is not already running:');
        $this->line("  php artisan queue:work --queue={$queueName} --tries=3 --timeout=900");
        $this->line('Track progress with:');
        $this->line('  php artisan media:sync-status');

        return self::SUCCESS;
    }
}
