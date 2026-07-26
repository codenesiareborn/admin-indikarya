<?php

namespace App\Console\Commands;

use App\Services\MediaInventory;
use App\Services\MediaStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncMediaToCloud extends Command
{
    protected $signature = 'media:sync-to-cloud
        {--from= : Disk to copy from (defaults to the current media disk)}
        {--to=r2 : Disk to copy to}
        {--since= : Only consider records created on or after this date (Y-m-d)}
        {--chunk=500 : Rows to load per batch}
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

        $copied = $skipped = $missing = $failed = 0;

        foreach (MediaInventory::sources() as $source) {
            $total = MediaInventory::count($source, $since);
            $this->newLine();
            $this->line("<comment>{$source['label']}</comment> ({$total} rows)");

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            MediaInventory::each($source, function (string $path) use (
                $from, $to, $dryRun, &$copied, &$skipped, &$missing, &$failed, $bar
            ): void {
                $bar->advance();

                try {
                    if ($to->exists($path)) {
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
}
