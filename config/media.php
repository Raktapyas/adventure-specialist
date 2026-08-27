<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed uploads
    |--------------------------------------------------------------------------
    |
    | The MIME and extension allowlists are checked server-side against the
    | sniffed file contents (finfo), never against the client-declared type.
    | SVG is intentionally excluded because untrusted SVG can execute script
    | when served inline. Videos are short cinematic clips; AV1 footage rides
    | inside MP4/WebM containers and is accepted through those MIME types.
    |
    */

    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/avif',
        'video/mp4',
        'video/webm',
    ],

    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'mp4', 'webm'],

    /*
    |--------------------------------------------------------------------------
    | Size limits
    |--------------------------------------------------------------------------
    |
    | Maximum upload size in bytes per kind of media. Images default to 5 MB;
    | short cinematic videos are allowed up to 50 MB.
    |
    */

    'max_image_bytes' => 5 * 1024 * 1024,

    'max_video_bytes' => 50 * 1024 * 1024,

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | New uploads are written to this disk under "media/<year>/<month>/" and
    | served through the public storage symlink (php artisan storage:link).
    |
    */

    'disk' => env('MEDIA_DISK', 'public'),

    'directory' => 'media',

];
