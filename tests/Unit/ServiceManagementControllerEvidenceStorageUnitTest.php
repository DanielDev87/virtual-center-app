<?php

namespace Tests\Unit;

use App\Http\Controllers\ServiceManagementController;
use App\Models\AppSetting;
use App\Models\Ticket;
use App\Models\TicketEvidence;
use App\Models\User;
use App\Models\UserRole;
use App\Services\GoogleDriveStorageService;
use App\Services\LocalEvidenceStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class ServiceManagementControllerEvidenceStorageUnitTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($instance);
        $targetMethod = $reflection->getMethod($method);
        $targetMethod->setAccessible(true);

        return $targetMethod->invokeArgs($instance, $args);
    }

    private function makeUser(string $roleName): User
    {
        $role = UserRole::firstOrCreate(['role_name' => $roleName], ['is_active' => true]);

        return User::create([
            'user_name' => 'Evidence Unit ' . $roleName . ' ' . uniqid(),
            'user_email' => 'evidence_unit_' . strtolower($roleName) . '_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    private function makeTicket(int $requesterId): Ticket
    {
        return Ticket::create([
            'title' => 'Evidence Storage Ticket ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => 1,
            'type' => 1,
            'requester_id' => $requesterId,
            'resume_number' => 0,
        ]);
    }

    /** @test */
    public function store_evidence_file_persists_filesystem_record_when_provider_is_filesystem()
    {
        AppSetting::setValue('evidence_storage_provider', 'filesystem');

        $requester = $this->makeUser('Requester');
        $ticket = $this->makeTicket($requester->user_id);
        $file = UploadedFile::fake()->create('evidencia.pdf', 50, 'application/pdf');

        $localStorage = Mockery::mock(LocalEvidenceStorageService::class);
        $localStorage->shouldReceive('store')
            ->once()
            ->andReturn([
                'base_path' => 'C:/tmp/evidence',
                'relative_path' => $ticket->ticket_number . '/stored-file.pdf',
                'absolute_path' => 'C:/tmp/evidence/' . $ticket->ticket_number . '/stored-file.pdf',
            ]);

        $controller = new ServiceManagementController();

        $this->invokePrivate($controller, 'storeEvidenceFile', [$ticket, $file, $requester->user_id, $localStorage]);

        $this->assertDatabaseHas('ticket_evidences', [
            'ticket_id' => $ticket->ticket_id,
            'uploaded_by' => $requester->user_id,
            'storage_disk' => 'filesystem',
            'file_path' => $ticket->ticket_number . '/stored-file.pdf',
        ]);
    }

    /** @test */
    public function store_evidence_file_falls_back_to_local_when_google_drive_is_not_configured()
    {
        AppSetting::setValue('evidence_storage_provider', 'google_drive');

        $requester = $this->makeUser('Requester');
        $ticket = $this->makeTicket($requester->user_id);
        $file = UploadedFile::fake()->create('fallback.txt', 10, 'text/plain');

        $googleDrive = Mockery::mock(GoogleDriveStorageService::class);
        $googleDrive->shouldReceive('isConfigured')->once()->andReturn(false);
        $this->app->instance(GoogleDriveStorageService::class, $googleDrive);

        $localStorage = Mockery::mock(LocalEvidenceStorageService::class);
        $localStorage->shouldReceive('store')
            ->once()
            ->andReturn([
                'base_path' => 'C:/tmp/evidence',
                'relative_path' => $ticket->ticket_number . '/fallback.txt',
                'absolute_path' => 'C:/tmp/evidence/' . $ticket->ticket_number . '/fallback.txt',
            ]);

        $controller = new ServiceManagementController();

        $this->invokePrivate($controller, 'storeEvidenceFile', [$ticket, $file, $requester->user_id, $localStorage]);

        $record = TicketEvidence::where('ticket_id', $ticket->ticket_id)->latest('evidence_id')->first();

        $this->assertNotNull($record);
        $this->assertSame('filesystem', $record->storage_disk);
        $this->assertSame($ticket->ticket_number . '/fallback.txt', $record->file_path);
    }
}
