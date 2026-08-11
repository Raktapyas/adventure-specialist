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
    | when served inline.
    |
    */

    'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],

    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],

    /*
    |--------------------------------------------------------------------------
    | Size limit
    |--------------------------------------------------------------------------
    |
    | Maximum upload size in bytes (default 5 MB).
    |
    */

    'max_upload_bytes' => 5 * 1024 * 1024,

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
