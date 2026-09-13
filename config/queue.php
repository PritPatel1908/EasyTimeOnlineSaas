<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('QUEUE_CONNECTION', 'database_tenant'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | ARCHITECTURE — Central vs Tenant Queue Isolation:
    |
    | Both central and tenant jobs use the SAME 'database' queue connection
    | (central SQLite jobs table). Isolation is achieved through:
    |
    |   1. QUEUE NAMES  — central jobs → 'default' queue name
    |                      tenant jobs  → 'tenant' queue name (+ processing, low)
    |
    |   2. PAYLOAD      — QueueTenancyBootstrapper injects tenant_id into every
    |                      tenant job payload. The worker reads this and calls
    |                      tenancy()->initialize() before running handle().
    |
    | The 'database' connection has 'central' => true so that Central jobs
    | dispatched while a tenant happens to be active do NOT get tenant_id
    | injected. Tenant jobs dispatched via ->onQueue('tenant') from a tenant
    | context WILL have tenant_id injected (because the queue name, not the
    | connection, determines which payload goes where).
    |
    | Wait — the 'central' flag applies to the CONNECTION, not the queue name.
    | So we need a separate connection WITHOUT 'central' => true that still
    | writes to the same SQLite jobs table, used only for tenant job dispatch.
    |
    |   CENTRAL jobs  → dispatch()->onConnection('database')         (central flag suppresses tenant_id)
    |   TENANT  jobs  → dispatch()->onConnection('database_tenant')  (no flag → tenant_id injected)
    |
    | Worker commands:
    |   Central:  php artisan queue:work database  --queue=default
    |   Tenant:   php artisan queue:work database_tenant --queue=tenant,processing,low
    |
    | Both workers read from the same physical SQLite jobs table.
    | The worker handles context switching automatically via QueueTenancyBootstrapper.
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        /*
        |----------------------------------------------------------------------
        | Central Queue Connection
        |----------------------------------------------------------------------
        | For jobs dispatched from the central admin panel (Filament, central
        | Artisan commands). The 'central' => true flag tells
        | QueueTenancyBootstrapper to NEVER inject tenant_id — so these jobs
        | always execute in the central (no-tenant) context.
        |
        | Jobs: app/Jobs/Central/*
        | Worker: php artisan queue:work database --queue=default
        */
        'database' => [
            'driver'       => 'database',
            'connection'   => 'sqlite',
            'table'        => env('DB_QUEUE_TABLE', 'jobs'),
            'queue'        => 'default',
            'retry_after'  => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
            'central'      => true, // Suppress tenant_id injection for central jobs
        ],

        /*
        |----------------------------------------------------------------------
        | Tenant Queue Connection
        |----------------------------------------------------------------------
        | For jobs dispatched from a tenant HTTP request or tenant Artisan
        | command. Uses the same central SQLite jobs table but has NO 'central'
        | flag, so QueueTenancyBootstrapper injects tenant_id into the payload.
        |
        | The worker reads tenant_id from each job's payload and automatically
        | calls tenancy()->initialize() before running handle(). This means a
        | single worker process handles jobs for ALL tenants correctly.
        |
        | Jobs: app/Jobs/Tenant/*
        | Worker: php artisan queue:work database_tenant --queue=tenant,processing,low
        */
        'database_tenant' => [
            'driver'       => 'database',
            'connection'   => 'sqlite',
            'table'        => env('DB_QUEUE_TABLE', 'jobs'),
            'queue'        => 'tenant',
            'retry_after'  => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
            // NO 'central' flag → QueueTenancyBootstrapper injects tenant_id
        ],

        'beanstalkd' => [
            'driver'       => 'beanstalkd',
            'host'         => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue'        => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after'  => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for'    => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver'       => 'sqs',
            'key'          => env('AWS_ACCESS_KEY_ID'),
            'secret'       => env('AWS_SECRET_ACCESS_KEY'),
            'prefix'       => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue'        => env('SQS_QUEUE', 'default'),
            'suffix'       => env('SQS_SUFFIX'),
            'region'       => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver'       => 'redis',
            'connection'   => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue'        => env('REDIS_QUEUE', 'default'),
            'retry_after'  => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for'    => null,
            'after_commit' => false,
        ],

        'deferred' => [
            'driver' => 'deferred',
        ],

        'background' => [
            'driver' => 'background',
        ],

        'failover' => [
            'driver'      => 'failover',
            'connections' => [
                'database',
                'deferred',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table'    => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    */

    'failed' => [
        'driver'   => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table'    => 'failed_jobs',
    ],

];
