<?php

namespace Stickee\Sync\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Stickee\Sync\Models\SyncTest;

class SyncTestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
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
            'test_1' => $this->faker->randomNumber(),
            'test_2' => $this->faker->sentence(),
            'test_3' => $this->faker->optional()->sentence(),
            'test_4' => $this->faker->optional()->randomElement(['A', 'B', 'C']),
        ];
    }
}
