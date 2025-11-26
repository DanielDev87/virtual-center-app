<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserRole;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserRole;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'Admin',
                'role_description' => 'Administrador del sistema con acceso total',
                'role_color' => '#dc3545',
                'is_active' => true,
            ],
            [
                'role_name' => 'Contributor',
                'role_description' => 'Colaborador que trabaja en tickets asignados',
                'role_color' => '#0d6efd',
                'is_active' => true,
            ],
            [
                'role_name' => 'Monitor',
                'role_description' => 'Supervisor de proyectos y tickets',
                'role_color' => '#6f42c1',
                'is_active' => true,
            ],
            [
                'role_name' => 'Requester',
                'role_description' => 'Usuario que crea y gestiona solicitudes',
                'role_color' => '#28a745',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            UserRole::updateOrCreate(
                ['role_name' => $role['role_name']],
                $role
            );
        }
    }
}
