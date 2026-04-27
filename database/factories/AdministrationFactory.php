<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Administration;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdministrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->year(),
            'start_period' => $this->faker->year(),
            'end_period' => $this->faker->year(),
            'mayor' => $this->faker->name(),
            'status' => true,
        ];
    }
}
