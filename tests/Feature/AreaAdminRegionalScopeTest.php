<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\RequestType;
use App\Models\RequestTypeRegionalAssignment;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AreaAdminRegionalScopeTest extends TestCase
{
    use DatabaseTransactions;

    private const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    /** @test */
    public function area_admin_only_sees_tickets_for_his_regional_assignment_in_regionalized_topics()
    {
        if (!Schema::hasTable('request_type_regional_assignments')) {
            $this->markTestSkipped('La tabla request_type_regional_assignments no existe en esta base de pruebas.');
        }

        $adminAreaRole = UserRole::firstOrCreate(['role_name' => 'Admin Área'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $institutionA = Institution::create([
            'institution_name' => 'Regional A ' . uniqid(),
            'is_active' => true,
        ]);

        $institutionB = Institution::create([
            'institution_name' => 'Regional B ' . uniqid(),
            'is_active' => true,
        ]);

        $faculty = Faculty::create([
            'faculty_name' => 'Facultad Regional Test ' . uniqid(),
            'institution_id' => $institutionA->institution_id,
            'is_active' => true,
        ]);

        $area = Area::create([
            'faculty_id' => $faculty->faculty_id,
            'area_name' => 'Área Regional Test ' . uniqid(),
            'is_active' => true,
        ]);

        $adminRegionalA = User::create([
            'user_name' => 'Admin Area Regional A',
            'user_email' => 'admin_area_regional_a_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $adminAreaRole->role_id,
            'area_id' => $area->area_id,
            'is_active' => true,
        ]);

        $adminRegionalB = User::create([
            'user_name' => 'Admin Area Regional B',
            'user_email' => 'admin_area_regional_b_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $adminAreaRole->role_id,
            'area_id' => $area->area_id,
            'is_active' => true,
        ]);

        $requester = User::create([
            'user_name' => 'Requester Regional Scope',
            'user_email' => 'requester_regional_scope_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $regionalizedTopic = RequestType::create([
            'type_name' => 'Topico Regionalizado ' . uniqid(),
            'is_active' => true,
            'area_id' => $area->area_id,
        ]);

        RequestTypeRegionalAssignment::create([
            'request_type_id' => $regionalizedTopic->type_id,
            'institution_id' => $institutionA->institution_id,
            'user_id' => $adminRegionalA->user_id,
        ]);

        RequestTypeRegionalAssignment::create([
            'request_type_id' => $regionalizedTopic->type_id,
            'institution_id' => $institutionB->institution_id,
            'user_id' => $adminRegionalB->user_id,
        ]);

        $ticketRegionalA = Ticket::create([
            'title' => 'Ticket Regional A',
            'ticket_number' => (int) (time() . rand(100, 199)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $regionalizedTopic->type_id,
            'institution_id' => $institutionA->institution_id,
            'requester_id' => $requester->user_id,
            'priority' => 2,
            'resume_number' => 0,
        ]);

        $ticketRegionalB = Ticket::create([
            'title' => 'Ticket Regional B',
            'ticket_number' => (int) (time() . rand(200, 299)),
            'status' => 1,
            'type' => 1,
            'request_type_id' => $regionalizedTopic->type_id,
            'institution_id' => $institutionB->institution_id,
            'requester_id' => $requester->user_id,
            'priority' => 2,
            'resume_number' => 0,
        ]);

        $response = $this->actingAs($adminRegionalA)
            ->get(route('area-admin.tickets.index'));

        $response->assertStatus(200);
        $response->assertSee((string) $ticketRegionalA->ticket_number);
        $response->assertDontSee((string) $ticketRegionalB->ticket_number);
    }
}
