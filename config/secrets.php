<?php

return [
    'secret_id' => env('AWS_SECRET_ID', 'laravel-prod-secrets'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
];
