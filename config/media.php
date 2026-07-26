<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media Disk
    |--------------------------------------------------------------------------
    |
    | The disk that user-uploaded media (attendance, patrol, and checkpoint
    | photos) is written to and read from. This is intentionally separate from
    | the framework's "default" disk, which stays local so that Livewire and
    | Filament temporary uploads never round-trip to cloud storage.
    |
    */

    'disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Mirror Disk
    |--------------------------------------------------------------------------
    |
    | When set, every upload is written to this disk in addition to the primary
    | disk above. This exists to bridge the cutover: turning the mirror on
    | before the bulk sync guarantees the cloud bucket holds a superset of the
    | local files, so switching "disk" afterwards can never surface a missing
    | image. Leave empty once the cutover is complete.
    |
    */

    'mirror_disk' => env('MEDIA_MIRROR_DISK') ?: null,

    /*
    |--------------------------------------------------------------------------
    | Media Directories
    |--------------------------------------------------------------------------
    |
    | Top-level directories, relative to the media disk root, that hold uploads.
    | Used by the media:* Artisan commands to scope their work and to keep
    | unrelated content (exports, framework files) out of the migration.
    |
    */

    'directories' => ['attendances', 'patrols', 'checkpoints'],

    /*
    |--------------------------------------------------------------------------
    | Cache-Control Header
    |--------------------------------------------------------------------------
    |
    | Applied to every object written to cloud storage. Uploaded photos are
    | immutable — each gets a unique, timestamped filename and is never
    | rewritten — so they can be cached indefinitely. Without this header a CDN
    | in front of the bucket reports "DYNAMIC" and re-fetches the object on
    | every single view, which is both slower and billed per request.
    |
    | Set to an empty value to stop sending the header.
    |
    */

    'cache_control' => env('MEDIA_CACHE_CONTROL', 'public, max-age=31536000, immutable'),

];
