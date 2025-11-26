<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserRole;

class LegacyUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist
        $adminRole = UserRole::where('role_name', 'Admin')->first();
        $contributorRole = UserRole::where('role_name', 'Contributor')->first();

        if (!$adminRole || !$contributorRole) {
            $this->command->error('Roles not found. Please run UserRoleSeeder first.');
            return;
        }

        // Migrate Superadmins
        $superadmins = DB::connection('mysql')->table('superadmin')->get();
        foreach ($superadmins as $admin) {
            $exists = User::where('user_email', $admin->correo_superadmin)->exists();
            if (!$exists) {
                User::create([
                    'user_name' => $admin->nombre_super . ' ' . $admin->apellido_super,
                    'user_email' => $admin->correo_superadmin,
                    'password' => Hash::make($admin->pw_super), // Hashing legacy password
                    'role_id' => $adminRole->role_id,
                    'is_active' => true,
                ]);
                $this->command->info("Migrated admin: {$admin->correo_superadmin}");
            }
        }

        // Migrate Contributors (tdea_usuarios)
        $users = DB::connection('mysql')->table('tdea_usuarios')->get();
        foreach ($users as $user) {
            $exists = User::where('user_email', $user->correo_usuario)->exists();
            if (!$exists) {
                User::create([
                    'user_name' => $user->nombre_usuario,
                    'user_email' => $user->correo_usuario,
                    'password' => Hash::make($user->pw_usuario), // Hashing legacy password
                    'role_id' => $contributorRole->role_id,
                    'is_active' => true,
                ]);
                $this->command->info("Migrated user: {$user->correo_usuario}");
            }
        }
    }
}
