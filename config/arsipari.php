<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ARSIPARI Configuration Options
    |--------------------------------------------------------------------------
    |
    | Centralized configuration parameters for ARSIPARI digital archive system.
    |
    */

    'version'                 => env('ARSIPARI_VERSION', '1.0.0'),
    'app_name'                => env('ARSIPARI_APP_NAME', 'ARSIPARI'),
    'backup_retention'        => (int) env('ARSIPARI_BACKUP_RETENTION', 7),
    'pdf_max_rows'            => (int) env('ARSIPARI_PDF_MAX_ROWS', 5000),
    'retention_warning_days'  => (int) env('ARSIPARI_RETENTION_WARNING_DAYS', 90),
    'max_file_size_mb'        => (int) env('ARSIPARI_MAX_FILE_SIZE_MB', 20),
];
