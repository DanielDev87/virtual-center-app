<?php

namespace Tests\Unit;

use App\Http\Middleware\CheckRole;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CheckRoleMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    private function makeUser(string $roleName): User
    {
        $role = UserRole::firstOrCreate(['role_name' => $roleName], ['is_active' => true]);

        return User::create([
            'user_name' => 'Middleware Test ' . $roleName . ' ' . uniqid(),
            'user_email' => 'middleware_' . strtolower($roleName) . '_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login()
    {
        $middleware = new CheckRole();
        $request = Request::create('/admin/tickets', 'GET');

        $response = $middleware->handle($request, fn () => response('ok'), 'Admin');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    /** @test */
    public function allowed_role_passes_through_middleware()
    {
        $admin = $this->makeUser('Admin');
        $this->actingAs($admin);

        $middleware = new CheckRole();
        $request = Request::create('/admin/tickets', 'GET');

        $response = $middleware->handle($request, fn () => response('ok', 200), 'Admin');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }

    /** @test */
    public function disallowed_non_monitor_role_throws_403()
    {
        $requester = $this->makeUser('Requester');
        $this->actingAs($requester);

        $middleware = new CheckRole();
        $request = Request::create('/admin/tickets', 'GET');

        try {
            $middleware->handle($request, fn () => response('ok', 200), 'Admin');
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    /** @test */
    public function monitor_get_request_can_observe_even_if_role_not_in_allowed_list()
    {
        $monitor = $this->makeUser('Monitor');
        $this->actingAs($monitor);

        $middleware = new CheckRole();
        $request = Request::create('/admin/tickets', 'GET');

        $response = $middleware->handle($request, fn () => response('ok', 200), 'Admin');

        $this->assertSame(200, $response->getStatusCode());
    }

    /** @test */
    public function monitor_post_request_is_blocked_with_redirect_and_error()
    {
        $monitor = $this->makeUser('Monitor');
        $this->actingAs($monitor);

        $middleware = new CheckRole();
        $request = Request::create('/admin/tickets/1/assign', 'POST');

        $response = $middleware->handle($request, fn () => response('ok', 200), 'Admin');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/', $response->headers->get('Location'));
    }

    /** @test */
    public function monitor_cannot_access_dashboard_route_even_with_get()
    {
        $monitor = $this->makeUser('Monitor');
        $this->actingAs($monitor);

        $middleware = new CheckRole();
        $request = Request::create('/dashboard', 'GET');

        try {
            $middleware->handle($request, fn () => response('ok', 200), 'Admin');
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    /** @test */
    public function authenticated_user_with_null_role_is_rejected_with_403()
    {
        // Create a real user then override the loaded role relation to null
        $role = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $user = User::create([
            'user_name' => 'No Role User ' . uniqid(),
            'user_email' => 'norole_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
        $user->setRelation('role', null);

        $this->actingAs($user);

        $middleware = new CheckRole();
        $request = Request::create('/admin/tickets', 'GET');

        try {
            $middleware->handle($request, fn () => response('ok', 200), 'Admin');
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
