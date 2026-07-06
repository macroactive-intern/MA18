<?php

namespace Database\Factories;

use App\Models\NutritionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NutritionLog>
 */
class NutritionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => \App\Models\User::factory(),
            'logged_at' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'meal_name' => $this->faker->words(2, true),
            'protein_g' => $this->faker->randomFloat(1, 5, 60),
            'carbs_g'   => $this->faker->randomFloat(1, 5, 100),
            'fat_g'     => $this->faker->randomFloat(1, 2, 40),
            'calories'  => null,
        ];
    }
}
