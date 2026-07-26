<?php

namespace App\Console\Commands;

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class SyncMediaStatus extends Command
{
    protected $signature = 'media:sync-status
        {--batch= : Batch ID to inspect (defaults to the most recent media-sync batch)}
        {--watch : Refresh every 10 seconds until the batch finishes}';

    protected $description = 'Show progress of a queued media:sync-to-cloud batch';

    public function handle(): int
    {
        $batchId = $this->option('batch') ?: $this->latestBatchId();

        if (! $batchId) {
            $this->warn('No media-sync batch found. Dispatch one with: php artisan media:sync-to-cloud --queue');

            return self::FAILURE;
        }

        do {
            $batch = Bus::findBatch($batchId);

            if (! $batch) {
                $this->error("Batch [{$batchId}] not found.");

                return self::FAILURE;
            }

            $this->render($batch);

            if (! $this->option('watch') || $batch->finished()) {
                break;
            }

            sleep(10);
            $this->newLine();
        } while (true);

        return self::SUCCESS;
    }

    protected function render(Batch $batch): void
    {
        $this->table(['Metric', 'Value'], [
            ['Batch ID', $batch->id],
            ['Total jobs', number_format($batch->totalJobs)],
            ['Processed', number_format($batch->processedJobs())],
            ['Pending', number_format($batch->pendingJobs)],
            ['Failed', number_format($batch->failedJobs)],
            ['Progress', $batch->progress().'%'],
            ['Cancelled', $batch->cancelled() ? 'yes' : 'no'],
            ['Finished', $batch->finished() ? 'yes' : 'no'],
        ]);

        if ($batch->failedJobs > 0) {
            $this->warn(
                "{$batch->failedJobs} jobs failed. Inspect them with `php artisan queue:failed`, then "
                .'re-run `php artisan media:sync-to-cloud --queue` — it only dispatches what is still missing.'
            );
        }

        if ($batch->finished() && $batch->failedJobs === 0) {
            $this->info('Batch complete. Run `php artisan media:verify` before switching MEDIA_DISK.');
        }
    }

    protected function latestBatchId(): ?string
    {
        return DB::table('job_batches')
            ->where('name', 'media-sync')
            ->orderByDesc('created_at')
            ->value('id');
    }
}
