<?php

namespace Database\Factories;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('DOC-???')),
            'name' => fake()->randomElement([
                'Carta',
                'Solicitud',
                'Oficio',
                'Memo',
                'Informe',
                'Resolución',
                'Decreto',
                'Edicto',
                'Denuncia',
                'Queja',
            ]),
            'requires_response' => fake()->boolean(70),
            'response_days' => fake()->randomElement([3, 5, 7, 10, 15, 30]),
            'status' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    public function requiresResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_response' => true,
            'response_days' => fake()->randomElement([3, 5, 7, 10, 15]),
        ]);
    }

    public function noRequiresResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_response' => false,
            'response_days' => null,
        ]);
    }
}
