<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Single entry point for reading and writing user-uploaded media.
 *
 * Every photo path stored in the database is relative to the media disk
 * (e.g. "attendances/check_in_2026-01-27_....jpg"), never absolute and never
 * prefixed with "storage/". That is what lets the underlying disk change
 * without touching a single database row.
 */
class MediaStorage
{
    /**
     * Store an uploaded file and return its disk-relative path.
     *
     * A failure on the primary disk aborts with an exception so the caller
     * never persists a row pointing at a file that was not written. A failure
     * on the mirror disk is logged and swallowed: the mirror is a temporary
     * bridge during the cloud migration, and `media:sync-to-cloud` backfills
     * anything it misses.
     */
    public static function store(UploadedFile $file, string $directory, string $filename): string
    {
        $path = static::primaryDisk()->putFileAs($directory, $file, $filename, static::writeOptions());

        if ($path === false) {
            throw new RuntimeException(
                "Failed to store [{$directory}/{$filename}] on media disk [".static::primaryDiskName().'].'
            );
        }

        static::mirror($file, $path);

        return $path;
    }

    /**
     * Build a public URL for a stored path.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return static::primaryDisk()->url($path);
    }

    /**
     * Delete a stored file from the primary disk and, if configured, the mirror.
     */
    public static function delete(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        $deleted = static::primaryDisk()->exists($path)
            && static::primaryDisk()->delete($path);

        if ($mirror = static::mirrorDisk()) {
            try {
                if ($mirror->exists($path)) {
                    $mirror->delete($path);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete media from mirror disk.', [
                    'path' => $path,
                    'disk' => config('media.mirror_disk'),
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        return $deleted;
    }

    /**
     * Determine whether a stored file exists on the primary disk.
     */
    public static function exists(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        return static::primaryDisk()->exists($path);
    }

    /**
     * Options applied to every media write.
     *
     * Ignored by the local driver, honoured by S3-compatible drivers.
     *
     * @return array<string, string>
     */
    public static function writeOptions(): array
    {
        $cacheControl = config('media.cache_control');

        return filled($cacheControl) ? ['CacheControl' => $cacheControl] : [];
    }

    public static function primaryDiskName(): string
    {
        return config('media.disk');
    }

    public static function primaryDisk(): FilesystemAdapter
    {
        return Storage::disk(static::primaryDiskName());
    }

    /**
     * The mirror disk, or null when mirroring is switched off.
     *
     * A misconfigured disk name resolves to null rather than throwing: the
     * mirror is an optional migration aid, and a typo in MEDIA_MIRROR_DISK
     * must never be able to take down uploads.
     */
    public static function mirrorDisk(): ?FilesystemAdapter
    {
        $disk = config('media.mirror_disk');

        if (blank($disk) || $disk === static::primaryDiskName()) {
            return null;
        }

        try {
            return Storage::disk($disk);
        } catch (\Throwable $e) {
            Log::warning('Media mirror disk is not configured; mirroring is disabled.', [
                'disk' => $disk,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Best-effort copy of a freshly uploaded file to the mirror disk.
     */
    protected static function mirror(UploadedFile $file, string $path): void
    {
        $mirror = static::mirrorDisk();

        if (! $mirror) {
            return;
        }

        try {
            $stream = fopen($file->getRealPath(), 'rb');

            if ($stream === false) {
                throw new RuntimeException('Unable to reopen the uploaded file for mirroring.');
            }

            try {
                $mirror->writeStream($path, $stream, static::writeOptions());
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to mirror media upload; run media:sync-to-cloud to backfill.', [
                'path' => $path,
                'disk' => config('media.mirror_disk'),
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
