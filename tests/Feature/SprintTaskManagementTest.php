<?php

namespace Tests\Feature;

use App\Models\ProjectTask;
use App\Models\RequestType;
use App\Models\Sprint;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SprintTaskManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $contributor;
    protected $otherContributor;
    protected $requester;
    protected $requestType;

    protected function setUp(): void
    {
        parent::setUp();

        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $this->contributor = User::create([
            'user_name' => 'Contributor Sprint Test',
            'user_email' => 'contributor_sprint_' . uniqid() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->otherContributor = User::create([
            'user_name' => 'Other Contributor Sprint Test',
            'user_email' => 'other_contributor_sprint_' . uniqid() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->requester = User::create([
            'user_name' => 'Requester Sprint Test',
            'user_email' => 'requester_sprint_' . uniqid() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $this->requestType = RequestType::firstOrCreate(['type_name' => 'Sprint Test Type']);
    }

    private function makeTicketForContributor(int $status = 1): Ticket
    {
        return Ticket::create([
            'title' => 'Ticket Sprint Test ' . uniqid(),
            'ticket_number' => time() . rand(100, 999),
            'status' => $status,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
            'current_phase' => 'Development',
            'progress_percentage' => 0,
        ]);
    }

    /** @test */
    public function contributor_can_create_sprint_on_assigned_ticket()
    {
        $ticket = $this->makeTicketForContributor();

        $response = $this->actingAs($this->contributor)
            ->post(route('contributors.tickets.store-sprint', $ticket->ticket_id), [
                'name' => 'Sprint 1',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'goal' => 'Completar tareas base',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sprints', [
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint 1',
            'status' => 'planned',
        ]);
    }

    /** @test */
    public function contributor_cannot_create_sprint_on_finalized_ticket()
    {
        $ticket = $this->makeTicketForContributor(3);

        $response = $this->actingAs($this->contributor)
            ->post(route('contributors.tickets.store-sprint', $ticket->ticket_id), [
                'name' => 'Sprint Bloqueado',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('sprints', [
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint Bloqueado',
        ]);
    }

    /** @test */
    public function contributor_can_create_task_with_default_todo_status()
    {
        $ticket = $this->makeTicketForContributor();

        $response = $this->actingAs($this->contributor)
            ->post(route('contributors.tickets.store-task', $ticket->ticket_id), [
                'title' => 'Implementar vista principal',
                'description' => 'Detalle de tarea de prueba',
                'priority' => 'high',
                'assigned_to' => $this->contributor->user_id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('project_tasks', [
            'ticket_id' => $ticket->ticket_id,
            'title' => 'Implementar vista principal',
            'status' => 'todo',
        ]);
    }

    /** @test */
    public function contributor_can_update_task_to_done_and_auto_progress_to_100()
    {
        $ticket = $this->makeTicketForContributor(1);

        $sprint = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint Progreso',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        $task = ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'sprint_id' => $sprint->sprint_id,
            'title' => 'Tarea única',
            'priority' => 'medium',
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->contributor)
            ->patch(route('contributors.tasks.update-status', $task->task_id), [
                'status' => 'done',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('project_tasks', [
            'task_id' => $task->task_id,
            'status' => 'done',
        ]);

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 2,
            'progress_percentage' => 100,
        ]);
    }

    /** @test */
    public function contributor_cannot_assign_task_to_completed_sprint()
    {
        $ticket = $this->makeTicketForContributor();

        $completedSprint = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint Cerrado',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'completed',
        ]);

        $task = ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'title' => 'Tarea a mover',
            'priority' => 'low',
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->contributor)
            ->patch(route('contributors.tasks.assign-sprint', $task->task_id), [
                'sprint_id' => $completedSprint->sprint_id,
            ]);

        $response->assertSessionHasErrors('sprint_id');

        $this->assertDatabaseMissing('project_tasks', [
            'task_id' => $task->task_id,
            'sprint_id' => $completedSprint->sprint_id,
        ]);
    }

    /** @test */
    public function non_assigned_contributor_cannot_create_task_for_ticket()
    {
        $ticket = $this->makeTicketForContributor();

        $response = $this->actingAs($this->otherContributor)
            ->post(route('contributors.tickets.store-task', $ticket->ticket_id), [
                'title' => 'Tarea sin permiso',
                'priority' => 'medium',
            ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('project_tasks', [
            'ticket_id' => $ticket->ticket_id,
            'title' => 'Tarea sin permiso',
        ]);
    }
}
