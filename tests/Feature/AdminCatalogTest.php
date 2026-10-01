<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\RequestType;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $contributor;
    protected $contributorTwo;
    protected $requester;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole       = UserRole::firstOrCreate(['role_name' => 'Admin'],       ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $requesterRole   = UserRole::firstOrCreate(['role_name' => 'Requester'],   ['is_active' => true]);

        $this->admin = User::create([
            'user_name'  => 'Admin Catalog Test',
            'user_email' => 'admin_catalog_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $adminRole->role_id,
            'is_active'  => true,
        ]);

        // Contributor needed as gestor_id for RequestType
        $this->contributor = User::create([
            'user_name'  => 'Contributor Catalog Test',
            'user_email' => 'contributor_catalog_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $contributorRole->role_id,
            'is_active'  => true,
        ]);

        $this->contributorTwo = User::create([
            'user_name'  => 'Contributor Catalog Test Two',
            'user_email' => 'contributor_catalog_two_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $contributorRole->role_id,
            'is_active'  => true,
        ]);

        $this->requester = User::create([
            'user_name'  => 'Requester Catalog Test',
            'user_email' => 'requester_catalog_' . uniqid() . '@test.com',
            'password'   => self::PASSWORD_HASH,
            'role_id'    => $requesterRole->role_id,
            'is_active'  => true,
        ]);
    }

    // =========================================================================
    //  Tipos de Solicitud (Request Types / Tópicos)
    // =========================================================================

    /** @test */
    public function admin_can_create_a_request_type()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.request-types.store'), [
                'type_name'  => 'Nuevo Tópico Test ' . uniqid(),
                'gestor_id'  => $this->contributor->user_id,
                'is_active'  => '1',
            ]);

        $response->assertRedirect(route('admin.request-types.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_assign_operario_as_request_type_responsible()
    {
        $operarioRole = UserRole::firstOrCreate(['role_name' => 'Operario'], ['is_active' => true]);
        $operario = User::create([
            'user_name' => 'Operario Catalog Test',
            'user_email' => 'operario_catalog_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $operarioRole->role_id,
            'is_active' => true,
        ]);

        $createResponse = $this->actingAs($this->admin)
            ->get(route('admin.request-types.create'));

        $createResponse->assertOk();
        $createResponse->assertSee('Operario Catalog Test');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.request-types.store'), [
                'type_name' => 'Tópico Operario Test ' . uniqid(),
                'collaborator_ids' => [$operario->user_id],
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.request-types.index'));
        $requestType = RequestType::where('type_name', 'like', 'Tópico Operario Test %')->latest('type_id')->first();

        $this->assertNotNull($requestType);
        $this->assertDatabaseHas('request_type_user', [
            'request_type_id' => $requestType->type_id,
            'user_id' => $operario->user_id,
        ]);
    }

    /** @test */
    public function admin_cannot_create_request_type_without_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.request-types.store'), []);

        $response->assertSessionHasErrors(['type_name']);
    }

    /** @test */
    public function admin_cannot_create_request_type_without_any_global_or_regional_responsible()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.request-types.store'), [
                'type_name' => 'Tópico Sin Responsable ' . uniqid(),
            ]);

        $response->assertSessionHasErrors(['regional_assignments']);
    }

    /** @test */
    public function admin_can_update_a_request_type()
    {
        $rt = RequestType::create([
            'type_name'  => 'Tópico Para Editar ' . uniqid(),
            'gestor_id'  => $this->contributor->user_id,
            'is_active'  => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.request-types.update', $rt->type_id), [
                'type_name'  => 'Tópico Editado',
                'gestor_id'  => $this->contributor->user_id,
                'is_active'  => '1',
            ]);

        $response->assertRedirect(route('admin.request-types.index'));
        $this->assertDatabaseHas('request_types', [
            'type_id'   => $rt->type_id,
            'type_name' => 'Tópico Editado',
        ]);
    }

    /** @test */
    public function admin_can_assign_multiple_regional_collaborators_to_a_request_type()
    {
        if (!Schema::hasTable('request_type_regional_assignments')) {
            $this->markTestSkipped('La tabla request_type_regional_assignments no existe en esta base de pruebas.');
        }

        $institution = Institution::create([
            'institution_name' => 'Regional Multi ' . uniqid(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.request-types.store'), [
                'type_name' => 'Tópico Regional Multi ' . uniqid(),
                'collaborator_ids' => [$this->contributor->user_id],
                'regional_assignments' => [
                    $institution->institution_id => [
                        $this->contributor->user_id,
                        $this->contributorTwo->user_id,
                    ],
                ],
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.request-types.index'));

        $requestType = RequestType::where('type_name', 'like', 'Tópico Regional Multi %')->latest('type_id')->first();
        $this->assertNotNull($requestType);

        $this->assertDatabaseHas('request_type_regional_assignments', [
            'request_type_id' => $requestType->type_id,
            'institution_id' => $institution->institution_id,
            'user_id' => $this->contributor->user_id,
        ]);

        $this->assertDatabaseHas('request_type_regional_assignments', [
            'request_type_id' => $requestType->type_id,
            'institution_id' => $institution->institution_id,
            'user_id' => $this->contributorTwo->user_id,
        ]);
    }

    /** @test */
    public function admin_can_assign_area_admin_as_regional_leader_for_a_request_type()
    {
        if (!Schema::hasTable('request_type_regional_assignments')) {
            $this->markTestSkipped('La tabla request_type_regional_assignments no existe en esta base de pruebas.');
        }

        $areaAdminRole = UserRole::firstOrCreate(['role_name' => 'Admin Área'], ['is_active' => true]);
        $areaAdminUser = User::create([
            'user_name' => 'Area Admin Regional ' . uniqid(),
            'user_email' => 'area_admin_regional_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $areaAdminRole->role_id,
            'is_active' => true,
        ]);

        $institution = Institution::create([
            'institution_name' => 'Regional Lider ' . uniqid(),
            'is_active' => true,
        ]);

        $typeName = 'Tópico Lider Regional ' . uniqid();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.request-types.store'), [
                'type_name' => $typeName,
                'regional_assignments' => [
                    $institution->institution_id => [$areaAdminUser->user_id],
                ],
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.request-types.index'));

        $requestType = RequestType::where('type_name', $typeName)->first();
        $this->assertNotNull($requestType);

        $this->assertDatabaseHas('request_type_regional_assignments', [
            'request_type_id' => $requestType->type_id,
            'institution_id' => $institution->institution_id,
            'user_id' => $areaAdminUser->user_id,
        ]);

        $this->assertSame((int) $areaAdminUser->user_id, (int) $requestType->gestor_id);
    }

    /** @test */
    public function admin_can_delete_request_type_without_tickets()
    {
        $rt = RequestType::create([
            'type_name'  => 'Tópico Para Eliminar ' . uniqid(),
            'gestor_id'  => $this->contributor->user_id,
            'is_active'  => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.request-types.destroy', $rt->type_id));

        $response->assertRedirect(route('admin.request-types.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('request_types', ['type_id' => $rt->type_id]);
    }

    /** @test */
    public function admin_cannot_delete_request_type_with_associated_tickets()
    {
        $rt = RequestType::create([
            'type_name'  => 'Tópico Con Tickets ' . uniqid(),
            'gestor_id'  => $this->contributor->user_id,
            'is_active'  => true,
        ]);

        Ticket::create([
            'title'           => 'Ticket bloqueador',
            'ticket_number'   => time() . rand(100, 999),
            'status'          => 1,
            'type'            => 1,
            'request_type_id' => $rt->type_id,
            'requester_id'    => $this->requester->user_id,
            'resume_number'   => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.request-types.destroy', $rt->type_id));

        $response->assertRedirect(route('admin.request-types.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('request_types', ['type_id' => $rt->type_id]);
    }

    // =========================================================================
    //  Instituciones
    // =========================================================================

    /** @test */
    public function admin_can_create_an_institution()
    {
        $name = 'Institución Test ' . uniqid();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.academic.institutions.store'), [
                'institution_name' => $name,
            ]);

        $response->assertRedirect(route('admin.academic.institutions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('institutions', ['institution_name' => $name]);
    }

    /** @test */
    public function admin_cannot_create_institution_without_name()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.academic.institutions.store'), []);

        $response->assertSessionHasErrors('institution_name');
    }

    /** @test */
    public function admin_can_update_an_institution()
    {
        $institution = Institution::create([
            'institution_name' => 'Institución Original ' . uniqid(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.academic.institutions.update', $institution->institution_id), [
                'institution_name' => 'Institución Actualizada',
            ]);

        $response->assertRedirect(route('admin.academic.institutions.index'));
        $this->assertDatabaseHas('institutions', [
            'institution_id'   => $institution->institution_id,
            'institution_name' => 'Institución Actualizada',
        ]);
    }

    /** @test */
    public function admin_can_deactivate_an_institution()
    {
        $institution = Institution::create([
            'institution_name' => 'Institución A Desactivar ' . uniqid(),
            'is_active'        => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.academic.institutions.destroy', $institution->institution_id));

        $response->assertRedirect(route('admin.academic.institutions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('institutions', [
            'institution_id' => $institution->institution_id,
            'is_active'      => false,
        ]);
    }

    // =========================================================================
    //  Control de acceso — rutas admin bloqueadas para no-admin
    // =========================================================================

    /** @test */
    public function requester_cannot_access_admin_request_types_index()
    {
        $response = $this->actingAs($this->requester)
            ->get(route('admin.request-types.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function requester_cannot_create_request_type()
    {
        $response = $this->actingAs($this->requester)
            ->post(route('admin.request-types.store'), [
                'type_name' => 'Intento No Autorizado',
                'gestor_id' => $this->contributor->user_id,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function requester_cannot_access_institutions_index()
    {
        $response = $this->actingAs($this->requester)
            ->get(route('admin.academic.institutions.index'));

        $response->assertStatus(403);
    }
}
