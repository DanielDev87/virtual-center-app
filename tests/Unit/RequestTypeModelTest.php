<?php

namespace Tests\Unit;

use App\Models\RequestType;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RequestTypeModelTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    private function makeUser(string $roleName): User
    {
        $role = UserRole::firstOrCreate(['role_name' => $roleName], ['is_active' => true]);

        return User::create([
            'user_name' => 'RequestType Model ' . $roleName . ' ' . uniqid(),
            'user_email' => 'request_type_model_' . strtolower($roleName) . '_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function gestor_relationship_returns_expected_user()
    {
        $contributor = $this->makeUser('Contributor');

        $type = RequestType::create([
            'type_name' => 'Gestor Relation Type ' . uniqid(),
            'gestor_id' => $contributor->user_id,
            'is_active' => true,
        ]);

        $this->assertNotNull($type->gestor);
        $this->assertSame($contributor->user_id, $type->gestor->user_id);
    }

    /** @test */
    public function tickets_relationship_returns_tickets_linked_to_request_type()
    {
        $requester = $this->makeUser('Requester');
        $contributor = $this->makeUser('Contributor');

        $type = RequestType::create([
            'type_name' => 'Tickets Relation Type ' . uniqid(),
            'gestor_id' => $contributor->user_id,
            'is_active' => true,
        ]);

        $ticket = Ticket::create([
            'title' => 'Ticket for RequestType relation',
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $type->type_id,
            'requester_id' => $requester->user_id,
            'mediator_id' => $contributor->user_id,
            'resume_number' => 0,
        ]);

        $ticketIds = $type->fresh()->tickets->pluck('ticket_id')->all();

        $this->assertContains($ticket->ticket_id, $ticketIds);
    }

    /** @test */
    public function is_active_is_cast_to_boolean()
    {
        $type = RequestType::create([
            'type_name' => 'Cast Type ' . uniqid(),
            'is_active' => 1,
        ]);

        $this->assertIsBool($type->is_active);
        $this->assertTrue($type->is_active);
    }
}
