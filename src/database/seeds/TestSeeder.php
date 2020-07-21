<?php

namespace Stickee\Sync\Seeds;

use Illuminate\Database\Seeder;
use Stickee\Sync\Models\SyncTest;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(SyncTest::class, 10)->create();
    }
}
