<?php

namespace Tests\Feature;

use App\Models\RequestType;
use App\Models\Ticket;
use App\Models\TicketProgress;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportsExportTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $contributor;
    protected $requester;
    protected $requestType;
    protected $ticket;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);
        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);

        $this->admin = User::create([
            'user_name' => 'Admin Reports Export Test',
            'user_email' => 'admin_reports_export_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $adminRole->role_id,
            'is_active' => true,
        ]);

        $this->contributor = User::create([
            'user_name' => 'Contributor Reports Export Test',
            'user_email' => 'contributor_reports_export_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->requester = User::create([
            'user_name' => 'Requester Reports Export Test',
            'user_email' => 'requester_reports_export_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'is_active' => true,
        ]);

        $this->requestType = RequestType::firstOrCreate(
            ['type_name' => 'Reports Export Type'],
            ['gestor_id' => $this->contributor->user_id, 'is_active' => true]
        );

        $this->ticket = Ticket::create([
            'title' => 'Ticket Reports Export ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 2,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $this->requester->user_id,
            'mediator_id' => $this->contributor->user_id,
            'resume_number' => 0,
            'priority' => 2,
            'current_phase' => 'Development',
            'progress_percentage' => 50,
        ]);

        TicketProgress::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => $this->contributor->user_id,
            'progress_description' => 'Nota de progreso para export',
            'progress_percentage' => 50,
            'status_update' => 'manual_note',
        ]);
    }

    /** @test */
    public function tickets_report_can_be_exported_as_csv()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.tickets', [
                'status' => 2,
                'format' => 'csv',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function tickets_report_can_be_exported_as_printable_pdf_html()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.tickets', [
                'status' => 2,
                'format' => 'pdf',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('Reporte de Tickets', $content);
        $this->assertStringContainsString('window.print()', $content);
    }

    /** @test */
    public function collaborators_report_with_filter_can_be_exported_as_csv()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.collaborators', [
                'is_active' => '1',
                'format' => 'csv',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function progress_report_with_date_filter_can_be_exported_as_csv()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.progress', [
                'start_date' => now()->subDay()->toDateString(),
                'format' => 'csv',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function tickets_csv_contains_expected_column_headers()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.tickets', [
                'status' => 2,
                'format' => 'csv',
            ]));

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringContainsString('Número', $content);
        $this->assertStringContainsString('Título', $content);
        $this->assertStringContainsString('Estado', $content);
        $this->assertStringContainsString('Prioridad', $content);
    }

    /** @test */
    public function tickets_csv_contains_the_created_ticket_data()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.tickets', [
                'status' => 2,
                'format' => 'csv',
            ]));

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringContainsString((string) $this->ticket->ticket_number, $content);
        $this->assertStringContainsString($this->ticket->title, $content);
    }

    /** @test */
    public function collaborators_csv_contains_expected_column_headers()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.collaborators', [
                'is_active' => '1',
                'format' => 'csv',
            ]));

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nombre', $content);
        $this->assertStringContainsString('Email', $content);
    }
}
