<?php

namespace App\Jobs;

use App\Services\MediaStorage;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Copies a chunk of media files from one disk to another.
 *
 * Work is batched rather than one job per file because the queue table would
 * otherwise carry a row for every photo. A chunk that fails part-way is
 * retried whole, which re-uploads the files it already copied — harmless,
 * since writing the same key twice overwrites it.
 */
class UploadMediaChunk implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 3;

    public int $timeout = 900;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 120];

    /**
     * @param  array<int, string>  $paths
     */
    public function __construct(
        public array $paths,
        public string $fromDisk,
        public string $toDisk,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $from = Storage::disk($this->fromDisk);
        $to = Storage::disk($this->toDisk);
        $options = MediaStorage::writeOptions();

        foreach ($this->paths as $path) {
            if (! $from->exists($path)) {
                // The database references a file that is already gone from the
                // source disk. Nothing to copy, and failing the chunk over it
                // would block every other file in the chunk.
                Log::warning('Media sync: source file missing, skipped.', [
                    'path' => $path,
                    'disk' => $this->fromDisk,
                ]);

                continue;
            }

            $stream = $from->readStream($path);

            if (! is_resource($stream)) {
                throw new RuntimeException("Unable to open a read stream for [{$path}] on [{$this->fromDisk}].");
            }

            try {
                $to->writeStream($path, $stream, $options);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }
    }
}
