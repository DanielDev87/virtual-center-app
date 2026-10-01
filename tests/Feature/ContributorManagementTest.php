<?php

namespace Tests\Feature;

use App\Models\ProjectTask;
use App\Models\Institution;
use App\Models\RequestType;
use App\Models\Sprint;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContributorManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $contributorA;
    protected $contributorB;
    protected $requester;
    protected $typeA;
    protected $typeB;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $this->contributorA = User::create([
            'user_name' => 'Contributor A Test',
            'user_email' => 'contributor_a_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->contributorB = User::create([
            'user_name' => 'Contributor B Test',
            'user_email' => 'contributor_b_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->requester = User::create([
            'user_name' => 'Requester Contributor Test',
            'user_email' => 'requester_contributor_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $this->typeA = RequestType::firstOrCreate(
            ['type_name' => 'Contributor Type A'],
            ['gestor_id' => $this->contributorA->user_id, 'is_active' => true]
        );

        $this->typeB = RequestType::firstOrCreate(
            ['type_name' => 'Contributor Type B'],
            ['gestor_id' => $this->contributorB->user_id, 'is_active' => true]
        );

        if (Schema::hasTable('request_type_user')) {
            $this->typeA->collaborators()->syncWithoutDetaching([
                $this->contributorA->user_id,
                $this->contributorB->user_id,
            ]);
            $this->typeB->collaborators()->syncWithoutDetaching([$this->contributorB->user_id]);
        }
    }

    private function makeTicket(int $status = 1): Ticket
    {
        return Ticket::create([
            'title' => 'Ticket Contributor Test ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => $status,
            'type' => 1,
            'request_type_id' => $this->typeA->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributorA->user_id,
            'resume_number' => 0,
            'current_phase' => 'Development',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);
    }

    /** @test */
    public function contributor_can_set_priority_on_managed_ticket()
    {
        $ticket = $this->makeTicket(1);

        $response = $this->actingAs($this->contributorA)
            ->post(route('contributors.tickets.priority', $ticket->ticket_id), [
                'priority' => 4,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'priority' => 4,
        ]);
    }

    /** @test */
    public function contributor_cannot_set_priority_on_completed_ticket()
    {
        $ticket = $this->makeTicket(3);

        $response = $this->actingAs($this->contributorA)
            ->post(route('contributors.tickets.priority', $ticket->ticket_id), [
                'priority' => 2,
            ]);

        $response->assertSessionHasErrors('priority');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'priority' => 1,
        ]);
    }

    /** @test */
    public function primary_contributor_can_transfer_ticket_to_other_request_type()
    {
        $ticket = $this->makeTicket(2);

        $response = $this->actingAs($this->contributorA)
            ->post(route('contributors.tickets.transfer', $ticket->ticket_id), [
                'new_request_type_id' => $this->typeB->type_id,
                'transfer_note' => 'Reasignacion por especialidad',
            ]);

        $response->assertRedirect(route('contributors.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'request_type_id' => $this->typeB->type_id,
            'mediator_id' => $this->contributorB->user_id,
        ]);

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributorB->user_id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'status_update' => 'ticket_transferred',
        ]);
    }

    /** @test */
    public function contributor_cannot_transfer_ticket_to_same_request_type()
    {
        $ticket = $this->makeTicket(2);

        $response = $this->actingAs($this->contributorA)
            ->post(route('contributors.tickets.transfer', $ticket->ticket_id), [
                'new_request_type_id' => $this->typeA->type_id,
            ]);

        $response->assertSessionHasErrors('new_request_type_id');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'request_type_id' => $this->typeA->type_id,
            'mediator_id' => $this->contributorA->user_id,
        ]);
    }

    /** @test */
    public function contributor_cannot_transfer_ticket_with_non_existing_request_type()
    {
        $ticket = $this->makeTicket(2);

        $response = $this->actingAs($this->contributorA)
            ->post(route('contributors.tickets.transfer', $ticket->ticket_id), [
                'new_request_type_id' => 99999999,
            ]);

        $response->assertSessionHasErrors('new_request_type_id');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'request_type_id' => $this->typeA->type_id,
            'mediator_id' => $this->contributorA->user_id,
        ]);
    }

    /** @test */
    public function non_primary_contributor_cannot_transfer_ticket()
    {
        $ticket = $this->makeTicket(2);

        $response = $this->actingAs($this->contributorB)
            ->post(route('contributors.tickets.transfer', $ticket->ticket_id), [
                'new_request_type_id' => $this->typeB->type_id,
            ]);

        $response->assertStatus(404);

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'request_type_id' => $this->typeA->type_id,
            'mediator_id' => $this->contributorA->user_id,
        ]);
    }

    /** @test */
    public function contributor_can_view_topic_queue_and_self_assign_ticket()
    {
        $ticket = Ticket::create([
            'title' => 'Ticket cola topico ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->typeA->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => null,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);

        $queueResponse = $this->actingAs($this->contributorA)
            ->get(route('contributors.topic-tickets.index'));

        $queueResponse->assertStatus(200);
        $queueResponse->assertSee((string) $ticket->ticket_number);

        $assignResponse = $this->actingAs($this->contributorA)
            ->post(route('contributors.topic-tickets.self-assign', $ticket->ticket_id));

        $assignResponse->assertRedirect(route('contributors.tickets.show', $ticket->ticket_id));

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'mediator_id' => $this->contributorA->user_id,
        ]);

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributorA->user_id,
            'status' => 'active',
        ]);

        $this->actingAs($this->contributorB)
            ->get(route('contributors.tickets.show', $ticket->ticket_id))
            ->assertStatus(200)
            ->assertSee('Para poder trabajar en él, debe asignárselo primero');
    }

    /** @test */
    public function contributor_cannot_self_assign_ticket_outside_his_topic()
    {
        $ticket = Ticket::create([
            'title' => 'Ticket fuera de topico ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->typeB->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => null,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);

        $this->actingAs($this->contributorA)
            ->post(route('contributors.topic-tickets.self-assign', $ticket->ticket_id))
            ->assertStatus(404);

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'mediator_id' => null,
        ]);
    }

    /** @test */
    public function contributor_can_self_assign_ticket_already_assigned_to_another_contributor()
    {
        $ticket = Ticket::create([
            'title' => 'Ticket compartido tópico ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->typeA->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => null,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributorA->user_id,
            'assigned_by' => $this->requester->user_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->contributorB)
            ->post(route('contributors.topic-tickets.self-assign', $ticket->ticket_id));

        $response->assertRedirect(route('contributors.tickets.show', $ticket->ticket_id));

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributorA->user_id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributorB->user_id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function contributor_can_get_topic_queue_count_as_json()
    {
        Ticket::create([
            'title' => 'Ticket count topico ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->typeA->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => null,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);

        $response = $this->actingAs($this->contributorA)
            ->get(route('contributors.topic-tickets.count'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['count']);
        $this->assertGreaterThanOrEqual(1, (int) $response->json('count'));
    }

    /** @test */
    public function contributor_only_sees_and_takes_tickets_from_his_regional_pool()
    {
        if (!Schema::hasTable('request_type_regional_assignments')) {
            $this->markTestSkipped('La tabla request_type_regional_assignments no existe en esta base de pruebas.');
        }

        $regionalA = Institution::create([
            'institution_name' => 'Regional A ' . uniqid(),
            'is_active' => true,
        ]);

        $regionalB = Institution::create([
            'institution_name' => 'Regional B ' . uniqid(),
            'is_active' => true,
        ]);

        DB::table('request_type_regional_assignments')->insert([
            [
                'request_type_id' => $this->typeA->type_id,
                'institution_id' => $regionalA->institution_id,
                'user_id' => $this->contributorA->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'request_type_id' => $this->typeA->type_id,
                'institution_id' => $regionalB->institution_id,
                'user_id' => $this->contributorB->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $ticketRegionalA = Ticket::create([
            'title' => 'Ticket regional A ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->typeA->type_id,
            'institution_id' => $regionalA->institution_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => null,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);

        $ticketRegionalB = Ticket::create([
            'title' => 'Ticket regional B ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->typeA->type_id,
            'institution_id' => $regionalB->institution_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => null,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);

        $this->actingAs($this->contributorA)
            ->get(route('contributors.topic-tickets.index'))
            ->assertStatus(200)
            ->assertSee((string) $ticketRegionalA->ticket_number)
            ->assertDontSee((string) $ticketRegionalB->ticket_number);

        $this->actingAs($this->contributorA)
            ->post(route('contributors.topic-tickets.self-assign', $ticketRegionalB->ticket_id))
            ->assertStatus(404);

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticketRegionalB->ticket_id,
            'mediator_id' => null,
        ]);
    }

    /** @test */
    public function contributor_can_view_and_self_assign_topic_ticket_routed_to_admin_area()
    {
        $adminAreaRole = UserRole::firstOrCreate(['role_name' => 'Admin Área'], ['is_active' => true]);
        $adminArea = User::create([
            'user_name' => 'Admin Area Topic Queue Test',
            'user_email' => 'admin_area_topic_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $adminAreaRole->role_id,
            'is_active' => true,
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket enrutado a admin area ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $this->typeA->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $adminArea->user_id,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
            'progress_percentage' => 0,
            'priority' => 1,
        ]);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $adminArea->user_id,
            'assigned_by' => $this->requester->user_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        $queueResponse = $this->actingAs($this->contributorA)
            ->get(route('contributors.topic-tickets.index'));

        $queueResponse->assertStatus(200);
        $queueResponse->assertSee((string) $ticket->ticket_number);

        $assignResponse = $this->actingAs($this->contributorA)
            ->post(route('contributors.topic-tickets.self-assign', $ticket->ticket_id));

        $assignResponse->assertRedirect(route('contributors.tickets.show', $ticket->ticket_id));

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'mediator_id' => $this->contributorA->user_id,
        ]);

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $adminArea->user_id,
            'status' => 'removed',
        ]);

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributorA->user_id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function contributor_can_close_ticket_when_auto_progress_is_100()
    {
        $ticket = $this->makeTicket(2);
        $ticket->update(['current_phase' => 'Evaluation']);

        $sprint = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint cierre',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'sprint_id' => $sprint->sprint_id,
            'title' => 'Tarea completada cierre',
            'priority' => 'high',
            'status' => 'done',
        ]);

        $response = $this->actingAs($this->contributorA)
            ->patch(route('contributors.tickets.close', $ticket->ticket_id), [
                'solution_detail' => 'Solucion aplicada y validada para el cierre final',
                'resource_link' => 'https://ejemplo.com/recurso',
            ]);

        $response->assertRedirect(route('contributors.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 3,
            'progress_percentage' => 100,
        ]);

        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'status_update' => 'service_closed',
            'progress_percentage' => 100,
        ]);
    }

    /** @test */
    public function contributor_cannot_close_ticket_when_auto_progress_is_below_100()
    {
        $ticket = $this->makeTicket(2);

        $sprint = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint incompleto',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'sprint_id' => $sprint->sprint_id,
            'title' => 'Tarea pendiente cierre',
            'priority' => 'medium',
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->contributorA)
            ->patch(route('contributors.tickets.close', $ticket->ticket_id), [
                'solution_detail' => 'Intento de cierre sin avance total',
            ]);

        $response->assertSessionHasErrors('close');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 2,
        ]);
    }

    /** @test */
    public function contributor_cannot_close_ticket_with_short_solution_detail()
    {
        $ticket = $this->makeTicket(2);

        $response = $this->actingAs($this->contributorA)
            ->patch(route('contributors.tickets.close', $ticket->ticket_id), [
                'solution_detail' => 'corta',
            ]);

        $response->assertSessionHasErrors('solution_detail');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 2,
        ]);
    }

    /** @test */
    public function contributor_can_send_message_to_requester_when_ticket_is_open()
    {
        $ticket = $this->makeTicket(2);

        $response = $this->actingAs($this->contributorA)
            ->post(route('contributors.tickets.message', $ticket->ticket_id), [
                'message' => 'Te comparto avance del ticket para tu validación.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->contributorA->user_id,
            'status_update' => 'collaborator_message',
        ]);
    }

    /** @test */
    public function contributor_cannot_send_message_when_ticket_is_closed()
    {
        $ticket = $this->makeTicket(3);

        $response = $this->actingAs($this->contributorA)
            ->from(route('contributors.tickets.show', $ticket->ticket_id))
            ->post(route('contributors.tickets.message', $ticket->ticket_id), [
                'message' => 'Intento de mensaje luego del cierre.',
            ]);

        $response->assertRedirect(route('contributors.tickets.show', $ticket->ticket_id));
        $response->assertSessionHasErrors('message');

        $this->assertDatabaseMissing('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'status_update' => 'collaborator_message',
        ]);
    }
}
