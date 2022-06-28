<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Tables that are allowed to be synchronised
     |--------------------------------------------------------------------------
     |
     | A map of <table>/<database>.<table>/arbitrary-name => options for that table
     | If the key is an arbitrary name then the "table" option must be specified
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
     | Directories that are allowed to be synchronised
     |--------------------------------------------------------------------------
     |
     | A map of (arbitrary) name to options
     | Options:
     |  - (string) disk: The disk to read from. Required
     |  - (string) hasher: The \Stickee\Sync\Interfaces\DirectoryHasherInterface class
     |    to use. Default: config('sync-server.default_file_hasher')
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
];
