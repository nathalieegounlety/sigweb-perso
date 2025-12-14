<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Roles>
 */
class RolesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'libelle'     => 'Role par défaut', // sera remplacé par la sequence dans le seeder
            //'description' => $this->faker->sentence(),
            'created_by'  => 1,
            'updated_by'  => 1,
        ];
    }
}
