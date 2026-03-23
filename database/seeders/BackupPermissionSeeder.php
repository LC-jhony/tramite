<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BackupPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'download-backup',
            'delete-backup',
            'create-backup',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign to a role (optional)
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $role->givePermissionTo($permissions);

        // Assign role to a user (optional)
        $user = User::find(1);

        if ($user && $role) {
            $user->assignRole($role);
        }
    }
}
