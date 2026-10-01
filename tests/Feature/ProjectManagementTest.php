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

class ProjectManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $monitor;
    protected $contributor;
    protected $requester;
    protected $requestType;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);
        $monitorRole = UserRole::firstOrCreate(['role_name' => 'Monitor'], ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $this->admin = User::create([
            'user_name' => 'Admin Project Test',
            'user_email' => 'admin_project_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $adminRole->role_id,
            'is_active' => true,
        ]);

        $this->monitor = User::create([
            'user_name' => 'Monitor Project Test',
            'user_email' => 'monitor_project_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $monitorRole->role_id,
            'is_active' => true,
        ]);

        $this->contributor = User::create([
            'user_name' => 'Contributor Project Test',
            'user_email' => 'contributor_project_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->requester = User::create([
            'user_name' => 'Requester Project Test',
            'user_email' => 'requester_project_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $this->requestType = RequestType::firstOrCreate(['type_name' => 'Project Test Type']);
    }

    private function makeTicket(int $status = 1, string $phase = 'Analysis'): Ticket
    {
        return Ticket::create([
            'title' => 'Ticket Project Test ' . uniqid(),
            'ticket_number' => time() . rand(100, 999),
            'status' => $status,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
            'current_phase' => $phase,
            'progress_percentage' => 0,
        ]);
    }

    /** @test */
    public function admin_can_access_project_dashboard()
    {
        $ticket = $this->makeTicket();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.projects.dashboard', $ticket->ticket_id));

        $response->assertStatus(200);
    }

    /** @test */
    public function monitor_can_access_project_dashboard_as_read_only()
    {
        $ticket = $this->makeTicket();

        $response = $this->actingAs($this->monitor)
            ->get(route('admin.projects.dashboard', $ticket->ticket_id));

        $response->assertStatus(200);
    }

    /** @test */
    public function contributor_cannot_access_project_dashboard()
    {
        $ticket = $this->makeTicket();

        $response = $this->actingAs($this->contributor)
            ->get(route('admin.projects.dashboard', $ticket->ticket_id));

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_from_project_dashboard()
    {
        $ticket = $this->makeTicket();

        $response = $this->get(route('admin.projects.dashboard', $ticket->ticket_id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_update_ticket_phase()
    {
        $ticket = $this->makeTicket(1, 'Analysis');

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.projects.update-phase', $ticket->ticket_id), [
                'phase' => 'Design',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'current_phase' => 'Design',
        ]);
    }

    /** @test */
    public function monitor_cannot_update_ticket_phase_due_read_only_mode()
    {
        $ticket = $this->makeTicket(1, 'Analysis');

        $response = $this->actingAs($this->monitor)
            ->patch(route('admin.projects.update-phase', $ticket->ticket_id), [
                'phase' => 'Design',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'current_phase' => 'Analysis',
        ]);
    }

    /** @test */
    public function admin_can_create_sprint_with_valid_dates()
    {
        $ticket = $this->makeTicket();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.projects.store-sprint', $ticket->ticket_id), [
                'name' => 'Sprint PM 1',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'goal' => 'Meta sprint',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sprints', [
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint PM 1',
            'status' => 'planned',
        ]);
    }

    /** @test */
    public function admin_cannot_create_sprint_if_end_date_is_before_start_date()
    {
        $ticket = $this->makeTicket();

        $response = $this->actingAs($this->admin)
            ->from(route('admin.projects.dashboard', $ticket->ticket_id))
            ->post(route('admin.projects.store-sprint', $ticket->ticket_id), [
                'name' => 'Sprint Invalido',
                'start_date' => now()->toDateString(),
                'end_date' => now()->subDay()->toDateString(),
            ]);

        $response->assertRedirect(route('admin.projects.dashboard', $ticket->ticket_id));
        $response->assertSessionHasErrors('end_date');

        $this->assertDatabaseMissing('sprints', [
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint Invalido',
        ]);
    }

    /** @test */
    public function activating_a_sprint_completes_other_active_sprint_for_same_ticket()
    {
        $ticket = $this->makeTicket();

        $active = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint Activo',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        $planned = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint Planificado',
            'start_date' => now()->addDays(8)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'status' => 'planned',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.projects.update-sprint-status', $planned->sprint_id), [
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sprints', [
            'sprint_id' => $active->sprint_id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('sprints', [
            'sprint_id' => $planned->sprint_id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_cannot_assign_task_to_completed_sprint()
    {
        $ticket = $this->makeTicket();

        $completedSprint = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint Cerrado PM',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'completed',
        ]);

        $task = ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'title' => 'Tarea PM',
            'priority' => 'medium',
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.projects.assign-task-sprint', $task->task_id), [
                'sprint_id' => $completedSprint->sprint_id,
            ]);

        $response->assertSessionHasErrors('sprint_id');

        $this->assertDatabaseHas('project_tasks', [
            'task_id' => $task->task_id,
            'sprint_id' => null,
        ]);
    }

    /** @test */
    public function update_task_status_to_done_recalculates_progress_and_moves_ticket_to_in_progress()
    {
        $ticket = $this->makeTicket(1, 'Development');

        $sprint = Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => 'Sprint progreso PM',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        $task = ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'sprint_id' => $sprint->sprint_id,
            'title' => 'Tarea Unica PM',
            'priority' => 'high',
            'status' => 'todo',
            'assigned_to' => $this->contributor->user_id,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.projects.update-task-status', $task->task_id), [
                'status' => 'done',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status' => 2,
            'progress_percentage' => 100,
        ]);

        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'status_update' => 'task_completed',
        ]);
    }

    /** @test */
    public function cannot_update_task_status_when_ticket_is_completed()
    {
        $ticket = $this->makeTicket(3, 'Evaluation');

        $task = ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'title' => 'Tarea bloqueada',
            'priority' => 'medium',
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.projects.update-task-status', $task->task_id), [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('project_tasks', [
            'task_id' => $task->task_id,
            'status' => 'todo',
        ]);
    }
}
