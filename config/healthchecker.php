<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Healthchecks.io base URL
    |--------------------------------------------------------------------------
    | Default to the public hosted Healthchecks.io endpoint. Override for a
    | self-hosted instance.
    */
    'base_url' => env('HEALTHCHECKER_BASE_URL', 'https://hc-ping.com'),

    /*
    |--------------------------------------------------------------------------
    | Global ping key
    |--------------------------------------------------------------------------
    | Used as a fallback for slug-based checks when an individual check does
    | not declare its own `ping_key`. Required for slug-based pings; ignored
    | when a check declares a UUID directly.
    */
    'ping_key' => env('HEALTHCHECKER_PING_KEY'),

    /*
    |--------------------------------------------------------------------------
    | HTTP client settings
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => (int) env('HEALTHCHECKER_HTTP_TIMEOUT', 5),
        'retries' => (int) env('HEALTHCHECKER_HTTP_RETRIES', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue checks
    |--------------------------------------------------------------------------
    | Each entry is a named queue probe. The scheduled command
    | `healthchecker:check-queue {check}` dispatches a job onto the configured
    | queue; when the worker processes it, it pings Healthchecks.io.
    |
    | Required per check:
    |   - One of:
    |       'uuid'                            (UUID-style ping URL)
    |       OR ('ping_key' + 'slug')          (slug-style ping URL; ping_key
    |                                          falls back to top-level ping_key)
    |   - 'queue'                             (target queue name)
    */
    'queue_checks' => [
        'default' => [
            'uuid' => env('HEALTHCHECKER_QUEUE_DEFAULT_UUID'),
            'ping_key' => env('HEALTHCHECKER_QUEUE_DEFAULT_PING_KEY'),
            'slug' => env('HEALTHCHECKER_QUEUE_DEFAULT_SLUG'),
            'queue' => env('HEALTHCHECKER_QUEUE_DEFAULT_NAME', 'default'),
            'connection' => env('HEALTHCHECKER_QUEUE_DEFAULT_CONNECTION'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail checks
    |--------------------------------------------------------------------------
    | Each entry is a named mail probe. The scheduled command
    | `healthchecker:check-mail {check}` sends an email to the configured
    | recipient. Point this at a Healthchecks.io email-ping address (each
    | Healthchecks.io check exposes a unique `*@hc-ping.com` address when
    | the email integration is enabled).
    |
    | Required per check:
    |   - 'recipient' (email address)
    | Optional per check (only if you also want to ping Healthchecks.io
    | over HTTP after sending the mail):
    |   - 'uuid' OR ('ping_key' + 'slug')
    */
    'mail_checks' => [
        'default' => [
            'uuid' => env('HEALTHCHECKER_MAIL_DEFAULT_UUID'),
            'ping_key' => env('HEALTHCHECKER_MAIL_DEFAULT_PING_KEY'),
            'slug' => env('HEALTHCHECKER_MAIL_DEFAULT_SLUG'),
            'recipient' => env('HEALTHCHECKER_MAIL_DEFAULT_RECIPIENT'),
            'subject' => env('HEALTHCHECKER_MAIL_DEFAULT_SUBJECT', 'Healthchecks.io mail probe'),
            'mailer' => env('HEALTHCHECKER_MAIL_DEFAULT_MAILER'),
        ],
    ],

];
