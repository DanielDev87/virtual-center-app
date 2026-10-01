<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $otherUser;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $this->user = User::create([
            'user_name' => 'Profile Test User',
            'user_email' => 'profile_user_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $this->otherUser = User::create([
            'user_name' => 'Profile Other User',
            'user_email' => 'profile_other_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function authenticated_user_can_access_profile_edit_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_from_profile_edit_page()
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_update_basic_profile_information()
    {
        $response = $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'user_name' => 'Profile Updated Name',
                'user_email' => $this->user->user_email,
                'user_phone' => '0414-0000000',
                'user_bio' => 'Bio de prueba de perfil',
                'user_profession' => 'Analista',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'user_id' => $this->user->user_id,
            'user_name' => 'Profile Updated Name',
            'user_phone' => '0414-0000000',
            'user_profession' => 'Analista',
        ]);
    }

    /** @test */
    public function user_cannot_update_profile_with_existing_email_of_another_user()
    {
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'user_name' => 'Profile Updated Name',
                'user_email' => $this->otherUser->user_email,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('user_email');
    }

    /** @test */
    public function user_can_change_password_with_correct_current_password()
    {
        $response = $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'user_name' => $this->user->user_name,
                'user_email' => $this->user->user_email,
                'current_password' => 'password',
                'new_password' => 'newpass123',
                'new_password_confirmation' => 'newpass123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpass123', $this->user->fresh()->password));
    }

    /** @test */
    public function user_cannot_change_password_with_wrong_current_password()
    {
        $oldHash = $this->user->password;

        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'user_name' => $this->user->user_name,
                'user_email' => $this->user->user_email,
                'current_password' => 'wrong-password',
                'new_password' => 'newpass123',
                'new_password_confirmation' => 'newpass123',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('current_password');

        $this->assertSame($oldHash, $this->user->fresh()->password);
    }

    /** @test */
    public function current_password_is_required_when_new_password_is_sent()
    {
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'user_name' => $this->user->user_name,
                'user_email' => $this->user->user_email,
                'new_password' => 'newpass123',
                'new_password_confirmation' => 'newpass123',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('current_password');
    }

    /** @test */
    public function user_can_delete_avatar_reference()
    {
        $this->user->update(['user_avatar' => 'avatars/non-existing-file.png']);

        $response = $this->actingAs($this->user)
            ->delete(route('profile.avatar.delete'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'user_id' => $this->user->user_id,
            'user_avatar' => null,
        ]);
    }
}
