<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\ContributorController;
use ReflectionMethod;

class ContributorAutoProgressUnitTest extends TestCase
{
    private ContributorController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ContributorController();
    }

    private function invokePrivate(string $method, array $args = [])
    {
        $ref = new ReflectionMethod(ContributorController::class, $method);
        return $ref->invokeArgs($this->controller, $args);
    }

    private function makeTicketWithTasks(array $taskStatuses, int $status = 1): object
    {
        $tasks = collect(array_map(fn($s) => (object)['status' => $s], $taskStatuses));
        $sprint = (object)['tasks' => $tasks];
        $ticket = new class($sprint, $status) {
            public $sprints;
            public $status;
            public $progress_percentage = 0;
            public array $updated = [];
            public function __construct($sprint, int $status)
            {
                $this->sprints = collect([$sprint]);
                $this->status = $status;
            }
            public function load($rel) { return $this; }
            public function update(array $data)
            {
                $this->updated = $data;
                foreach ($data as $k => $v) {
                    $this->$k = $v;
                }
            }
        };
        return $ticket;
    }

    /** @test */
    public function computeAutoProgress_returns_zero_when_no_tasks(): void
    {
        $ticket = $this->makeTicketWithTasks([]);
        $result = $this->invokePrivate('computeAutoProgress', [$ticket]);
        $this->assertSame(0, $result);
    }

    /** @test */
    public function computeAutoProgress_returns_correct_percentage(): void
    {
        // 2 done out of 4 = 50%
        $ticket = $this->makeTicketWithTasks(['done', 'done', 'pending', 'pending']);
        $result = $this->invokePrivate('computeAutoProgress', [$ticket]);
        $this->assertSame(50, $result);
    }

    /** @test */
    public function computeAutoProgress_rounds_correctly(): void
    {
        // 1 done out of 3 = 33.33... rounds to 33
        $ticket = $this->makeTicketWithTasks(['done', 'pending', 'pending']);
        $result = $this->invokePrivate('computeAutoProgress', [$ticket]);
        $this->assertSame(33, $result);
    }

    /** @test */
    public function recalculateTicketProgress_promotes_status_to_in_progress(): void
    {
        // progress > 0 and status == 1 → should update status to 2
        $ticket = $this->makeTicketWithTasks(['done', 'pending'], 1);
        $this->invokePrivate('recalculateTicketProgress', [$ticket]);

        $this->assertArrayHasKey('progress_percentage', $ticket->updated);
        $this->assertSame(50, $ticket->updated['progress_percentage']);
        $this->assertArrayHasKey('status', $ticket->updated);
        $this->assertSame(2, $ticket->updated['status']);
    }

    /** @test */
    public function recalculateTicketProgress_does_not_auto_close_when_progress_is_100(): void
    {
        // progress == 100 and status == 2 → keep in_progress (no auto-close)
        $ticket = $this->makeTicketWithTasks(['done', 'done'], 2);
        $this->invokePrivate('recalculateTicketProgress', [$ticket]);

        $this->assertSame(100, $ticket->updated['progress_percentage']);
        $this->assertArrayNotHasKey('status', $ticket->updated);
    }

    /** @test */
    public function isFinalizado_returns_true_for_completado_and_cancelado(): void
    {
        // Status 3 = Completado, 4 = Cancelado
        foreach ([3, 4] as $status) {
            $ticket = new class($status) extends \App\Models\Ticket {
                public function __construct(int $s) { $this->status = $s; }
            };
            $result = $this->invokePrivate('isFinalizado', [$ticket]);
            $this->assertTrue($result, "Expected isFinalizado=true for status {$status}");
        }
    }

    /** @test */
    public function isFinalizado_returns_false_for_open_and_in_progress(): void
    {
        foreach ([1, 2] as $status) {
            $ticket = new class($status) extends \App\Models\Ticket {
                public function __construct(int $s) { $this->status = $s; }
            };
            $result = $this->invokePrivate('isFinalizado', [$ticket]);
            $this->assertFalse($result, "Expected isFinalizado=false for status {$status}");
        }
    }
}
