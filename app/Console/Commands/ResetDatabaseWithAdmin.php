<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetDatabaseWithAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-with-admin
                            {--name=Admin de Prueba : Nombre del usuario admin}
                            {--email=admin.prueba@local.test : Correo del admin}
                            {--password=Admin12345* : Contrasena del admin}
                            {--document= : Documento opcional del admin}
                            {--force : Ejecuta sin confirmacion interactiva}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reinicia la base de datos y deja un unico usuario Admin de prueba';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                'Esto eliminara todos los datos actuales y dejara solo un admin de prueba. Deseas continuar?',
                false
            );

            if (!$confirmed) {
                $this->warn('Operacion cancelada.');
                return self::SUCCESS;
            }
        }

        if (app()->environment('production') && !$this->option('force')) {
            $this->error('En produccion debes usar --force para ejecutar este comando.');
            return self::FAILURE;
        }

        $this->info('Reiniciando base de datos con migrate:fresh...');
        $this->call('migrate:fresh', ['--force' => true]);

        $adminRole = UserRole::updateOrCreate(
            ['role_name' => 'Admin'],
            [
                'role_description' => 'Administrador del sistema con acceso total',
                'role_color' => '#dc3545',
                'is_active' => true,
            ]
        );

        User::query()->delete();

        $user = User::create([
            'user_name' => (string) $this->option('name'),
            'user_email' => (string) $this->option('email'),
            'password' => Hash::make((string) $this->option('password')),
            'document_number' => $this->option('document') ?: null,
            'role_id' => $adminRole->role_id,
            'is_active' => true,
        ]);

        $this->newLine();
        $this->info('Base de datos reiniciada correctamente.');
        $this->line('Usuario admin de prueba creado:');
        $this->line('- ID: ' . $user->user_id);
        $this->line('- Nombre: ' . $user->user_name);
        $this->line('- Correo: ' . $user->user_email);
        $this->line('- Rol: Admin');
        $this->line('- Contrasena: ' . (string) $this->option('password'));

        return self::SUCCESS;
    }
}
