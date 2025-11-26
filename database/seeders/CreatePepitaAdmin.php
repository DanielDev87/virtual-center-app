<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class CreatePepitaAdmin extends Seeder
{
    public function run(): void
    {
        $adminRole = UserRole::where('role_name', 'Admin')->first();

        if (!$adminRole) {
            $this->command->error('❌ Rol Admin no encontrado. Ejecuta primero: php artisan db:seed --class=UserRoleSeeder');
            return;
        }

        User::updateOrCreate(
            ['user_email' => 'pepita@prueba.edu.co'],
            [
                'user_name' => 'Pepita Perez',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->role_id,
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Usuario admin creado/actualizado: pepita@prueba.edu.co');
        $this->command->info('🔑 Contraseña: password');
    }
}
