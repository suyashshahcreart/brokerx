<?php

return [
    /*
    | Human-readable labels for Laravel job display names (payload displayName).
    */
    'job_labels' => [
        'App\\Jobs\\ProcessTourZipFile' => 'Tour ZIP processing',
        'App\\Jobs\\UploadTourAssetsToS3' => 'Tour assets S3 upload',
    ],

    /*
    | Known queues shown in helpers / empty states even when DB has none yet.
    */
    'known_queues' => ['tour-processing', 'default'],

    /*
    | Worker documentation note (shown in Queue Monitor UI).
    */
    'supervisor_queues_note' =>
        'Supervisor workers typically run: php artisan queue:work --queue=tour-processing. '
        .'Jobs on other queues (e.g. default) need a worker that listens to those queues.',

    /*
    | Job classes for which booking tour_zip_* columns are merged into monitor rows.
    */
    'tour_job_classes' => [
        'App\\Jobs\\ProcessTourZipFile',
    ],

    /*
    | How far back (days) failed_jobs rows are scanned for filter-option job types.
    */
    'recent_failed_days_for_filters' => 30,

    /*
    | Default date window when listing tour_done rows (heavy). Overridden by request params.
    */
    'completed_tours_default_days' => 14,
];
