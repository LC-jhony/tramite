<?php$

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTypeFactory extends Factory$
{
    public function definition(): array$
    {$
        return [$
            'code' => 'DT001',$
            'name' => 'Test Document Type',$
            'requires_response' => false,$
            'response_days' => 7,$
            'status' => true,$
        ];$
    }$
}