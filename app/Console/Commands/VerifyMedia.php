<?php

namespace App\Console\Commands;

use App\Services\MediaInventory;
use Illuminate\Console\Command;

class VerifyMedia extends Command
{
    protected $signature = 'media:verify
        {--local= : Local disk to check (defaults to "public")}
        {--cloud=r2 : Cloud disk to check}
        {--since= : Only consider records created on or after this date (Y-m-d)}
        {--chunk=500 : Rows to load per batch}
        {--show-missing : List every path missing from the cloud disk}';

    protected $description = 'Report media coverage across the local and cloud disks; the go/no-go gate before cutover';

    public function handle(): int
    {
        $localName = $this->option('local') ?: 'public';
        $cloudName = $this->option('cloud');
        $since = $this->option('since');
        $chunk = max(1, (int) $this->option('chunk'));

        $this->info("Verifying media across local [{$localName}] and cloud [{$cloudName}].");

        $this->line('Indexing both disks...');
        $cloudIndex = MediaInventory::indexDisk($cloudName);
        $localIndex = MediaInventory::indexDisk($localName);
        $this->line(sprintf(
            '  %s objects on [%s], %s on [%s].',
            number_format(count($cloudIndex)), $cloudName,
            number_format(count($localIndex)), $localName
        ));

        $rows = [];
        $totalMissingFromCloud = 0;
        $totalOrphaned = 0;
        $missingPaths = [];

        foreach (MediaInventory::sources() as $source) {
            $total = MediaInventory::count($source, $since);
            $onCloud = $onLocal = $onNeither = 0;

            $bar = $this->output->createProgressBar($total);
            $this->newLine();
            $this->line("<comment>{$source['label']}</comment>");
            $bar->start();

            MediaInventory::each($source, function (string $path) use (
                $cloudIndex, $localIndex, $bar, &$onCloud, &$onLocal, &$onNeither, &$missingPaths
            ): void {
                $bar->advance();

                $inCloud = isset($cloudIndex[$path]);
                $inLocal = isset($localIndex[$path]);

                if ($inCloud) {
                    $onCloud++;
                } else {
                    $missingPaths[] = $path;
                }

                if ($inLocal) {
                    $onLocal++;
                }

                if (! $inCloud && ! $inLocal) {
                    $onNeither++;
                }
            }, $since, $chunk);

            $bar->finish();
            $this->newLine();

            $missingFromCloud = $total - $onCloud;
            $totalMissingFromCloud += $missingFromCloud;
            $totalOrphaned += $onNeither;

            $rows[] = [
                $source['label'],
                $total,
                $onCloud,
                $onLocal,
                $missingFromCloud,
                $onNeither,
            ];
        }

        $this->newLine();
        $this->table(
            ['Source', 'Rows', 'On cloud', 'On local', 'Missing from cloud', 'On neither'],
            $rows
        );

        if ($this->option('show-missing') && $missingPaths !== []) {
            $this->newLine();
            $this->line('<comment>Paths missing from the cloud disk:</comment>');
            foreach ($missingPaths as $path) {
                $this->line("  {$path}");
            }
        }

        if ($totalOrphaned > 0) {
            $this->warn(
                "{$totalOrphaned} rows reference files that exist on neither disk. "
                .'These were already broken before the migration and cannot be recovered by syncing.'
            );
        }

        if ($totalMissingFromCloud > 0) {
            $this->newLine();
            $this->error(
                "NO-GO: {$totalMissingFromCloud} referenced files are not on [{$cloudName}]. "
                .'Run media:sync-to-cloud and verify again before switching MEDIA_DISK.'
            );

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("GO: every referenced media file is present on [{$cloudName}]. Safe to switch MEDIA_DISK.");

        return self::SUCCESS;
    }
}
