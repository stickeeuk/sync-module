<?php

use Stickee\Sync\Models\Commissions\FixedCommission;
use Stickee\Sync\Models\Commissions\PercentageCommission;
use Stickee\Sync\Models\PropertyConfigurations\BroadbandSite;
use Stickee\Sync\Models\PropertyConfigurations\MobilesSite;
use Stickee\Sync\Models\PropertyConfigurations\PetInsuranceSite;
use Stickee\Sync\Models\Vertical;

return [
    /*
     |--------------------------------------------------------------------------
     | API URL
     |--------------------------------------------------------------------------
     |
     | The sync API URL
     */
    'api_url' => env('SYNC_API_URL', 'https://todo'),

    /*
     |--------------------------------------------------------------------------
     | Tables that are allowed to be synchronised
     |--------------------------------------------------------------------------
     |
     | A map of table names (or database.table) that can be synchronised to
     | options for that table
     | Options:
     |  - (string) connection: The name of the database connection to use
     |    (i.e. DB::connection($name)). Default: config(database.default)
     |  - (string|array) primary: The column or array of columns that make up
     |    the primary key. Default: "id"
     |
     | Example:
     | [
     |     'table_1' => [],
     |     'table_2' => ['primary' => 'uuid'],
     |     'table_3' => ['connection' => 'my_connection'],
     |     'db_2.table_1' => [
     |         'connection' => 'my_connection'
     |         'primary' => ['type', 'code'],
     |      ],
     | ]
     */
    'tables' => [
        'sync_tests' => [],
    ],

    /*
     |--------------------------------------------------------------------------
     | URL for API requests
     |--------------------------------------------------------------------------
     |
     | The URL to register routes on for API requests
     */
    'url' => 'sync',
];
