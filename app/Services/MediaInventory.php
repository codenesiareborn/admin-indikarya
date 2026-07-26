<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Patrol;
use App\Models\TaskSubmission;
use Illuminate\Database\Eloquent\Model;

/**
 * Enumerates every media path referenced by the database.
 *
 * The media:* commands walk the database rather than the storage directory on
 * purpose: a file on disk that no row points at is dead weight, while a row
 * pointing at a file that is missing from the cloud is the failure that would
 * break the application after cutover. Only the latter matters here.
 */
class MediaInventory
{
    /**
     * Every model/column pair that holds a media path.
     *
     * @return array<int, array{model: class-string<Model>, column: string, label: string}>
     */
    public static function sources(): array
    {
        return [
            ['model' => Attendance::class, 'column' => 'check_in_photo', 'label' => 'attendances.check_in_photo'],
            ['model' => Attendance::class, 'column' => 'check_out_photo', 'label' => 'attendances.check_out_photo'],
            ['model' => Patrol::class, 'column' => 'photo', 'label' => 'patrols.photo'],
            ['model' => TaskSubmission::class, 'column' => 'foto', 'label' => 'task_submissions.foto'],
        ];
    }

    /**
     * Count the rows holding a non-empty path for one source.
     *
     * @param  array{model: class-string<Model>, column: string, label: string}  $source
     */
    public static function count(array $source, ?string $since = null): int
    {
        return static::query($source, $since)->count();
    }

    /**
     * Stream the distinct media paths for one source in chunks.
     *
     * Chunking is keyed on the primary key so the cursor stays stable even
     * while the application keeps writing new rows during the migration.
     *
     * @param  array{model: class-string<Model>, column: string, label: string}  $source
     * @param  callable(string $path, Model $record): void  $callback
     */
    public static function each(array $source, callable $callback, ?string $since = null, int $chunkSize = 500): void
    {
        static::query($source, $since)
            ->select(['id', $source['column']])
            ->chunkById($chunkSize, function ($records) use ($source, $callback): void {
                foreach ($records as $record) {
                    $path = $record->{$source['column']};

                    if (filled($path)) {
                        $callback($path, $record);
                    }
                }
            });
    }

    /**
     * @param  array{model: class-string<Model>, column: string, label: string}  $source
     */
    protected static function query(array $source, ?string $since = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = $source['model']::query()
            ->whereNotNull($source['column'])
            ->where($source['column'], '!=', '');

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        return $query;
    }
}
