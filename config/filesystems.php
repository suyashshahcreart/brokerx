<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'tours' => [
            'driver' => env('TOURS_DISK_DRIVER', 'local'),
            'root' => public_path('tours'),
            'url' => env('APP_URL').'/tours',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL') ?: ('https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => true, // Enable exceptions for better error handling
        ],

        'ftp_creart_qr' => [
            'driver' => 'ftp',
            'host' => preg_replace('#^ftps?://#', '', env('FTP_HOST', '147.93.109.17')), // Remove ftp:// prefix if present
            'username' => env('FTP_USERNAME', 'u678951868.qrcreart'),
            'password' => env('FTP_PASSWORD', 'Other@@42@@'),
            'port' => (int) env('FTP_PORT', 21), // Cast to integer
            'root' => env('FTP_ROOT', '/'),
            'passive' => (bool) env('FTP_PASSIVE', true), // Cast to boolean
            'ssl' => (bool) env('FTP_SSL', false), // Cast to boolean
            'timeout' => (int) env('FTP_TIMEOUT', 30), // Cast to integer
            'throw' => false,
        ],

        // Location-based FTP configurations
        'ftp_industry' => [
            'driver' => 'ftp',
            'host' => preg_replace('#^ftps?://#', '', env('FTP_INDUSTRY_HOST', '82.25.125.92')), // Remove ftp:// prefix if present
            'username' => env('FTP_INDUSTRY_USERNAME') ?: env('FTP_INDUSTRY_USERNAME', 'u646288003.industryproppik'),
            'password' => env('FTP_INDUSTRY_PASSWORD') ?: env('FTP_INDUSTRY_PASSWORD', 'Other@@42@@'),
            'port' => (int) env('FTP_INDUSTRY_PORT', env('FTP_PORT', 21)),
            'root' => env('FTP_INDUSTRY_ROOT', '/'),
            'passive' => (bool) env('FTP_INDUSTRY_PASSIVE', true),
            'ssl' => (bool) env('FTP_INDUSTRY_SSL', false),
            'timeout' => (int) env('FTP_INDUSTRY_TIMEOUT', 30),
            'throw' => false,
        ],

        'ftp_htl' => [
            'driver' => 'ftp',
            'host' => preg_replace('#^ftps?://#', '', env('FTP_HTL_HOST', '82.25.125.92')), // Remove ftp:// prefix if present
            'username' => env('FTP_HTL_USERNAME') ?: env('FTP_HTL_USERNAME', 'u646288003.htlproppik'),
            'password' => env('FTP_HTL_PASSWORD') ?: env('FTP_HTL_PASSWORD', 'Other@@42@@'),
            'port' => (int) env('FTP_HTL_PORT', env('FTP_PORT', 21)),
            'root' => env('FTP_HTL_ROOT', '/'),
            'passive' => (bool) env('FTP_HTL_PASSIVE', true),
            'ssl' => (bool) env('FTP_HTL_SSL', false),
            'timeout' => (int) env('FTP_HTL_TIMEOUT', 30),
            'throw' => false,
        ],

        'ftp_re' => [
            'driver' => 'ftp',
            'host' => preg_replace('#^ftps?://#', '', env('FTP_RE_HOST', '82.25.125.92')), // Remove ftp:// prefix if present
            'username' => env('FTP_RE_USERNAME') ?: env('FTP_RE_USERNAME', 'u646288003.reproppik'),
            'password' => env('FTP_RE_PASSWORD') ?: env('FTP_RE_PASSWORD', 'Other@@42@@'),
            'port' => (int) env('FTP_RE_PORT', env('FTP_PORT', 21)),
            'root' => env('FTP_RE_ROOT', '/'),
            'passive' => (bool) env('FTP_RE_PASSIVE', true),
            'ssl' => (bool) env('FTP_RE_SSL', false),
            'timeout' => (int) env('FTP_RE_TIMEOUT', 30),
            'throw' => false,
        ],

        'ftp_rs' => [
            'driver' => 'ftp',
            'host' => preg_replace('#^ftps?://#', '', env('FTP_RS_HOST', '82.25.125.92')), // Remove ftp:// prefix if present
            'username' => env('FTP_RS_USERNAME') ?: env('FTP_RS_USERNAME', 'u646288003.rsproppik'),
            'password' => env('FTP_RS_PASSWORD') ?: env('FTP_RS_PASSWORD', 'Other@@42@@'),
            'port' => (int) env('FTP_RS_PORT', env('FTP_PORT', 21)),
            'root' => env('FTP_RS_ROOT', '/'),
            'passive' => (bool) env('FTP_RS_PASSIVE', true),
            'ssl' => (bool) env('FTP_RS_SSL', false),
            'timeout' => (int) env('FTP_RS_TIMEOUT', 30),
            'throw' => false,
        ],

        'ftp_tours' => [
            // tours uses SFTP (port 22)
            'driver' => 'sftp',
            'host' => preg_replace('#^s?ftps?://#', '', env('FTP_TOURS_HOST', '13.204.231.57')),
            'username' => env('FTP_TOURS_USERNAME', 'tourftp'),
            'password' => env('FTP_TOURS_PASSWORD', 'Tour@@42##'),
            'port' => (int) env('FTP_TOURS_PORT', 22),
            'root' => env('FTP_TOURS_ROOT', '/public_html'),
            'timeout' => (int) env('FTP_TOURS_TIMEOUT', 30),
            'visibility' => 'public',
            'permissions' => [
                // Flysystem SFTP expects visibility-based perms
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ],
            'host_fingerprint' => env('FTP_TOURS_FINGERPRINT', null),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
