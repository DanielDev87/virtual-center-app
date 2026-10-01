<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TicketRelationsTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    private function makeUser(): User
    {
        $role = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);

        return User::create([
            'user_name' => 'Relations Test User ' . uniqid(),
            'user_email' => 'relations_test_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    private function makeTicket(User $requester): Ticket
    {
        return Ticket::create([
            'title' => 'Relations Test Ticket ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'requester_id' => $requester->user_id,
            'resume_number' => 0,
            'current_phase' => 'Analysis',
        ]);
    }

    /** @test */
    public function active_mediators_returns_only_users_with_active_pivot_status()
    {
        $requester = $this->makeUser();
        $activeMediator = $this->makeUser();
        $removedMediator = $this->makeUser();

        $ticket = $this->makeTicket($requester);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $activeMediator->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $removedMediator->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'removed',
            'assigned_at' => now(),
        ]);

        $mediators = $ticket->activeMediators()->get();

        $this->assertCount(1, $mediators);
        $this->assertSame($activeMediator->user_id, $mediators->first()->user_id);
    }

    /** @test */
    public function active_mediators_returns_empty_when_all_assignments_are_removed()
    {
        $requester = $this->makeUser();
        $mediator = $this->makeUser();

        $ticket = $this->makeTicket($requester);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $mediator->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'removed',
            'assigned_at' => now(),
        ]);

        $this->assertCount(0, $ticket->activeMediators()->get());
    }

    /** @test */
    public function active_mediators_includes_pivot_data()
    {
        $requester = $this->makeUser();
        $mediator = $this->makeUser();

        $ticket = $this->makeTicket($requester);

        TicketAssignment::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $mediator->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'active',
            'assigned_at' => now(),
            'notes' => 'Nota de asignación',
        ]);

        $result = $ticket->activeMediators()->get();

        $this->assertCount(1, $result);
        $pivot = $result->first()->pivot;
        $this->assertSame('Nota de asignación', $pivot->notes);
        $this->assertNotNull($pivot->assigned_at);
    }
}
