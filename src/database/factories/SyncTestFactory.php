<?php

use Faker\Generator as Faker;
use Stickee\Sync\Models\SyncTest;

$factory->define(SyncTest::class, function (Faker $faker) {
    return [
        'test_1' => $faker->randomNumber,
        'test_2' => $faker->sentence,
    ];
});
