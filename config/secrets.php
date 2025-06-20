<?php

return [
    'full_config' => env('FULL_CONFIG', false),
    'secret_id' => env('AWS_SECRET_ID', 'laravel-prod-secrets'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'rds_rotation' => env('RDS_ROTATION', false),
    'rds_secret_id' => env('AWS_SECRET_ID_RDS', 'laravel-prod-secrets'),
    'rds_read_write' => env('RDS_READ_WRITE', false),
];
