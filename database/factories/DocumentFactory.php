<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Document;
use App\Models\Customer;
use App\Models\Office;
use App\Models\DocumentType;
use App\Models\Administration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'document_number' => 'DOC-' . $this->faker->unique()->randomNumber(5),
            'case_number' => 'CASE-' . $this->faker->unique()->randomNumber(5),
            'subject' => 'Test Document Subject',
            'origen' => 'externo',
            'document_type_id' => DocumentType::factory(),
            'current_office_id' => Office::factory(),
            'user_id' => User::factory(),
            'gestion_id' => Administration::factory(),
            'reception_date' => now(),
            'status' => 'registrado',
        ];
    }
}
