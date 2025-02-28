<?php

namespace Stickee\Sync\Seeds;

use Illuminate\Database\Seeder;
use Stickee\Sync\Models\SyncTest;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SyncTest::factory()->count(10)->create();
    }
}
