<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Tables that will be synchronised
     |--------------------------------------------------------------------------
     |
     | A map of sync-server.tables keys to destination table info.
     | The key MUST be one of the keys from the server configuration
     | Options:
     |  - (string) connection: The name of the database connection to use
     |    (i.e. DB::connection($name)). Default: config(database.default)
     |  - (string|array) primary: The column or array of columns that make up
     |    the primary key. Default: "id"
     |  - (string|array) importIndexes: Indexes to join on when importing data.
          Default: "PRIMARY"
     |  - (string) table: The table name, if different to the key
     |  - (array) renames: Array of field renames to do in the format: FROM => TO
     |    Default: []
     |
     | Example:
     | [
     |     'table_1' => [],
     |     'table_2' => ['primary' => 'uuid'],
     |     'table_2_on_my_connection' => ['connection' => 'my_connection', 'table' => 'table_2'],
     |     'db_2.table_1' => [
     |         'connection' => 'my_connection'
     |         'primary' => ['type', 'code'],
     |      ],
     |     'table_3' => ['renames' => ['remote_field' => 'local_field']],
     | ]
     */
    'tables' => [],

    /*
     |--------------------------------------------------------------------------
     | Directories that will be synchronised
     |--------------------------------------------------------------------------
     |
     | A map of sync-server.directories keys to destination directory info.
     | The key MUST be one of the keys from the server configuration
     | Options:
     |  - (string) disk: The disk to write to. Required
     |  - (string) hasher: The \Stickee\Sync\Interfaces\DirectoryHasherInterface class
     |    to use. Default: config('sync-client.default_file_hasher')
     */
    'directories' => [],

    /*
     |--------------------------------------------------------------------------
     | Default file hasher
     |--------------------------------------------------------------------------
     |
     | The default file hasher class
     */
    'default_file_hasher' => \Stickee\Sync\DirectoryHashers\Md5DirectoryHasher::class,

    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | The sync API URL for clients to connect to, e.g. http://example.com/api/sync
    */
    'api_url' => env('SYNC_API_URL'),

    /*
    |--------------------------------------------------------------------------
    | Files per request
    |--------------------------------------------------------------------------
    |
    | The number of files for the client to download per HTTP request
    */
    'files_per_request' => 10,

    /*
    |--------------------------------------------------------------------------
    | Single transaction
    |--------------------------------------------------------------------------
    |
    | Perform all table updates in a single transaction
    */
    'single_transaction' => true,

    /*
    |--------------------------------------------------------------------------
    | Cron schedule
    |--------------------------------------------------------------------------
    |
    | How often to run php artisan sync:sync
    */
    'cron_schedule' => env('SYNC_CRON_SCHEDULE', '*/5 * * * *'),
];
