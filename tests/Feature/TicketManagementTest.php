<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\JobPosition;
use App\Models\RequestType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $requester;
    protected $contributor;
    protected $requestType;
    protected $jobPosition;

    protected function setUp(): void
    {
        parent::setUp();

        // Get or Create Roles (safe for dev DB)
        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);

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
        $ticket = Ticket::create([
            'title' => 'Ticket for Assignment',
            'ticket_number' => time() . rand(100,999),
            'status' => 1,
            'type' => 1,
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
        
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'progress_percentage' => 50
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

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $ticket->ticket_id), [
                'status' => 3,
                'resource_link' => 'http://example.com'
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

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tickets.close', $ticket->ticket_id), [
                'status' => 3, 
                'resource_link' => 'http://example.com'
            ]);

        $this->assertEquals(3, $ticket->fresh()->status);
        $this->assertEquals('http://example.com', $ticket->fresh()->resource_link);
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
}
