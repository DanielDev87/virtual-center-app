<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminRole;
    protected $contributorRole;
    protected $monitorRole;
    protected $requesterRole;

    // Bcrypt hash de la contraseña 'password' (mismo que usan los otros tests)
    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    protected const RAW_PASSWORD  = 'password';

    protected function setUp(): void
    {
        parent::setUp();

        // firstOrCreate garantiza que no se rompa la BD de desarrollo
        $this->adminRole       = UserRole::firstOrCreate(['role_name' => 'Admin'],       ['is_active' => true]);
        $this->contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $this->monitorRole     = UserRole::firstOrCreate(['role_name' => 'Monitor'],     ['is_active' => true]);
        $this->requesterRole   = UserRole::firstOrCreate(['role_name' => 'Requester'],   ['is_active' => true]);
    }

    // -----------------------------------------------------------------------
    //  Helpers
    // -----------------------------------------------------------------------

    private function makeUser(UserRole $role, bool $isActive = true): User
    {
        return User::create([
            'user_name'  => 'Test User ' . uniqid(),
            'user_email' => 'auth_test_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $role->role_id,
            'is_active'  => $isActive,
        ]);
    }

    // -----------------------------------------------------------------------
    //  1. Página de login pública
    // -----------------------------------------------------------------------

    /** @test */
    public function login_page_is_publicly_accessible()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    // -----------------------------------------------------------------------
    //  2. Login con credenciales incorrectas
    // -----------------------------------------------------------------------

    /** @test */
    public function login_fails_with_wrong_password()
    {
        $user = $this->makeUser($this->requesterRole);

        $response = $this->post('/login', [
            'email'    => $user->user_email,
            'password' => 'contraseña_incorrecta',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function login_fails_with_nonexistent_email()
    {
        $response = $this->post('/login', [
            'email'    => 'noexiste_' . uniqid() . '@test.com',
            'password' => self::RAW_PASSWORD,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function login_fails_when_required_fields_are_missing()
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    // -----------------------------------------------------------------------
    //  3. Cuenta inactiva
    // -----------------------------------------------------------------------

    /** @test */
    public function inactive_user_cannot_login()
    {
        $inactiveUser = $this->makeUser($this->requesterRole, false);

        $response = $this->post('/login', [
            'email'    => $inactiveUser->user_email,
            'password' => self::RAW_PASSWORD,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -----------------------------------------------------------------------
    //  4. Redirección por rol tras login exitoso
    // -----------------------------------------------------------------------

    /** @test */
    public function admin_is_redirected_to_admin_tickets_after_login()
    {
        $admin = $this->makeUser($this->adminRole);

        $response = $this->post('/login', [
            'email'    => $admin->user_email,
            'password' => self::RAW_PASSWORD,
        ]);

        $response->assertRedirect(route('admin.tickets.index'));
        $this->assertAuthenticatedAs($admin);
    }

    /** @test */
    public function contributor_is_redirected_to_contributor_dashboard_after_login()
    {
        $contributor = $this->makeUser($this->contributorRole);

        $response = $this->post('/login', [
            'email'    => $contributor->user_email,
            'password' => self::RAW_PASSWORD,
        ]);

        $response->assertRedirect(route('contributors.dashboard'));
        $this->assertAuthenticatedAs($contributor);
    }

    /** @test */
    public function monitor_is_redirected_to_monitor_panel_after_login()
    {
        $monitor = $this->makeUser($this->monitorRole);

        $response = $this->post('/login', [
            'email'    => $monitor->user_email,
            'password' => self::RAW_PASSWORD,
        ]);

        $response->assertRedirect(route('monitor.index'));
        $this->assertAuthenticatedAs($monitor);
    }

    /** @test */
    public function requester_is_redirected_to_service_management_after_login()
    {
        $requester = $this->makeUser($this->requesterRole);

        $response = $this->post('/login', [
            'email'    => $requester->user_email,
            'password' => self::RAW_PASSWORD,
        ]);

        $response->assertRedirect(route('service-management.index'));
        $this->assertAuthenticatedAs($requester);
    }

    // -----------------------------------------------------------------------
    //  5. Logout
    // -----------------------------------------------------------------------

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = $this->makeUser($this->requesterRole);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // -----------------------------------------------------------------------
    //  6. Protección de rutas (usuario no autenticado)
    // -----------------------------------------------------------------------

    /** @test */
    public function unauthenticated_user_is_redirected_to_login_when_accessing_dashboard()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login_when_accessing_contributor_area()
    {
        $response = $this->get(route('contributors.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login_when_accessing_monitor_area()
    {
        $response = $this->get(route('monitor.index'));

        $response->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    //  7. Control de acceso por rol (usuario autenticado con rol incorrecto)
    // -----------------------------------------------------------------------

    /** @test */
    public function requester_cannot_access_admin_routes()
    {
        $requester = $this->makeUser($this->requesterRole);

        $response = $this->actingAs($requester)->get(route('admin.tickets.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function requester_cannot_access_contributor_dashboard()
    {
        $requester = $this->makeUser($this->requesterRole);

        $response = $this->actingAs($requester)->get(route('contributors.dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function contributor_cannot_access_admin_routes()
    {
        $contributor = $this->makeUser($this->contributorRole);

        $response = $this->actingAs($contributor)->get(route('admin.tickets.index'));

        $response->assertStatus(403);
    }
}
