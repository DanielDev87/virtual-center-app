<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswordCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function command_resets_password_for_existing_user(): void
    {
        $user = User::factory()->create([
            'user_email' => 'reset_test_user@example.com',
            'password'   => Hash::make('old_password'),
        ]);

        $this->artisan('user:reset-password', [
            'email'    => 'reset_test_user@example.com',
            'password' => 'new_secret_123',
        ])
            ->expectsOutputToContain('Contraseña actualizada para')
            ->assertExitCode(0);

        $user->refresh();
        $this->assertTrue(Hash::check('new_secret_123', $user->password));
    }

    /** @test */
    public function command_errors_when_user_not_found(): void
    {
        $this->artisan('user:reset-password', [
            'email' => 'nonexistent_xyz@example.com',
        ])
            ->expectsOutputToContain('Usuario no encontrado')
            ->assertExitCode(1);
    }

    /** @test */
    public function command_uses_default_password_when_not_provided(): void
    {
        $user = User::factory()->create([
            'user_email' => 'default_pwd_test@example.com',
            'password'   => Hash::make('something_old'),
        ]);

        $this->artisan('user:reset-password', [
            'email' => 'default_pwd_test@example.com',
        ])->assertExitCode(0);

        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password));
    }
}
