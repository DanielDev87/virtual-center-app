<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserRoleModelTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    /** @test */
    public function is_active_is_cast_to_boolean()
    {
        $role = new UserRole(['is_active' => 1]);
        $this->assertTrue($role->is_active);
        $this->assertIsBool($role->is_active);

        $inactiveRole = new UserRole(['is_active' => 0]);
        $this->assertFalse($inactiveRole->is_active);
        $this->assertIsBool($inactiveRole->is_active);
    }

    /** @test */
    public function users_relation_returns_users_belonging_to_role()
    {
        $role = UserRole::create([
            'role_name' => 'Test Role ' . uniqid(),
            'role_description' => 'Role for unit test',
            'is_active' => true,
        ]);

        $user = User::create([
            'user_name' => 'UserRole Test User ' . uniqid(),
            'user_email' => 'userrole_unit_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);

        $loaded = UserRole::with('users')->find($role->role_id);

        $this->assertCount(1, $loaded->users);
        $this->assertSame($user->user_id, $loaded->users->first()->user_id);
    }

    /** @test */
    public function users_relation_excludes_users_from_other_roles()
    {
        $roleA = UserRole::create([
            'role_name' => 'Role A ' . uniqid(),
            'is_active' => true,
        ]);
        $roleB = UserRole::create([
            'role_name' => 'Role B ' . uniqid(),
            'is_active' => true,
        ]);

        User::create([
            'user_name' => 'User RoleB ' . uniqid(),
            'user_email' => 'userrole_b_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $roleB->role_id,
            'is_active' => true,
        ]);

        $loaded = UserRole::with('users')->find($roleA->role_id);

        $this->assertCount(0, $loaded->users);
    }
}
