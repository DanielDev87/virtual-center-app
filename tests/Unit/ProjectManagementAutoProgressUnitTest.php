<?php

namespace Tests\Unit;

use App\Http\Controllers\ProjectManagementController;
use Tests\TestCase;

class ProjectManagementAutoProgressUnitTest extends TestCase
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
    public function compute_auto_progress_returns_zero_when_ticket_has_no_sprint_tasks()
    {
        $controller = new ProjectManagementController();
        $ticket = $this->buildTicketLike([]);

        $result = $this->invokePrivate($controller, 'computeAutoProgress', [$ticket]);

        $this->assertSame(0, $result);
    }

    /** @test */
    public function compute_auto_progress_returns_percentage_based_on_done_tasks()
    {
        $controller = new ProjectManagementController();
        $ticket = $this->buildTicketLike([
            ['done', 'done', 'todo', 'review'],
        ]);

        $result = $this->invokePrivate($controller, 'computeAutoProgress', [$ticket]);

        $this->assertSame(50, $result);
    }

    /** @test */
    public function recalculate_ticket_progress_updates_percentage_and_promotes_status_when_pending()
    {
        $controller = new ProjectManagementController();

        $ticket = $this->buildTicketLike([
            ['done', 'todo'],
        ]);
        $ticket->status = 1;
        $ticket->updatedData = null;
        $ticket->loadedRelation = null;
        $ticket->load = function ($relation) use ($ticket) {
            $ticket->loadedRelation = $relation;
            return $ticket;
        };
        $ticket->update = function (array $data) use ($ticket) {
            $ticket->updatedData = $data;
        };

        $proxy = new class($ticket) {
            public object $inner;
            public function __construct(object $inner)
            {
                $this->inner = $inner;
                $this->status = $inner->status;
                $this->sprints = $inner->sprints;
            }
            public function load($relation)
            {
                return ($this->inner->load)($relation);
            }
            public function update(array $data)
            {
                return ($this->inner->update)($data);
            }
            public function __get($name)
            {
                return $this->inner->{$name} ?? null;
            }
        };

        $this->invokePrivate($controller, 'recalculateTicketProgress', [$proxy]);

        $this->assertSame('sprints.tasks', $ticket->loadedRelation);
        $this->assertSame(['progress_percentage' => 50, 'status' => 2], $ticket->updatedData);
    }

    /** @test */
    public function recalculate_ticket_progress_only_updates_percentage_when_ticket_already_in_progress()
    {
        $controller = new ProjectManagementController();

        $ticket = $this->buildTicketLike([
            ['done', 'done'],
        ]);
        $ticket->status = 2;
        $ticket->updatedData = null;
        $ticket->loadedRelation = null;
        $ticket->load = function ($relation) use ($ticket) {
            $ticket->loadedRelation = $relation;
            return $ticket;
        };
        $ticket->update = function (array $data) use ($ticket) {
            $ticket->updatedData = $data;
        };

        $proxy = new class($ticket) {
            public object $inner;
            public function __construct(object $inner)
            {
                $this->inner = $inner;
                $this->status = $inner->status;
                $this->sprints = $inner->sprints;
            }
            public function load($relation)
            {
                return ($this->inner->load)($relation);
            }
            public function update(array $data)
            {
                return ($this->inner->update)($data);
            }
            public function __get($name)
            {
                return $this->inner->{$name} ?? null;
            }
        };

        $this->invokePrivate($controller, 'recalculateTicketProgress', [$proxy]);

        $this->assertSame('sprints.tasks', $ticket->loadedRelation);
        $this->assertSame(['progress_percentage' => 100], $ticket->updatedData);
    }
}
