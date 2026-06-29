<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed roles and default users.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'administrator@inventori.test'],
            [
                'name'     => 'Budi Santoso',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles([$adminRole]);

        // Create default staff user
        $staff = User::firstOrCreate(
            ['email' => 'staff@inventori.test'],
            [
                'name'     => 'Siti Rahayu',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $staff->syncRoles([$staffRole]);

        $this->command->info('Roles and default users seeded successfully.');
        $this->command->info('  - administrator@inventori.test / password (Administrator)');
        $this->command->info('  - staff@inventori.test / password (Staff)');
    }
}
