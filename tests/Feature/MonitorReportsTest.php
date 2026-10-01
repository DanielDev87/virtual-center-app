<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MonitorReportsTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $monitor;
    protected $contributor;
    protected $requester;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole       = UserRole::firstOrCreate(['role_name' => 'Admin'],       ['is_active' => true]);
        $monitorRole     = UserRole::firstOrCreate(['role_name' => 'Monitor'],     ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $requesterRole   = UserRole::firstOrCreate(['role_name' => 'Requester'],   ['is_active' => true]);

        $this->admin = User::create([
            'user_name'  => 'Admin Monitor Test',
            'user_email' => 'admin_monitor_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $adminRole->role_id,
            'is_active'  => true,
        ]);

        $this->monitor = User::create([
            'user_name'  => 'Monitor Test',
            'user_email' => 'monitor_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $monitorRole->role_id,
            'is_active'  => true,
        ]);

        $this->contributor = User::create([
            'user_name'  => 'Contributor Monitor Test',
            'user_email' => 'contributor_monitor_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $contributorRole->role_id,
            'is_active'  => true,
        ]);

        $this->requester = User::create([
            'user_name'  => 'Requester Monitor Test',
            'user_email' => 'requester_monitor_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $requesterRole->role_id,
            'is_active'  => true,
        ]);
    }

    // =========================================================================
    //  Panel Monitor — acceso por rol
    // =========================================================================

    /** @test */
    public function monitor_user_can_access_monitor_panel()
    {
        $response = $this->actingAs($this->monitor)
            ->get(route('monitor.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_monitor_panel()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('monitor.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function contributor_cannot_access_monitor_panel()
    {
        $response = $this->actingAs($this->contributor)
            ->get(route('monitor.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function requester_cannot_access_monitor_panel()
    {
        $response = $this->actingAs($this->requester)
            ->get(route('monitor.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_from_monitor_panel()
    {
        $response = $this->get(route('monitor.index'));

        $response->assertRedirect(route('login'));
    }

    // =========================================================================
    //  Monitor — sub-secciones
    // =========================================================================

    /** @test */
    public function monitor_user_can_access_monitor_reports_section()
    {
        $response = $this->actingAs($this->monitor)
            ->get(route('monitor.reports'));

        $response->assertStatus(200);
    }

    /** @test */
    public function monitor_user_can_access_monitor_analytics_section()
    {
        $response = $this->actingAs($this->monitor)
            ->get(route('monitor.analytics'));

        $response->assertStatus(200);
    }

    // =========================================================================
    //  Monitor — solo lectura (no puede hacer POST)
    // =========================================================================

    /** @test */
    public function monitor_user_is_blocked_from_post_actions_on_admin_routes()
    {
        // El middleware CheckRole bloquea al Monitor en métodos no-GET
        $response = $this->actingAs($this->monitor)
            ->post(route('admin.request-types.store'), [
                'type_name' => 'Intento Monitor',
                'gestor_id' => $this->contributor->user_id,
            ]);

        // Debería redireccionar con error de acceso, no procesar
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // =========================================================================
    //  Reportes Admin — acceso por rol
    // =========================================================================

    /** @test */
    public function admin_can_access_reports_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function monitor_can_access_reports_dashboard()
    {
        $response = $this->actingAs($this->monitor)
            ->get(route('admin.reports.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function contributor_cannot_access_reports_dashboard()
    {
        $response = $this->actingAs($this->contributor)
            ->get(route('admin.reports.index'));

        $response->assertStatus(403);
    }

    // =========================================================================
    //  Reportes Admin — regla de negocio: requiere al menos un filtro
    // =========================================================================

    /** @test */
    public function tickets_report_without_filters_redirects_with_error()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.tickets'));

        $response->assertRedirect(route('admin.reports.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function collaborators_report_without_filters_redirects_with_error()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.collaborators'));

        $response->assertRedirect(route('admin.reports.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function progress_report_without_filters_redirects_with_error()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.progress'));

        $response->assertRedirect(route('admin.reports.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function tickets_report_with_status_filter_returns_response()
    {
        // Crear un ticket para que el reporte tenga datos
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.tickets', ['status' => 1]));

        // Con filtro válido debe procesar (no redirigir con error)
        $response->assertSuccessful();
    }
}
