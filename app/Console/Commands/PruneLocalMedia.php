<?php

namespace App\Console\Commands;

use App\Services\MediaInventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneLocalMedia extends Command
{
    protected $signature = 'media:prune-local
        {--local=public : Local disk to prune}
        {--cloud=r2 : Cloud disk that must already hold the file}
        {--since= : Only consider records created on or after this date (Y-m-d)}
        {--chunk=500 : Rows to load per batch}
        {--force : Actually delete; without this the command only reports}';

    protected $description = 'Delete local media files that are confirmed present on the cloud disk, to reclaim server disk space';

    public function handle(): int
    {
        $localName = $this->option('local');
        $cloudName = $this->option('cloud');
        $force = (bool) $this->option('force');
        $since = $this->option('since');
        $chunk = max(1, (int) $this->option('chunk'));

        if ($localName === $cloudName) {
            $this->error("Local and cloud disks are both [{$localName}]. Refusing to run.");

            return self::FAILURE;
        }

        $local = Storage::disk($localName);
        $cloud = Storage::disk($cloudName);

        if (! $force) {
            $this->warn('[DRY RUN] Nothing will be deleted. Pass --force to actually prune.');
        } else {
            $this->warn("This will permanently delete files from [{$localName}].");

            if (! $this->confirm("Delete local media that is already on [{$cloudName}]?", false)) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $deleted = $keptNotOnCloud = $alreadyGone = $failed = 0;
        $bytesFreed = 0;

        foreach (MediaInventory::sources() as $source) {
            $total = MediaInventory::count($source, $since);
            $this->newLine();
            $this->line("<comment>{$source['label']}</comment> ({$total} rows)");

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            MediaInventory::each($source, function (string $path) use (
                $local, $cloud, $force, $bar, &$deleted, &$keptNotOnCloud, &$alreadyGone, &$failed, &$bytesFreed
            ): void {
                $bar->advance();

                try {
                    if (! $local->exists($path)) {
                        $alreadyGone++;

                        return;
                    }

                    // Never delete a local file unless the cloud copy is confirmed
                    // to exist. This check is the entire safety of the command.
                    if (! $cloud->exists($path)) {
                        $keptNotOnCloud++;

                        return;
                    }

                    $bytesFreed += (int) $local->size($path);

                    if ($force) {
                        $local->delete($path);
                    }

                    $deleted++;
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
            [$force ? 'Deleted locally' : 'Would delete', $deleted],
            ['Kept (not on cloud yet)', $keptNotOnCloud],
            ['Already absent locally', $alreadyGone],
            ['Failed', $failed],
            [$force ? 'Space freed' : 'Space that would be freed', $this->formatBytes($bytesFreed)],
        ]);

        if ($keptNotOnCloud > 0) {
            $this->warn("{$keptNotOnCloud} files were kept because they are not on [{$cloudName}]. Run media:sync-to-cloud first.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
