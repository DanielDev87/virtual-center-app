<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email} {password=password}';
    protected $description = 'Reset user password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('user_email', $email)->first();

        if (!$user) {
            $this->error("Usuario no encontrado: {$email}");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("✅ Contraseña actualizada para: {$user->user_name} ({$user->user_email})");
        $this->info("🔑 Nueva contraseña: {$password}");
        $this->info("👤 Rol ID: {$user->role_id}");

        return 0;
    }
}
