<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoleMiddlewareAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    private function makeUser(string $roleName): User
    {
        $role = UserRole::firstOrCreate(['role_name' => $roleName], ['is_active' => true]);

        return User::create([
            'user_name' => 'Role middleware test ' . $roleName . ' ' . uniqid(),
            'user_email' => 'role_middleware_' . strtolower(str_replace(' ', '_', $roleName)) . '_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_access_dashboard_route()
    {
        $admin = $this->makeUser('Admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_cannot_access_super_admin_technical_route()
    {
        $admin = $this->makeUser('Admin');

        $response = $this->actingAs($admin)->get(route('technical.storage-settings.edit'));

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_tecnico_can_access_technical_storage_settings()
    {
        $superAdmin = $this->makeUser('Super Admin Tecnico');

        $response = $this->actingAs($superAdmin)->get(route('technical.storage-settings.edit'));

        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_tecnico_cannot_access_admin_routes()
    {
        $superAdmin = $this->makeUser('Super Admin Tecnico');

        $response = $this->actingAs($superAdmin)->get(route('admin.tickets.index'));

        $response->assertStatus(403);
    }
}
