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
     | The API key
     |--------------------------------------------------------------------------
     |
     | The key for the sync API
     */
    'api_key' => env('AFFILIATES_API_KEY'),

    /*
     |--------------------------------------------------------------------------
     | Tables that are allowed to be synchronised
     |--------------------------------------------------------------------------
     |
     | A list of table names (or database.table) that can be synchronised
     */
    'allowed_tables' => ['sync_tests'],

    /*
     |--------------------------------------------------------------------------
     | URL for API requests
     |--------------------------------------------------------------------------
     |
     | The URL to register routes on for API requests
     */
    'url' => 'sync',
];
