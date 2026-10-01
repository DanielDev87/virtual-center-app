<?php

namespace Tests\Unit;

use App\Models\RequestType;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TicketAssignmentModelTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    private function makeUser(string $roleName): User
    {
        $role = UserRole::firstOrCreate(['role_name' => $roleName], ['is_active' => true]);

        return User::create([
            'user_name' => 'Assignment Model ' . $roleName . ' ' . uniqid(),
            'user_email' => 'assignment_model_' . strtolower($roleName) . '_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    private function makeTicket(User $requester, User $mediator): Ticket
    {
        $type = RequestType::create([
            'type_name' => 'Assignment Type ' . uniqid(),
            'gestor_id' => $mediator->user_id,
            'is_active' => true,
        ]);

        return Ticket::create([
            'title' => 'Ticket Assignment Model ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $type->type_id,
            'requester_id' => $requester->user_id,
            'mediator_id' => $mediator->user_id,
            'resume_number' => 0,
        ]);
    }

    /** @test */
    public function assigned_at_is_cast_to_datetime()
    {
        $requester = $this->makeUser('Requester');
        $contributor = $this->makeUser('Contributor');
        $ticket = $this->makeTicket($requester, $contributor);

        $assignment = TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $contributor->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'active',
            'assigned_at' => now()->toDateTimeString(),
        ]);

        $this->assertInstanceOf(Carbon::class, $assignment->assigned_at);
    }

    /** @test */
    public function ticket_and_mediator_relationships_return_expected_models()
    {
        $requester = $this->makeUser('Requester');
        $contributor = $this->makeUser('Contributor');
        $ticket = $this->makeTicket($requester, $contributor);

        $assignment = TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $contributor->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        $this->assertSame($ticket->ticket_id, $assignment->ticket->ticket_id);
        $this->assertSame($contributor->user_id, $assignment->mediator->user_id);
    }

    /** @test */
    public function assigned_by_user_relationship_returns_assigner()
    {
        $requester = $this->makeUser('Requester');
        $contributor = $this->makeUser('Contributor');
        $ticket = $this->makeTicket($requester, $contributor);

        $assignment = TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $contributor->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        $this->assertNotNull($assignment->assignedByUser);
        $this->assertSame($requester->user_id, $assignment->assignedByUser->user_id);
    }
}
