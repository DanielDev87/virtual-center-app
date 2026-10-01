<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Ticket;
use App\Models\RequestType;
use App\Models\TicketAssignment;
use App\Mail\TicketClosed;
use App\Mail\TicketCreated;
use App\Mail\ServiceRated;
use App\Mail\CollaboratorMessageReceived;
use App\Mail\RequesterMessageReceived;
use App\Mail\TicketAssigned;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $requester;
    protected $requestType;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        // Setup Users
        $this->admin = User::create([
            'user_name' => 'Admin Email Test',
            'user_email' => 'admin_email@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $adminRole->role_id,
            'is_active' => true
        ]);

        $this->requester = User::create([
            'user_name' => 'Requester Email Test',
            'user_email' => 'requester_email@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $requesterRole->role_id,
            'is_active' => true
        ]);

        $this->requestType = RequestType::firstOrCreate(['type_name' => 'Email Test Type']);
    }

    /** @test */
    public function email_is_sent_when_ticket_is_closed()
    {
        Mail::fake();

        $ticket = Ticket::create([
            'title'               => 'Ticket for Closure Email',
            'ticket_number'       => time() . rand(100, 999),
            'status'              => 2, // In Progress
            'type'                => 1,
            'request_type_id'     => $this->requestType->type_id,
            'requester_id'        => $this->requester->user_id,
            'resume_number'       => 0,
            'progress_percentage' => 100,
            'current_phase'       => 'Evaluation',
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

        // Act: Close the ticket
        $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $ticket->ticket_id), [
                'status'          => 3,
                'solution_detail' => 'Solución completa e implementada correctamente',
                'resource_link'   => 'http://test-resource.com',
            ]);

        // Assert: TicketClosed email was sent to requester
        Mail::assertSent(TicketClosed::class, function ($mail) use ($ticket) {
            return $mail->hasTo($this->requester->user_email) &&
                   $mail->ticket->ticket_id === $ticket->ticket_id;
        });
    }

    /** @test */
    public function email_is_sent_when_ticket_is_created()
    {
        Mail::fake();

        $title = 'Ticket created email test ' . uniqid();

        $response = $this->actingAs($this->requester)
            ->post(route('service-management.store'), [
                'title' => $title,
                'description' => 'Detalle de prueba para notificacion de creacion',
                'request_type_id' => $this->requestType->type_id,
                'priority' => 2,
            ]);

        $response->assertRedirect(route('service-management.index'));

        $ticket = Ticket::where('title', $title)->latest('ticket_id')->first();
        $this->assertNotNull($ticket);

        Mail::assertSent(TicketCreated::class, function ($mail) use ($ticket) {
            return $mail->hasTo($this->requester->user_email) &&
                   $mail->ticket->ticket_id === $ticket->ticket_id;
        });
    }

    /** @test */
    public function email_is_sent_when_service_is_rated()
    {
        Mail::fake();

        $ticket = Ticket::create([
            'title' => 'Ticket for Rating Email',
            'ticket_number' => time() . rand(100,999),
            'status' => 3, // Completed
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 100
        ]);

        // Act: Rate the ticket
        $this->actingAs($this->requester)
            ->post(route('service-management.rate', $ticket->ticket_id), [
                'rating' => 5,
                'comment' => 'Excellent service!'
            ]);

        // Assert: ServiceRated email was sent to admin
        Mail::assertSent(ServiceRated::class, function ($mail) use ($ticket) {
            return $mail->hasTo($this->admin->user_email) &&
                   $mail->ticket->ticket_id === $ticket->ticket_id;
        });
    }

    /** @test */
    public function email_is_sent_when_ticket_is_assigned_to_collaborator()
    {
        Mail::fake();

        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $contributor = User::create([
            'user_name' => 'Contributor Assignment Email Test',
            'user_email' => 'contributor_assignment@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket for Assignment Email',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 20,
        ]);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $contributor->user_id,
            'assigned_by' => $this->admin->user_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        Mail::assertSent(TicketAssigned::class, function ($mail) use ($contributor, $ticket) {
            return $mail->hasTo($contributor->user_email) &&
                $mail->assignment->ticket_id === $ticket->ticket_id;
        });
    }

    /** @test */
    public function email_is_sent_to_requester_when_collaborator_posts_message()
    {
        Mail::fake();

        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $contributor = User::create([
            'user_name' => 'Contributor Email Message Test',
            'user_email' => 'contributor_message@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket for Collaborator Message Email',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $contributor->user_id,
            'resume_number' => 0,
            'progress_percentage' => 20,
        ]);

        $this->actingAs($contributor)
            ->post(route('contributors.tickets.message', $ticket->ticket_id), [
                'message' => 'Mensaje de prueba del colaborador.',
            ]);

        Mail::assertSent(CollaboratorMessageReceived::class, function ($mail) use ($ticket) {
            return $mail->hasTo($this->requester->user_email) &&
                $mail->ticket->ticket_id === $ticket->ticket_id;
        });
    }

    /** @test */
    public function email_is_sent_to_collaborator_when_requester_posts_message()
    {
        Mail::fake();

        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $contributor = User::create([
            'user_name' => 'Contributor Email Receiver Test',
            'user_email' => 'contributor_receiver@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->requester->update([
            'document_number' => 'DOC-MSG-' . uniqid(),
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket for Requester Message Email',
            'ticket_number' => time() . rand(100, 999),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $contributor->user_id,
            'resume_number' => 0,
            'progress_percentage' => 35,
        ]);

        $this->post(route('service-management.trackMessage'), [
            'document_number' => $this->requester->document_number,
            'ticket_number' => $ticket->ticket_number,
            'message' => 'Mensaje de prueba del solicitante.',
        ]);

        Mail::assertSent(RequesterMessageReceived::class, function ($mail) use ($ticket, $contributor) {
            return $mail->hasTo($contributor->user_email) &&
                $mail->ticket->ticket_id === $ticket->ticket_id;
        });
    }
}
