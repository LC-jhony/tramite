<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'code' => $this->faker->unique()->lexify('OFF???'),
            'status' => true,
        ];
    }
}
