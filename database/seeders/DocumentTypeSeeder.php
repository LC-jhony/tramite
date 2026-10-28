<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DOCUMENTOS QUE REQUIEREN RESPUESTA
        DocumentType::create([
            'code' => 'SOL',
            'name' => 'Solicitud',
            'requires_response' => true,
            'response_days' => 5,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'OFI',
            'name' => 'Oficio',
            'requires_response' => true,
            'response_days' => 5,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'MEM',
            'name' => 'Memorándum',
            'requires_response' => true,
            'response_days' => 3,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'NOT',
            'name' => 'Nota',
            'requires_response' => true,
            'response_days' => 3,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'PET',
            'name' => 'Petición',
            'requires_response' => true,
            'response_days' => 10,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'QUE',
            'name' => 'Queja',
            'requires_response' => true,
            'response_days' => 10,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'DEN',
            'name' => 'Denuncia',
            'requires_response' => true,
            'response_days' => 10,
            'status' => true,
        ]);

        // DOCUMENTOS INFORMATIVOS
        DocumentType::create([
            'code' => 'INF',
            'name' => 'Informe',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'DIC',
            'name' => 'Dictamen',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'CIR',
            'name' => 'Circular',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'ACT',
            'name' => 'Acta',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'CON',
            'name' => 'Convenio',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        // ACTOS DE GESTIÓN
        DocumentType::create([
            'code' => 'RES',
            'name' => 'Resolución',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'DEC',
            'name' => 'Decreto',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'CONTR',
            'name' => 'Contrato',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);

        DocumentType::create([
            'code' => 'REM',
            'name' => 'Remisión',
            'requires_response' => false,
            'response_days' => null,
            'status' => true,
        ]);
    }
}
