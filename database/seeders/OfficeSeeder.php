<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'parent_office_id' => null,
            'level' => 1,
            'manager' => 'Alcalde Municipal',
            'status' => true,
        ]);

        // NIVEL 2
        $concejo = Office::create([
            'code' => 'MPCI-002',
            'name' => 'Concejo Municipal',
            'parent_office_id' => $alcaldia->id,
            'level' => 2,
            'manager' => 'Presidente del Concejo',
            'status' => true,
        ]);

        $auditoria = Office::create([
            'code' => 'MPCI-003',
            'name' => 'Auditoría Interna',
            'parent_office_id' => $alcaldia->id,
            'level' => 2,
            'manager' => 'Auditor Interno',
            'status' => true,
        ]);

        $secretaria = Office::create([
            'code' => 'MPCI-004',
            'name' => 'Secretaría Municipal',
            'parent_office_id' => $alcaldia->id,
            'level' => 2,
            'manager' => 'Secretario Municipal',
            'status' => true,
        ]);

        // NIVEL 3 - Direcciones
        $admin = Office::create([
            'code' => 'MPCI-005',
            'name' => 'Dirección Administrativa',
            'parent_office_id' => $alcaldia->id,
            'level' => 3,
            'manager' => 'Director Administrativo',
            'status' => true,
        ]);

        $finanzas = Office::create([
            'code' => 'MPCI-006',
            'name' => 'Dirección Financiera',
            'parent_office_id' => $alcaldia->id,
            'level' => 3,
            'manager' => 'Director Financiero',
            'status' => true,
        ]);

        $planificacion = Office::create([
            'code' => 'MPCI-007',
            'name' => 'Dirección de Planificación',
            'parent_office_id' => $alcaldia->id,
            'level' => 3,
            'manager' => 'Director de Planificación',
            'status' => true,
        ]);

        $social = Office::create([
            'code' => 'MPCI-008',
            'name' => 'Dirección de Desarrollo Social',
            'parent_office_id' => $alcaldia->id,
            'level' => 3,
            'manager' => 'Director Social',
            'status' => true,
        ]);

        $servicios = Office::create([
            'code' => 'MPCI-009',
            'name' => 'Dirección de Servicios Municipales',
            'parent_office_id' => $alcaldia->id,
            'level' => 3,
            'manager' => 'Director de Servicios',
            'status' => true,
        ]);

        // NIVEL 4 - Unidades Administrativas
        Office::create([
            'code' => 'MPCI-010',
            'name' => 'Unidad de Recursos Humanos',
            'parent_office_id' => $admin->id,
            'level' => 4,
            'manager' => 'Jefe de RRHH',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-011',
            'name' => 'Unidad de Compras',
            'parent_office_id' => $admin->id,
            'level' => 4,
            'manager' => 'Jefe de Compras',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-012',
            'name' => 'Contabilidad',
            'parent_office_id' => $finanzas->id,
            'level' => 4,
            'manager' => 'Contador Municipal',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-013',
            'name' => 'Tesorería',
            'parent_office_id' => $finanzas->id,
            'level' => 4,
            'manager' => 'Tesorero Municipal',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-014',
            'name' => 'Presupuesto',
            'parent_office_id' => $finanzas->id,
            'level' => 4,
            'manager' => 'Encargado de Presupuesto',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-015',
            'name' => 'Catastro',
            'parent_office_id' => $planificacion->id,
            'level' => 4,
            'manager' => 'Jefe de Catastro',
            'status' => true,
        ]);

        Office::create([
            'code' => 'MPCI-016',
            'name' => 'Obras Públicas',
            'parent_office_id' => $servicios->id,
            'level' => 4,
            'manager' => 'Jefe de Obras',
            'status' => true,
        ]);
    }
}
