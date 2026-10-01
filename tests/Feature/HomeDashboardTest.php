<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\MaterialType;
use App\Models\ProjectTracking;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomeDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $contributor;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);

        $this->admin = User::create([
            'user_name' => 'Admin Dashboard Test',
            'user_email' => 'admin_dashboard_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $adminRole->role_id,
            'is_active' => true,
        ]);

        $this->contributor = User::create([
            'user_name' => 'Contributor Dashboard Test',
            'user_email' => 'contributor_dashboard_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);
    }

    private function makeProject(string $name, bool $active = true): ProjectTracking
    {
        $institution = Institution::create([
            'institution_name' => 'Inst ' . uniqid(),
            'is_active' => true,
        ]);

        $materialType = MaterialType::create([
            'material_type_name' => 'Material ' . uniqid(),
            'is_active' => true,
        ]);

        return ProjectTracking::create([
            'project_name' => $name,
            'project_description' => 'Descripcion ' . $name,
            'institution_id' => $institution->institution_id,
            'material_type_id' => $materialType->material_type_id,
            'project_status' => 'pending',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'project_notes' => 'Notas de prueba',
            'is_active' => $active,
        ]);
    }

    /** @test */
    public function home_page_is_accessible_publicly()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }

    /** @test */
    public function radio_station_endpoint_is_not_accessible()
    {
        $response = $this->get('/radio-station');

        $response->assertStatus(404);
    }

    /** @test */
    public function ajax_search_returns_only_active_projects_matching_query()
    {
        $matching = $this->makeProject('Proyecto Alpha', true);
        $this->makeProject('Proyecto Beta', true);
        $this->makeProject('Proyecto Alpha Inactivo', false);

        $response = $this->get(route('ajax.search', ['q' => 'Alpha']));

        $response->assertStatus(200)
            ->assertJsonFragment(['tracking_id' => $matching->tracking_id, 'project_name' => 'Proyecto Alpha'])
            ->assertJsonMissing(['project_name' => 'Proyecto Beta'])
            ->assertJsonMissing(['project_name' => 'Proyecto Alpha Inactivo']);
    }

    /** @test */
    public function ajax_project_details_returns_requested_project()
    {
        $project = $this->makeProject('Proyecto Detalle', true);

        $response = $this->get(route('ajax.project-details', $project->tracking_id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'tracking_id' => $project->tracking_id,
                'project_name' => 'Proyecto Detalle',
            ]);
    }

    /** @test */
    public function ajax_project_details_returns_404_for_unknown_project()
    {
        $response = $this->get(route('ajax.project-details', 99999999));

        $response->assertStatus(404);
    }

    /** @test */
    public function ajax_send_status_updates_project_status_and_notes()
    {
        $project = $this->makeProject('Proyecto Estado', true);

        $response = $this->post(route('ajax.send-status'), [
            'project_id' => $project->tracking_id,
            'status' => 'completed',
            'notes' => 'Estado actualizado desde test',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('project_tracking', [
            'tracking_id' => $project->tracking_id,
            'project_status' => 'completed',
            'project_notes' => 'Estado actualizado desde test',
        ]);
    }

    /** @test */
    public function ajax_send_status_validates_required_fields()
    {
        $response = $this->postJson(route('ajax.send-status'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'status']);
    }

    /** @test */
    public function ajax_theme_endpoint_accepts_valid_theme_and_persists_in_session()
    {
        $response = $this->postJson(route('ajax.theme'), [
            'theme' => 'dark',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSame('dark', session('theme'));
    }

    /** @test */
    public function ajax_theme_endpoint_rejects_invalid_theme()
    {
        $response = $this->postJson(route('ajax.theme'), [
            'theme' => 'blue',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theme']);
    }

    /** @test */
    public function dashboard_is_accessible_to_admin()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function dashboard_is_forbidden_for_non_admin_authenticated_user()
    {
        $response = $this->actingAs($this->contributor)
            ->get(route('dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function dashboard_redirects_unauthenticated_user_to_login()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
