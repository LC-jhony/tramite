<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NIVEL 1 - Máxima autoridad municipal
        Office::create([
            'code' => 'MPCI-001',
            'name' => 'Oficina de la Alcaldía Municipal',
            'status' => true,
        ]);

        // NIVEL 2 - Órganos de gobierno y control
        Office::create([
            'code' => 'MPCI-002',
            'name' => 'Honorable Concejo Municipal',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-003',
            'name' => 'Oficina de Auditoría Interna',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-004',
            'name' => 'Secretaría General Municipal',
            'status' => true,
        ]);

        // NIVEL 3 - Direcciones
        Office::create([
            'code' => 'MPCI-005',
            'name' => 'Dirección de Administración y Gestión Interna',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-006',
            'name' => 'Dirección de Finanzas y Recursos Económicos',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-007',
            'name' => 'Dirección de Planificación y Desarrollo Territorial',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-008',
            'name' => 'Dirección de Desarrollo Social y Humano',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-009',
            'name' => 'Dirección de Servicios Públicos Municipales',
            'status' => true,
        ]);

        // NIVEL 4 - Unidades operativas
        Office::create([
            'code' => 'MPCI-010',
            'name' => 'Unidad de Recursos Humanos',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-011',
            'name' => 'Unidad de Contrataciones y Adquisiciones',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-012',
            'name' => 'Unidad de Contabilidad',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-013',
            'name' => 'Unidad de Tesorería',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-014',
            'name' => 'Unidad de Presupuesto',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-015',
            'name' => 'Unidad de Catastro y Contribuciones',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-016',
            'name' => 'Unidad de Obras y Servicios Públicos',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-017',
            'name' => 'Unidad de Mesa de Partes',
            'status' => true,
        ]);
    }
}
