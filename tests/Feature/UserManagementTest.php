<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $testRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Get Admin Role
        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);
        // Get Requester Role for the new user test
        $this->testRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        // Create Admin User explicitly without Factory
        $this->admin = User::create([
            'user_name' => 'Admin User Manager',
            'user_email' => 'admin_manager_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role_id' => $adminRole->role_id,
            'is_active' => true
        ]);
    }

    /** @test */
    public function admin_can_create_a_new_user()
    {
        $newEmail = 'new_user_'.time().'@test.com';

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'user_name' => 'New Test User',
                'user_email' => $newEmail,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
                'role_id' => $this->testRole->role_id,
                'is_active' => 1
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'user_email' => $newEmail,
            'user_name' => 'New Test User'
        ]);
    }

    /** @test */
    public function admin_can_update_a_user()
    {
        // Create user to update
        $user = User::create([
            'user_name' => 'User To Update',
            'user_email' => 'update_me_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $this->testRole->role_id,
            'is_active' => true
        ]);

        $updatedName = 'Updated Name ' . time();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user->user_id), [
                'user_name' => $updatedName,
                'user_email' => $user->user_email, // Keep email
                'role_id' => $this->testRole->role_id,
                'is_active' => 1
                // Password nullable on update
            ]);

        $response->assertRedirect(route('admin.users.index'));
        
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'user_name' => $updatedName
        ]);
    }

    /** @test */
    public function admin_cannot_create_user_with_duplicate_email()
    {
        // Existing user
        $existingUser = User::create([
            'user_name' => 'Existing User',
            'user_email' => 'duplicate_'.time().'@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $this->testRole->role_id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'user_name' => 'Another User',
                'user_email' => $existingUser->user_email, // Duplicate!
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => $this->testRole->role_id,
                'is_active' => 1
            ]);

        $response->assertSessionHasErrors('user_email');
    }
}
