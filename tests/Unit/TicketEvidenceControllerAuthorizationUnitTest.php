<?php

namespace Tests\Unit;

use App\Http\Controllers\TicketEvidenceController;
use App\Models\User;
use App\Models\UserRole;
use Mockery;
use Tests\TestCase;

class TicketEvidenceControllerAuthorizationUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($instance);
        $targetMethod = $reflection->getMethod($method);
        $targetMethod->setAccessible(true);

        return $targetMethod->invokeArgs($instance, $args);
    }

    private function makeUser(int $id, string $roleName): User
    {
        $user = new User(['user_id' => $id]);
        $user->user_id = $id;
        $user->setRelation('role', new UserRole(['role_name' => $roleName]));

        return $user;
    }

    private function makeTicket(int $requesterId, int $mediatorId, object $assignmentsQuery): object
    {
        return new class($requesterId, $mediatorId, $assignmentsQuery) {
            public int $requester_id;
            public int $mediator_id;
            private object $assignmentsQuery;

            public function __construct(int $requesterId, int $mediatorId, object $assignmentsQuery)
            {
                $this->requester_id = $requesterId;
                $this->mediator_id = $mediatorId;
                $this->assignmentsQuery = $assignmentsQuery;
            }

            public function assignments(): object
            {
                return $this->assignmentsQuery;
            }
        };
    }

    /** @test */
    public function admin_monitor_and_super_admin_tecnico_always_have_access()
    {
        $controller = new TicketEvidenceController();
        $query = Mockery::mock();
        $ticket = $this->makeTicket(10, 20, $query);

        $this->assertTrue($this->invokePrivate($controller, 'canAccessEvidence', [$this->makeUser(1, 'Admin'), $ticket]));
        $this->assertTrue($this->invokePrivate($controller, 'canAccessEvidence', [$this->makeUser(2, 'Monitor'), $ticket]));
        $this->assertTrue($this->invokePrivate($controller, 'canAccessEvidence', [$this->makeUser(3, 'Super Admin Tecnico'), $ticket]));
    }

    /** @test */
    public function requester_has_access_only_to_own_ticket()
    {
        $controller = new TicketEvidenceController();
        $query = Mockery::mock();

        $ownTicket = $this->makeTicket(50, 60, $query);
        $otherTicket = $this->makeTicket(51, 60, $query);

        $requester = $this->makeUser(50, 'Requester');

        $this->assertTrue($this->invokePrivate($controller, 'canAccessEvidence', [$requester, $ownTicket]));
        $this->assertFalse($this->invokePrivate($controller, 'canAccessEvidence', [$requester, $otherTicket]));
    }

    /** @test */
    public function contributor_has_access_when_is_primary_mediator()
    {
        $controller = new TicketEvidenceController();

        $query = Mockery::mock();
        $query->shouldReceive('where')->never();

        $ticket = $this->makeTicket(70, 80, $query);
        $contributor = $this->makeUser(80, 'Contributor');

        $this->assertTrue($this->invokePrivate($controller, 'canAccessEvidence', [$contributor, $ticket]));
    }

    /** @test */
    public function contributor_has_access_when_has_active_assignment()
    {
        $controller = new TicketEvidenceController();

        $query = Mockery::mock();
        $query->shouldReceive('where')->with('user_id', 90)->once()->andReturnSelf();
        $query->shouldReceive('where')->with('status', 'active')->once()->andReturnSelf();
        $query->shouldReceive('exists')->once()->andReturn(true);

        $ticket = $this->makeTicket(70, 80, $query);
        $contributor = $this->makeUser(90, 'Contributor');

        $this->assertTrue($this->invokePrivate($controller, 'canAccessEvidence', [$contributor, $ticket]));
    }

    /** @test */
    public function contributor_without_primary_or_active_assignment_is_denied()
    {
        $controller = new TicketEvidenceController();

        $query = Mockery::mock();
        $query->shouldReceive('where')->with('user_id', 100)->once()->andReturnSelf();
        $query->shouldReceive('where')->with('status', 'active')->once()->andReturnSelf();
        $query->shouldReceive('exists')->once()->andReturn(false);

        $ticket = $this->makeTicket(70, 80, $query);
        $contributor = $this->makeUser(100, 'Contributor');

        $this->assertFalse($this->invokePrivate($controller, 'canAccessEvidence', [$contributor, $ticket]));
    }
}
