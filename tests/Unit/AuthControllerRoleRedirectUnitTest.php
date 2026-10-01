<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;

class AuthControllerRoleRedirectUnitTest extends TestCase
{
    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($instance);
        $targetMethod = $reflection->getMethod($method);
        $targetMethod->setAccessible(true);

        return $targetMethod->invokeArgs($instance, $args);
    }

    private function makeUserWithRole(string $roleName): User
    {
        $user = new User();
        $user->setRelation('role', new UserRole(['role_name' => $roleName]));

        return $user;
    }

    /** @test */
    public function redirect_based_on_role_sends_super_admin_tecnico_to_technical_settings()
    {
        $controller = new AuthController();
        $response = $this->invokePrivate($controller, 'redirectBasedOnRole', [$this->makeUserWithRole('Super Admin Tecnico')]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('technical.storage-settings.edit'), $response->getTargetUrl());
    }

    /** @test */
    public function redirect_based_on_role_sends_admin_to_admin_tickets()
    {
        $controller = new AuthController();
        $response = $this->invokePrivate($controller, 'redirectBasedOnRole', [$this->makeUserWithRole('Admin')]);

        $this->assertSame(route('admin.tickets.index'), $response->getTargetUrl());
    }

    /** @test */
    public function redirect_based_on_role_sends_contributor_to_contributors_dashboard()
    {
        $controller = new AuthController();
        $response = $this->invokePrivate($controller, 'redirectBasedOnRole', [$this->makeUserWithRole('Contributor')]);

        $this->assertSame(route('contributors.dashboard'), $response->getTargetUrl());
    }

    /** @test */
    public function redirect_based_on_role_sends_operario_to_operario_dashboard()
    {
        $controller = new AuthController();
        $response = $this->invokePrivate($controller, 'redirectBasedOnRole', [$this->makeUserWithRole('Operario')]);

        $this->assertSame(route('operario.dashboard'), $response->getTargetUrl());
    }

    /** @test */
    public function redirect_based_on_role_sends_monitor_to_monitor_index()
    {
        $controller = new AuthController();
        $response = $this->invokePrivate($controller, 'redirectBasedOnRole', [$this->makeUserWithRole('Monitor')]);

        $this->assertSame(route('monitor.index'), $response->getTargetUrl());
    }

    /** @test */
    public function redirect_based_on_role_falls_back_to_service_management_for_other_roles()
    {
        $controller = new AuthController();
        $response = $this->invokePrivate($controller, 'redirectBasedOnRole', [$this->makeUserWithRole('Requester')]);

        $this->assertSame(route('service-management.index'), $response->getTargetUrl());
    }
}
