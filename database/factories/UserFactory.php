<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Nombre del modelo correspondiente a esta fábrica.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Definir el estado predeterminado del modelo.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_name' => $this->faker->name(),
            'user_email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // contraseña
            'user_phone' => $this->faker->phoneNumber(),
            'user_bio' => $this->faker->text(),
            'user_avatar' => null,
            'role_id' => 1, // Predeterminado, sobrescribir en pruebas
            'is_active' => true,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
