<?php

return [
    'app_secret_token' => env('ECONOMIC_APP_SECRET_TOKEN'),
    'agreement_grant_token' => env('ECONOMIC_AGREEMENT_GRANT_TOKEN'),

    /*
     * This class handles actions on request and response to Economic.
     */
    'request_logger' => \Morningtrain\LaravelEconomic\RequestLogger\VoidRequestLogger::class,

    /*
     * The timeout in seconds for the request to Economic.
     */
    'timeout_seconds' => env('ECONOMIC_TIMEOUT_SECONDS', 30),

    /*
     * Number of months to fetch invoices for (default: 6)
     * Change this to fetch more or less historical data
     */
    'sync_months' => env('ECONOMIC_SYNC_MONTHS', 6),

    /*
     * Cache duration in minutes for invoice data (default: 30)
     * Set to 0 to disable caching
     */
    'cache_duration' => env('ECONOMIC_CACHE_DURATION', 30),
];
