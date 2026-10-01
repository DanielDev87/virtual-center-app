<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminTicketController;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AdminTicketAutoProgressUnitTest extends TestCase
{
    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($instance);
        $targetMethod = $reflection->getMethod($method);
        $targetMethod->setAccessible(true);

        return $targetMethod->invokeArgs($instance, $args);
    }

    private function buildTicketLike(array $sprintTaskStatuses): object
    {
        $sprints = collect($sprintTaskStatuses)->map(function (array $statuses) {
            $tasks = collect($statuses)->map(fn (string $status) => (object) ['status' => $status]);
            return (object) ['tasks' => $tasks];
        });

        return (object) ['sprints' => $sprints];
    }

    /** @test */
    public function compute_auto_progress_returns_zero_when_no_tasks_exist()
    {
        $controller = new AdminTicketController();
        $ticket = $this->buildTicketLike([]);

        $result = $this->invokePrivate($controller, 'computeAutoProgress', [$ticket]);

        $this->assertSame(0, $result);
    }

    /** @test */
    public function compute_auto_progress_returns_expected_percentage_for_mixed_tasks()
    {
        $controller = new AdminTicketController();
        $ticket = $this->buildTicketLike([
            ['done', 'todo'],
            ['done', 'in_progress'],
        ]);

        $result = $this->invokePrivate($controller, 'computeAutoProgress', [$ticket]);

        $this->assertSame(50, $result);
    }

    /** @test */
    public function compute_auto_progress_rounds_result_to_nearest_integer()
    {
        $controller = new AdminTicketController();
        $ticket = $this->buildTicketLike([
            ['done', 'done', 'todo'],
        ]);

        $result = $this->invokePrivate($controller, 'computeAutoProgress', [$ticket]);

        $this->assertSame(67, $result);
    }
}
