<?php

namespace Database\Factories;

use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Document;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    protected $model = \App\Models\Movement::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'origin_office_id' => Office::factory(),
            'origin_user_id' => User::factory(),
            'destination_office_id' => Office::factory(),
            'destination_user_id' => User::factory(),
            'action' => MovementAction::DERIVACION,
            'indication' => fake()->optional()->sentence(),
            'observation' => fake()->optional()->sentence(),
            'receipt_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => MovementStatus::PENDING,
        ];
    }

    public function derivacion(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => MovementAction::DERIVACION,
        ]);
    }

    public function recibido(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => MovementAction::RECIBIDO,
            'status' => MovementStatus::RECEIVED,
        ]);
    }

    public function respuesta(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => MovementAction::RESPUESTA,
        ]);
    }

    public function rechazado(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => MovementAction::RECHAZADO,
            'status' => MovementStatus::REJECTED,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MovementStatus::COMPLETED,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MovementStatus::PENDING,
        ]);
    }
}
