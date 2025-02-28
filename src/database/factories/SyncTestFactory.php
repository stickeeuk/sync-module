<?php

namespace Stickee\Sync\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Stickee\Sync\Models\SyncTest;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Stickee\Sync\Models\SyncTest>
 */
class SyncTestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Stickee\Sync\Models\SyncTest>
     */
    protected $model = SyncTest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'test_1' => fake()->randomNumber(),
            'test_2' => fake()->sentence(),
            'test_3' => fake()->optional()->sentence(),
            'test_4' => fake()->optional()->randomElement(['A', 'B', 'C']),
        ];
    }
}
