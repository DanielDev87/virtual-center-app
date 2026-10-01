<?php

namespace Tests\Unit;

use App\Models\RequestType;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    private function makeUser(string $roleName, bool $isActive = true): User
    {
        $role = UserRole::firstOrCreate(['role_name' => $roleName], ['is_active' => true]);

        return User::create([
            'user_name' => 'User Model ' . $roleName . ' ' . uniqid(),
            'user_email' => 'user_model_' . strtolower($roleName) . '_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => $isActive,
        ]);
    }

    private function makeTicket(User $requester, User $mediator, RequestType $type, int $status = 1): Ticket
    {
        return Ticket::create([
            'title' => 'Ticket User Model ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => $status,
            'type' => 1,
            'request_type_id' => $type->type_id,
            'requester_id' => $requester->user_id,
            'mediator_id' => $mediator->user_id,
            'resume_number' => 0,
        ]);
    }

    /** @test */
    public function role_relationship_returns_expected_role()
    {
        $user = $this->makeUser('Contributor');

        $this->assertNotNull($user->role);
        $this->assertSame('Contributor', $user->role->role_name);
    }

    /** @test */
    public function is_active_is_cast_to_boolean()
    {
        $activeUser = $this->makeUser('Requester', 1);
        $inactiveUser = $this->makeUser('Requester', 0);

        $this->assertIsBool($activeUser->is_active);
        $this->assertTrue($activeUser->is_active);
        $this->assertFalse($inactiveUser->is_active);
    }

    /** @test */
    public function assigned_tickets_relationship_only_returns_active_assignments()
    {
        $requester = $this->makeUser('Requester');
        $contributor = $this->makeUser('Contributor');

        $type = RequestType::firstOrCreate(
            ['type_name' => 'User Model Relation Type ' . uniqid()],
            ['gestor_id' => $contributor->user_id, 'is_active' => true]
        );

        $activeTicket = $this->makeTicket($requester, $contributor, $type, 2);
        $removedTicket = $this->makeTicket($requester, $contributor, $type, 1);

        TicketAssignment::create([
            'ticket_id' => $activeTicket->ticket_id,
            'user_id' => $contributor->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        TicketAssignment::create([
            'ticket_id' => $removedTicket->ticket_id,
            'user_id' => $contributor->user_id,
            'assigned_by' => $requester->user_id,
            'status' => 'removed',
            'assigned_at' => now(),
        ]);

        $assignedTicketIds = $contributor->fresh()->assignedTickets->pluck('ticket_id')->all();

        $this->assertContains($activeTicket->ticket_id, $assignedTicketIds);
        $this->assertNotContains($removedTicket->ticket_id, $assignedTicketIds);
    }
}
