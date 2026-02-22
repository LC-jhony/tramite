<?php

namespace Database\Factories;

use App\Enum\DocumentStatus;
use App\Models\Administration;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Priority;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $year = now()->year;

        return [
            'customer_id' => Customer::factory(),
            'document_number' => "{$year}-".str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'case_number' => "{$year}-0".str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'subject' => fake()->sentence(3),
            'origen' => fake()->randomElement(['Interno', 'Externo']),
            'document_type_id' => DocumentType::factory(),
            'area_origen_id' => Office::factory(),
            'gestion_id' => Administration::factory(),
            'user_id' => User::factory(),
            'folio' => fake()->numberBetween(1, 100),
            'reception_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'response_deadline' => fake()->dateTimeBetween('now', '+30 days'),
            'condition' => true,
            'status' => DocumentStatus::REGISTERED,
            'priority_id' => Priority::factory(),
        ];
    }

    public function registered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::REGISTERED,
        ]);
    }

    public function inProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::IN_PROCESS,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::COMPLETED,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::REJECTED,
        ]);
    }

    public function externo(): static
    {
        return $this->state(fn (array $attributes) => [
            'origen' => 'Externo',
        ]);
    }

    public function interno(): static
    {
        return $this->state(fn (array $attributes) => [
            'origen' => 'Interno',
        ]);
    }
}
