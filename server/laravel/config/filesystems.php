<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | The default disk is used by Storage::put() and similar calls when no
    | specific disk is specified. For this application the default remains
    | 'local' so that non-image framework writes (logs, cache, etc.) are
    | unaffected.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Three disks are defined:
    |
    |   local   — Framework internal use (sessions, cache, etc.).
    |   private — All user-uploaded images. Never web-accessible. Access is
    |             mediated exclusively through authenticated Laravel controller
    |             endpoints that stream the file after authorization. Compliant
    |             with LOPD / GDPR requirements for personal data files.
    |   public  — Retained for possible future use but NOT symlinked by the
    |             entrypoint.sh in this project. No files should be written here.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
            'throw'  => false,
        ],

        /**
         * Private disk — SRP: used exclusively for user-uploaded images.
         * All files stored here are inaccessible via Nginx (deny rule in
         * default.conf). Files are streamed through ImageService::stream()
         * which applies Cache-Control: private headers.
         */
        'private' => [
            'driver'     => 'local',
            'root'       => storage_path('app/private'),
            'visibility' => 'private',
            'throw'      => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => public_path('uploads'),
            'url'        => env('APP_URL') . '/uploads',
            'visibility' => 'public',
            'throw'      => false,
        ],

        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION'),
            'bucket'                  => env('AWS_BUCKET'),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'                   => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | No symlink from public/storage to storage/app/public is used in this
    | project. The entrypoint.sh does NOT call php artisan storage:link to
    | prevent accidental public exposure of the private images directory.
    |
    */

    'links' => [],

];
