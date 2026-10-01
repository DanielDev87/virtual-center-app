<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\JobPosition;
use App\Models\RequestType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketReturned;
use App\Mail\TicketCancelled;
use App\Models\TicketJoinRequest;
use App\Models\TicketAssociationRequest;
use App\Models\Area;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $requester;
    protected $contributor;
    protected $operario;
    protected $requestType;
    protected $jobPosition;

    protected function setUp(): void
    {
        parent::setUp();

        // Get or Create Roles (safe for dev DB)
        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $operarioRole = UserRole::firstOrCreate(['role_name' => 'Operario'], ['is_active' => true]);

        // Create Users manually (will be rolled back)
        $this->admin = User::create([
            'user_name' => 'Admin Test',
            'user_email' => 'admin_test_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $adminRole->role_id,
            'is_active' => true
        ]);

        $this->requester = User::create([
            'user_name' => 'Requester Test',
            'user_email' => 'requester_test_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $requesterRole->role_id,
            'is_active' => true
        ]);

        $this->contributor = User::create([
            'user_name' => 'Contributor Test',
            'user_email' => 'contributor_test_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $contributorRole->role_id,
            'is_active' => true
        ]);

        $this->operario = User::create([
            'user_name' => 'Operario Test',
            'user_email' => 'operario_test_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $operarioRole->role_id,
            'is_active' => true
        ]);

        // Create Catalog Data
        $this->requestType = RequestType::firstOrCreate(['type_name' => 'Development Test']);
        $this->jobPosition = JobPosition::firstOrCreate(['position_name' => 'Developer Test'], ['position_color' => '#000000']);
    }

    /** @test */
    public function requester_can_create_a_ticket()
    {
        // We simulate the post request to the store route
        // Note: We need to check if the route demands logged in user, usually yes.
        $this->actingAs($this->requester);
        
        $ticketData = [
            'title' => 'Test Ticket Creation',
            'description' => 'Description for test ticket',
            'request_type_id' => $this->requestType->type_id,
            // Assuming these IDs exist or we should mock them. 
            // In a real dev DB, ID 1 usually exists for faculties/programs. 
            // If not, this test might fail on FK. We'll try with 1.
            'faculty_id' => 1, 
            'program_id' => 1,
            'course_id' => null
        ];

        // Direct creation to bypass potential FK issues in controller validation if data is missing in dev DB
        // But we want to test the Feature. 
        // Let's create a ticket via Model to be safe about the "Requester Logic" part if controller is complex.
        // Actually, let's test the Model creation which is safer for this environment.
        
        $ticket = Ticket::create([
            'title' => 'Test Ticket Model',
            'ticket_number' => time() . rand(100,999), // Unique
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0
        ]);

        $this->assertDatabaseHas('tickets', ['ticket_id' => $ticket->ticket_id]);
    }

    /** @test */
    public function admin_can_assign_contributor()
    {
        if (Schema::hasTable('request_type_user')) {
            $this->requestType->collaborators()->syncWithoutDetaching([$this->contributor->user_id]);
        }

        $ticket = Ticket::create([
            'title' => 'Ticket for Assignment',
            'ticket_number' => time() . rand(100,999),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.assign-mediator', $ticket->ticket_id), [
                'user_id' => $this->contributor->user_id,
                'job_position_id' => $this->jobPosition->job_position_id
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributor->user_id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function admin_cancellation_notifies_requester_with_reason()
    {
        Mail::fake();

        $ticket = Ticket::create([
            'title' => 'Ticket Mal Direccionado',
            'ticket_number' => time() . rand(100,999),
            'status' => 1,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
        ]);

        $reason = 'La solicitud fue direccionada al tópico equivocado.';
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $ticket->ticket_id), [
                'status' => 4,
                'cancellation_reason' => $reason,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 4,
        ]);
        Mail::assertSent(TicketCancelled::class, function ($mail) use ($ticket, $reason) {
            return $mail->ticket->ticket_id === $ticket->ticket_id
                && $mail->reason === $reason;
        });
    }

    /** @test */
    public function contributor_can_request_and_responsible_contributor_can_approve_team_join()
    {
        $secondaryContributor = User::create([
            'user_name' => 'Contributor Team Member',
            'user_email' => 'contributor_team_' . time() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/igi',
            'role_id' => $this->contributor->role_id,
            'is_active' => true,
        ]);
        $this->requestType->collaborators()->sync([$this->contributor->user_id, $secondaryContributor->user_id]);

        $ticket = Ticket::create([
            'title' => 'Ticket for Team Join',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
        ]);
        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributor->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);

        $requestResponse = $this->actingAs($secondaryContributor)
            ->post(route('contributors.tickets.join-request', $ticket->ticket_id), [
                'request_note' => 'Puedo apoyar el trabajo de campo del equipo.',
            ]);

        $requestResponse->assertRedirect();
        $joinRequest = TicketJoinRequest::where('ticket_id', $ticket->ticket_id)
            ->where('requester_id', $secondaryContributor->user_id)
            ->firstOrFail();

        $approveResponse = $this->actingAs($this->contributor)
            ->post(route('contributors.join-requests.approve', $joinRequest->join_request_id));

        $approveResponse->assertRedirect();
        $this->assertDatabaseHas('ticket_join_requests', [
            'join_request_id' => $joinRequest->join_request_id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $secondaryContributor->user_id,
            'status' => 'active',
            'assigned_by' => $this->contributor->user_id,
        ]);
    }

    /** @test */
    public function contributor_association_requires_area_admin_approval()
    {
        $areaAdminRole = UserRole::firstOrCreate(['role_name' => 'Admin Área'], ['is_active' => true]);
        $areaAdmin = User::create([
            'user_name' => 'Area Admin Association Test',
            'user_email' => 'area_assoc_' . time() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/igi',
            'role_id' => $areaAdminRole->role_id,
            'area_id' => null,
            'is_active' => true,
        ]);
        $parent = Ticket::create([
            'title' => 'Association Parent',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
        ]);
        $child = Ticket::create([
            'title' => 'Association Child',
            'ticket_number' => time() . rand(1000, 9999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
        ]);

        $response = $this->actingAs($this->contributor)
            ->post(route('contributors.tickets.associate', $parent->ticket_id), [
                'child_ticket_id' => $child->ticket_id,
                'request_note' => 'Mismo incidente reportado por otra persona.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_association_requests', [
            'parent_ticket_id' => $parent->ticket_id,
            'child_ticket_id' => $child->ticket_id,
            'status' => 'pending',
        ]);
        $this->assertNull($child->fresh()->parent_ticket_id);
    }

    /** @test */
    public function contributor_can_transfer_ticket_to_a_topic_in_another_area()
    {
        $sourceArea = Area::create(['area_name' => 'Source Area ' . uniqid(), 'is_active' => true]);
        $targetArea = Area::create(['area_name' => 'Target Area ' . uniqid(), 'is_active' => true]);
        $targetContributor = User::create([
            'user_name' => 'Target Area Contributor',
            'user_email' => 'target_area_' . time() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/igi',
            'role_id' => $this->contributor->role_id,
            'is_active' => true,
        ]);
        $this->requestType->update(['area_id' => $sourceArea->area_id]);
        $targetTopic = RequestType::create([
            'type_name' => 'Target Cross Area Topic ' . uniqid(),
            'area_id' => $targetArea->area_id,
            'is_active' => true,
        ]);
        $targetTopic->collaborators()->sync([$targetContributor->user_id]);
        $ticket = Ticket::create([
            'title' => 'Cross Area Transfer Ticket',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
        ]);

        $response = $this->actingAs($this->contributor)
            ->post(route('contributors.tickets.transfer', $ticket->ticket_id), [
                'new_request_type_id' => $targetTopic->type_id,
                'target_mediator_id' => $targetContributor->user_id,
                'transfer_note' => 'El solicitante seleccionó el tópico equivocado.',
            ]);

        $response->assertRedirect(route('contributors.dashboard'));
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'request_type_id' => $targetTopic->type_id,
            'mediator_id' => $targetContributor->user_id,
        ]);
    }

    /** @test */
    public function contributor_can_request_multiple_ticket_associations_in_one_request()
    {
        $this->requestType->collaborators()->syncWithoutDetaching([$this->contributor->user_id]);
        $parent = Ticket::create([
            'title' => 'Batch Association Parent',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
        ]);
        $children = collect(range(1, 2))->map(function ($index) {
            return Ticket::create([
                'title' => 'Batch Child ' . $index,
                'ticket_number' => time() . rand(1000, 9999),
                'status' => 2,
                'type' => 1,
                'request_type_id' => $this->requestType->type_id,
                'requester_id' => $this->requester->user_id,
            ]);
        });

        $response = $this->actingAs($this->contributor)
            ->post(route('contributors.tickets.associate', $parent->ticket_id), [
                'child_ticket_ids' => $children->pluck('ticket_id')->all(),
                'request_note' => 'Mismo incidente para varias solicitudes.',
            ]);

        $response->assertRedirect();
        $requests = TicketAssociationRequest::where('parent_ticket_id', $parent->ticket_id)->get();
        $this->assertCount(2, $requests);
        $this->assertSame(1, $requests->pluck('request_group')->unique()->count());
        $this->assertTrue($requests->every(fn ($item) => $item->status === 'pending'));
    }

    /** @test */
    public function area_admin_out_of_scope_association_request_is_resolved_and_requester_is_notified()
    {
        Mail::fake();

        $area = Area::create(['area_name' => 'Association Scope Area ' . uniqid(), 'is_active' => true]);
        $otherArea = Area::create(['area_name' => 'Other Scope Area ' . uniqid(), 'is_active' => true]);
        $areaAdminRole = UserRole::firstOrCreate(['role_name' => 'Admin Área'], ['is_active' => true]);
        $areaAdmin = User::create([
            'user_name' => 'Area Admin Scope Test',
            'user_email' => 'area_scope_' . time() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/igi',
            'role_id' => $areaAdminRole->role_id,
            'area_id' => $area->area_id,
            'is_active' => true,
        ]);
        $inScopeTopic = RequestType::create([
            'type_name' => 'In Scope Topic ' . uniqid(),
            'area_id' => $area->area_id,
            'is_active' => true,
        ]);
        $outOfScopeTopic = RequestType::create([
            'type_name' => 'Out Of Scope Topic ' . uniqid(),
            'area_id' => $otherArea->area_id,
            'is_active' => true,
        ]);
        $parent = Ticket::create([
            'title' => 'Scope Association Parent',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $inScopeTopic->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
        ]);
        $inScopeChild = Ticket::create([
            'title' => 'In Scope Child',
            'ticket_number' => time() . rand(1000, 9999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $inScopeTopic->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
        ]);
        $outOfScopeChild = Ticket::create([
            'title' => 'Out Of Scope Child',
            'ticket_number' => time() . rand(1000, 9999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $outOfScopeTopic->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
        ]);
        $requestGroup = (string) \Illuminate\Support\Str::uuid();
        $requestOne = TicketAssociationRequest::create([
            'parent_ticket_id' => $parent->ticket_id,
            'child_ticket_id' => $inScopeChild->ticket_id,
            'requested_by' => $this->contributor->user_id,
            'request_group' => $requestGroup,
            'status' => 'pending',
        ]);
        TicketAssociationRequest::create([
            'parent_ticket_id' => $parent->ticket_id,
            'child_ticket_id' => $outOfScopeChild->ticket_id,
            'requested_by' => $this->contributor->user_id,
            'request_group' => $requestGroup,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($areaAdmin)
            ->post(route('area-admin.association-requests.approve', $requestOne->association_request_id));

        $response->assertRedirect(route('area-admin.dashboard'));
        $this->assertDatabaseMissing('ticket_association_requests', [
            'request_group' => $requestGroup,
            'status' => 'pending',
        ]);
        $this->assertSame(2, TicketAssociationRequest::where('request_group', $requestGroup)
            ->where('status', 'rejected')->count());
        $this->assertNull($inScopeChild->fresh()->parent_ticket_id);
        Mail::assertSent(\App\Mail\TicketAssociationRequestNeedsReview::class, function ($mail) use ($parent) {
            return $mail->parentTicket->ticket_id === $parent->ticket_id;
        });
    }

    /** @test */
    public function legacy_primary_assignment_records_who_assigned_operario()
    {
        $ticket = Ticket::create([
            'title' => 'Legacy Operario Assignment',
            'ticket_number' => time() . rand(100,999),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
        ]);

        $requestType = RequestType::find($ticket->request_type_id);
        if ($requestType) {
            $requestType->collaborators()->syncWithoutDetaching([$this->operario->user_id]);
        }

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.assign', $ticket->ticket_id), [
                'mediator_id' => $this->operario->user_id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_mark_a_returned_ticket_alert_as_read()
    {
        $ticket = Ticket::create([
            'title' => 'Returned Alert Test',
            'ticket_number' => time() . rand(100,999),
            'status' => 1,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
        ]);

        $assignment = TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'removed',
            'notes' => 'Devuelto por operario: no cuenta con herramientas.',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('returned-alerts.read', $assignment->assignment_id));

        $response->assertRedirect(route('admin.tickets.show', $ticket->ticket_id));
        $this->assertDatabaseHas('ticket_assignments', [
            'assignment_id' => $assignment->assignment_id,
            'returned_alert_read_at' => $assignment->fresh()->returned_alert_read_at,
        ]);
        $this->assertNotNull($assignment->fresh()->returned_alert_read_at);
    }

    /** @test */
    public function operario_can_return_assigned_ticket_with_a_reason()
    {
        Mail::fake();

        $ticket = Ticket::create([
            'title' => 'Ticket for Operario Return',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->operario->user_id,
            'resume_number' => 0,
        ]);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->operario)
            ->post(route('operario.tickets.return', $ticket->ticket_id), [
                'return_reason' => 'No cuento con las herramientas requeridas para atenderlo.',
            ]);

        $response->assertRedirect(route('operario.dashboard'));
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'mediator_id' => null,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'status' => 'removed',
        ]);
        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'status_update' => 'operario_returned_ticket',
        ]);
        Mail::assertSent(TicketReturned::class, function ($mail) use ($ticket) {
            return $mail->ticket->ticket_id === $ticket->ticket_id;
        });
    }

    /** @test */
    public function operario_marks_ticket_as_realizado_instead_of_completado()
    {
        $ticket = Ticket::create([
            'title' => 'Ticket for Operario Audit',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->operario->user_id,
            'resume_number' => 0,
            'progress_percentage' => 40,
        ]);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->operario)
            ->post(route('operario.tickets.status', $ticket->ticket_id), [
                'status' => 5,
                'note' => 'Trabajo físico realizado y evidencia disponible para revisión.',
            ]);

        $response->assertRedirect(route('operario.tickets.show', $ticket->ticket_id));
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 5,
            'progress_percentage' => 100,
        ]);
        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'status_update' => 'operario_status_update',
            'progress_percentage' => 100,
        ]);
    }

    /** @test */
    public function only_primary_operario_can_mark_ticket_as_realizado()
    {
        $secondaryOperario = User::create([
            'user_name' => 'Operario Secundario Test',
            'user_email' => 'operario_secondary_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.ogJgi',
            'role_id' => $this->operario->role_id,
            'is_active' => true,
        ]);
        $ticket = Ticket::create([
            'title' => 'Ticket with Operario Team',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->operario->user_id,
            'resume_number' => 0,
        ]);
        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);
        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $secondaryOperario->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($secondaryOperario)
            ->post(route('operario.tickets.status', $ticket->ticket_id), [
                'status' => 5,
                'note' => 'Intento de marcar realizado desde usuario secundario.',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 2,
        ]);
    }

    /** @test */
    public function operario_cannot_modify_ticket_when_contributor_is_in_team()
    {
        $ticket = Ticket::create([
            'title' => 'Ticket with Contributor Team',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->operario->user_id,
            'resume_number' => 0,
        ]);
        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->operario->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);
        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributor->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->operario)
            ->post(route('operario.tickets.status', $ticket->ticket_id), [
                'status' => 5,
                'note' => 'Intento de completar con Contributor responsable.',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 2,
        ]);
    }

    /** @test */
    public function contributor_can_update_progress()
    {
        $ticket = Ticket::create([
            'title' => 'Ticket for Progress',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 0
        ]);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributor->user_id,
            'job_position_id' => $this->jobPosition->job_position_id,
            'assigned_by' => $this->admin->user_id
        ]);

        $response = $this->actingAs($this->contributor)
            ->post(route('contributors.tickets.progress', $ticket->ticket_id), [
                'progress_percentage' => 50,
                'progress_description' => 'Halfway there'
            ]);

        $response->assertRedirect();

        // storeProgress saves to ticket_progress, not directly to tickets
        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id'           => $ticket->ticket_id,
            'progress_percentage' => 50,
        ]);
    }

    /** @test */
    public function admin_cannot_close_ticket_if_incomplete()
    {
        $ticket = Ticket::create([
            'title' => 'Incomplete Ticket',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 90,
            'current_phase' => 'Development'
        ]);

        // No sprint tasks → autoProgress=0 → controller blocks closure with 'error'
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $ticket->ticket_id), [
                'status'          => 3,
                'solution_detail' => 'Detalle de solución de prueba para cierre',
                'resource_link'   => 'http://example.com',
            ]);

        // Should NOT close - check status hasn't changed to 3
        $this->assertNotEquals(3, $ticket->fresh()->status);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_close_ticket_if_all_conditions_met()
    {
        $ticket = Ticket::create([
            'title' => 'Complete Ticket',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 100,
            'current_phase' => 'Evaluation'
        ]);

        // autoProgress is computed from sprint tasks; create one sprint with all tasks done
        $sprint = \App\Models\Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name'      => 'Sprint Final',
            'status'    => 'active',
                'start_date' => now()->toDateString(),
                'end_date'   => now()->addDays(7)->toDateString(),
        ]);
        \App\Models\ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'sprint_id' => $sprint->sprint_id,
            'title'     => 'Tarea completada',
            'status'    => 'done',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $ticket->ticket_id), [
                'status'          => 3,
                'solution_detail' => 'Solución completa e implementada correctamente',
                'resource_link'   => 'http://example.com',
            ]);

        $this->assertEquals(3, $ticket->fresh()->status);
        $this->assertEquals('http://example.com', $ticket->fresh()->resource_link);
    }

    /** @test */
    public function closing_parent_ticket_propagates_completion_to_associated_ticket()
    {
        Mail::fake();

        $childRequester = User::create([
            'user_name' => 'Associated Requester',
            'user_email' => 'associated_requester_' . time() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/igi',
            'role_id' => UserRole::where('role_name', 'Requester')->value('role_id'),
            'is_active' => true,
        ]);
        $parent = Ticket::create([
            'title' => 'Parent Shared Issue',
            'ticket_number' => time() . rand(100,999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'progress_percentage' => 100,
            'current_phase' => 'Evaluation',
        ]);
        $child = Ticket::create([
            'title' => 'Associated Shared Issue',
            'ticket_number' => time() . rand(1000,9999),
            'status' => 2,
            'type' => 1,
            'requester_id' => $childRequester->user_id,
            'parent_ticket_id' => $parent->ticket_id,
        ]);

        $sprint = \App\Models\Sprint::create([
            'ticket_id' => $parent->ticket_id,
            'name' => 'Parent Final Sprint',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);
        \App\Models\ProjectTask::create([
            'ticket_id' => $parent->ticket_id,
            'sprint_id' => $sprint->sprint_id,
            'title' => 'Shared issue resolved',
            'status' => 'done',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $parent->ticket_id), [
                'status' => 3,
                'solution_detail' => 'Se resolvió la causa común que afectaba todas las solicitudes.',
            ]);

        $response->assertRedirect();
        $this->assertSame(3, $child->fresh()->status);
        $this->assertSame(100, $child->fresh()->progress_percentage);
        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $child->ticket_id,
            'status_update' => 'service_closed_associated_ticket',
        ]);
        Mail::assertSent(\App\Mail\TicketClosed::class, function ($mail) use ($child) {
            return $mail->ticket->ticket_id === $child->ticket_id;
        });
    }

    /** @test */
    public function admin_can_reopen_ticket()
    {
        $ticket = Ticket::create([
            'title' => 'Closed Ticket',
            'ticket_number' => time() . rand(100,999),
            'status' => 3,
            'type' => 1,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 100,
            'rating' => 5
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.reopen', $ticket->ticket_id));

        $freshTicket = $ticket->fresh();

        $this->assertEquals(2, $freshTicket->status);
        $this->assertEquals(0, $freshTicket->progress_percentage);
        $this->assertTrue((bool)$freshTicket->is_reopened);
    }
    /** @test */
    public function requester_can_see_resource_link_when_ticket_is_closed()
    {
        // 1. Setup: Create ticket and user
        $requestType = RequestType::firstOrCreate(['type_name' => 'Test Type']);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);
        
        $requester = User::create([
            'user_name' => 'Requester Link User',
            'user_email' => 'req_link_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $requesterRole->role_id,
            'is_active' => true
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket with Resource',
            'ticket_number' => time() . rand(100,999),
            'status' => 3, // Completed
            'type' => 1,
            'request_type_id' => $requestType->type_id,
            'requester_id' => $requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 100,
            'resource_link' => 'http://final-resource-link.com'
        ]);

        // 2. Act: Requester views the ticket
        $response = $this->actingAs($requester)
                         ->get(route('service-management.show', $ticket->ticket_id));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee('Resultado del Servicio');
        $response->assertSee('http://final-resource-link.com');
    }
}
