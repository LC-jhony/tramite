<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFileFactory extends Factory
{
    protected $model = DocumentFile::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'filename' => fake()->word().'.pdf',
            'path' => 'documents/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 10485760),
            'uploaded_by' => User::factory(),
        ];
    }

    public function word(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'application/msword',
        ]);
    }

    public function excel(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'application/vnd.ms-excel',
        ]);
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => fake()->randomElement(['image/jpeg', 'image/png']),
            'filename' => fake()->word().'.'.fake()->randomElement(['jpg', 'png']),
        ]);
    }
}
