<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Ticket;
use App\Models\RequestType;
use App\Mail\TicketClosed;
use App\Mail\ServiceRated;
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
            'title' => 'Ticket for Closure Email',
            'ticket_number' => time() . rand(100,999),
            'status' => 2, // In Progress
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => 100,
            'current_phase' => 'Evaluation'
        ]);

        // Act: Close the ticket
        $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $ticket->ticket_id), [
                'status' => 3,
                'resource_link' => 'http://test-resource.com'
            ]);

        // Assert: TicketClosed email was sent to requester
        Mail::assertSent(TicketClosed::class, function ($mail) use ($ticket) {
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
}
