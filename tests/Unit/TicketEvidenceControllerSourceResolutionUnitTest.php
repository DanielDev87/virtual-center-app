<?php

namespace Tests\Unit;

use App\Http\Controllers\TicketEvidenceController;
use App\Models\TicketEvidence;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TicketEvidenceControllerSourceResolutionUnitTest extends TestCase
{
    use DatabaseTransactions;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeAdmin(): User
    {
        $adminRole = UserRole::firstOrCreate(['role_name' => 'Admin'], ['is_active' => true]);

        return User::create([
            'user_name' => 'Evidence Source Admin ' . uniqid(),
            'user_email' => 'evidence_source_admin_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $adminRole->role_id,
            'is_active' => true,
        ]);
    }

    private function mockEvidenceWithTicket(array $attributes = []): TicketEvidence
    {
        $ticketStub = new class {
            public function assignments(): object
            {
                return Mockery::mock();
            }
        };

        $relation = Mockery::mock();
        $relation->shouldReceive('with')->with('assignments')->andReturnSelf();
        $relation->shouldReceive('first')->andReturn($ticketStub);

        $evidence = Mockery::mock(TicketEvidence::class)->makePartial();
        foreach ($attributes as $key => $value) {
            $evidence->{$key} = $value;
        }

        $evidence->shouldReceive('ticket')->andReturn($relation);

        return $evidence;
    }

    /** @test */
    public function view_redirects_to_external_url_for_google_drive_evidence()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $evidence = $this->mockEvidenceWithTicket([
            'storage_disk' => 'google_drive',
            'external_url' => 'https://drive.google.com/file/d/abc/view',
            'file_path' => null,
        ]);

        $response = (new TicketEvidenceController())->view($evidence);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://drive.google.com/file/d/abc/view', $response->getTargetUrl());
    }

    /** @test */
    public function view_throws_404_when_storage_origin_is_not_resolvable()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $evidence = $this->mockEvidenceWithTicket([
            'storage_disk' => 'unknown',
            'external_url' => null,
            'file_path' => null,
        ]);

        $controller = new TicketEvidenceController();

        try {
            $controller->view($evidence);
            $this->fail('Expected HttpException 404 was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    /** @test */
    public function inline_throws_404_when_mime_type_is_not_image()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $evidence = $this->mockEvidenceWithTicket([
            'storage_disk' => 'filesystem',
            'mime_type' => 'application/pdf',
            'file_path' => '123/file.pdf',
            'file_name' => 'file.pdf',
        ]);

        $controller = new TicketEvidenceController();

        try {
            $controller->inline($evidence);
            $this->fail('Expected HttpException 404 was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }
}
