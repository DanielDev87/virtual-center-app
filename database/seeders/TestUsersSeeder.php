<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = UserRole::where('role_name', 'Admin')->first();
        $contributorRole = UserRole::where('role_name', 'Contributor')->first();
        $monitorRole = UserRole::where('role_name', 'Monitor')->first();
        $requesterRole = UserRole::where('role_name', 'Requester')->first();

        // Create test users
        $users = [
            [
                'user_name' => 'Admin Test',
                'user_email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->role_id,
                'is_active' => true,
            ],
            [
                'user_name' => 'Colaborador Test',
                'user_email' => 'colaborador@test.com',
                'password' => Hash::make('password'),
                'role_id' => $contributorRole->role_id,
                'is_active' => true,
            ],
            [
                'user_name' => 'Monitor Test',
                'user_email' => 'monitor@test.com',
                'password' => Hash::make('password'),
                'role_id' => $monitorRole->role_id,
                'is_active' => true,
            ],
            [
                'user_name' => 'Solicitante Test',
                'user_email' => 'solicitante@test.com',
                'password' => Hash::make('password'),
                'role_id' => $requesterRole->role_id,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['user_email' => $userData['user_email']],
                $userData
            );
        }

        $this->command->info('✅ Usuarios de prueba creados exitosamente!');
        $this->command->info('📧 Email: admin@test.com | colaborador@test.com | monitor@test.com | solicitante@test.com');
        $this->command->info('🔑 Password: password (para todos)');
    }
}
