<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tour ZIP size threshold (MB)
    |--------------------------------------------------------------------------
    |
    | Files strictly larger than this size use background queue processing on the
    | admin web upload, browser chunked upload on the tour edit page, and internal
    | chunked handling on the Tour Manager API. At or below this size: sync
    | processing on admin (single request), simple API upload path, and single
    | POST upload from the edit form when using regular (non-chunked) submit.
    |
    */
    'zip_chunk_threshold_mb' => (int) env('TOUR_ZIP_CHUNK_THRESHOLD_MB', 30),

];
