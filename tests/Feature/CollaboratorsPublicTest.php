<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CollaboratorsPublicTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    /** @test */
    public function collaborators_public_page_is_accessible()
    {
        $response = $this->get(route('collaborators'));

        $response->assertStatus(200);
    }

    /** @test */
    public function collaborators_page_shows_only_active_collaborator_roles()
    {
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $visibleContributor = User::create([
            'user_name' => 'Visible Contributor',
            'user_email' => 'visible_contributor_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        User::create([
            'user_name' => 'Inactive Contributor',
            'user_email' => 'inactive_contributor_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => false,
        ]);

        User::create([
            'user_name' => 'Active Requester',
            'user_email' => 'active_requester_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $response = $this->get(route('collaborators'));

        $response->assertStatus(200);
        $response->assertSee($visibleContributor->user_name);
        $response->assertDontSee('Inactive Contributor');
        $response->assertDontSee('Active Requester');
    }

    /** @test */
    public function collaborators_page_excludes_requester_roles_from_listing()
    {
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        User::create([
            'user_name' => 'Requester Hidden User',
            'user_email' => 'requester_hidden_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $response = $this->get(route('collaborators'));

        $response->assertStatus(200);
        $response->assertDontSee('Requester Hidden User');
    }
}
