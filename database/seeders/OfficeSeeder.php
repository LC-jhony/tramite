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
        $alcaldia = Office::create([
            'code' => 'MPCI-001',
            'name' => 'Alcaldía Municipal',
            'status' => true,
        ]);

        // NIVEL 2
        $concejo = Office::create([
            'code' => 'MPCI-002',
            'name' => 'Concejo Municipal',
            'status' => true,
        ]);

        $auditoria = Office::create([
            'code' => 'MPCI-003',
            'name' => 'Auditoría Interna',
            'status' => true,
        ]);

        $secretaria = Office::create([
            'code' => 'MPCI-004',
            'name' => 'Secretaría Municipal',
            'status' => true,
        ]);

        // NIVEL 3 - Direcciones
        $admin = Office::create([
            'code' => 'MPCI-005',
            'name' => 'Dirección Administrativa',
            'status' => true,
        ]);

        $finanzas = Office::create([
            'code' => 'MPCI-006',
            'name' => 'Dirección Financiera',
            'status' => true,
        ]);

        $planificacion = Office::create([
            'code' => 'MPCI-007',
            'name' => 'Dirección de Planificación',
            'status' => true,
        ]);

        $social = Office::create([
            'code' => 'MPCI-008',
            'name' => 'Dirección de Desarrollo Social',
            'status' => true,
        ]);

        $servicios = Office::create([
            'code' => 'MPCI-009',
            'name' => 'Dirección de Servicios Municipales',
            'status' => true,
        ]);

        // NIVEL 4 - Unidades Administrativas
        Office::create([
            'code' => 'MPCI-010',
            'name' => 'Unidad de Recursos Humanos',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-011',
            'name' => 'Unidad de Compras',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-012',
            'name' => 'Contabilidad',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-013',
            'name' => 'Tesorería',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-014',
            'name' => 'Presupuesto',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-015',
            'name' => 'Catastro',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-016',
            'name' => 'Obras Públicas',
            'status' => true,
        ]);
        Office::create([
            'code' => 'MPCI-017',
            'name' => 'Mesa de Partes',
            'status' => true,
        ]);
    }
}
