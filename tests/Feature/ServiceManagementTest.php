<?php

namespace Tests\Feature;

use App\Models\RequestType;
use App\Models\RequestTypeRegionalAssignment;
use App\Models\Ticket;
use App\Models\TicketEvidence;
use App\Models\TicketProgress;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Institution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $requester;
    protected $otherRequester;
    protected $contributor;
    protected $requestType;

    protected const PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    protected function setUp(): void
    {
        parent::setUp();

        $requesterRole = UserRole::firstOrCreate(['role_name' => 'Requester'], ['is_active' => true]);
        $contributorRole = UserRole::firstOrCreate(['role_name' => 'Contributor'], ['is_active' => true]);

        $this->requester = User::create([
            'user_name' => 'Requester Service Test',
            'user_email' => 'requester_service_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'document_number' => 'DOC-' . uniqid(),
            'is_active' => true,
        ]);

        $this->otherRequester = User::create([
            'user_name' => 'Other Requester Service Test',
            'user_email' => 'other_requester_service_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $requesterRole->role_id,
            'document_number' => 'DOC2-' . uniqid(),
            'is_active' => true,
        ]);

        $this->contributor = User::create([
            'user_name' => 'Contributor Service Test',
            'user_email' => 'contributor_service_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $contributorRole->role_id,
            'is_active' => true,
        ]);

        $this->requestType = RequestType::firstOrCreate(
            ['type_name' => 'Service Test Type'],
            ['gestor_id' => $this->contributor->user_id, 'is_active' => true]
        );
    }

    private function makeTicketFor(User $requester, int $status = 1): Ticket
    {
        return Ticket::create([
            'title' => 'Ticket Service Test ' . uniqid(),
            'ticket_number' => (int) (time() . rand(100, 999)),
            'status' => $status,
            'type' => 1,
            'request_type_id' => $this->requestType->type_id,
            'requester_id' => $requester->user_id,
            'resume_number' => 0,
            'progress_percentage' => $status === 3 ? 100 : 0,
        ]);
    }

    private function guestStorePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Solicitud publica base',
            'description' => 'Detalle de solicitud publica para pruebas',
            'request_type_id' => $this->requestType->type_id,
            'priority' => 2,
            'document_number' => 'DOC-GUEST-' . uniqid(),
            'requester_name' => 'Solicitante Publico',
            'requester_email' => 'guest_requester_' . uniqid() . '@test.com',
            'institution_link' => 'https://ula.ve',
            'policy_accepted' => '1',
        ], $overrides);
    }

    /** @test */
    public function guest_can_open_service_request_form()
    {
        $response = $this->get(route('service-management.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function blocked_topic_incident_prevents_new_ticket_creation()
    {
        $this->requestType->update([
            'incident_active' => true,
            'incident_title' => 'Intermitencia general',
            'incident_message' => 'El servicio está temporalmente suspendido mientras se corrige la incidencia.',
        ]);

        $response = $this->post(route('service-management.store'), $this->guestStorePayload([
            'request_type_id' => $this->requestType->type_id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('tickets', [
            'title' => 'Solicitud publica base',
            'request_type_id' => $this->requestType->type_id,
        ]);
    }

    /** @test */
    public function guest_can_create_ticket_and_requester_by_document_number()
    {
        Mail::fake();

        $document = 'DOC-GUEST-' . uniqid();
        $email = 'guest_requester_' . uniqid() . '@test.com';

        $response = $this->post(route('service-management.store'), $this->guestStorePayload([
            'title' => 'Solicitud publica',
            'document_number' => $document,
            'requester_email' => $email,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('new_ticket');

        $this->assertDatabaseHas('users', [
            'document_number' => $document,
            'user_email' => $email,
        ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Solicitud publica',
            'request_type_id' => $this->requestType->type_id,
            'status' => 1,
        ]);
    }

    /** @test */
    public function guest_cannot_create_ticket_without_accepting_policy()
    {
        $response = $this->from(route('service-management.create'))
            ->post(route('service-management.store'), $this->guestStorePayload([
                'policy_accepted' => null,
            ]));

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('policy_accepted');
    }

    /** @test */
    public function guest_cannot_create_ticket_with_invalid_email_format()
    {
        $response = $this->from(route('service-management.create'))
            ->post(route('service-management.store'), $this->guestStorePayload([
                'requester_email' => 'correo-invalido',
            ]));

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('requester_email');
    }

    /** @test */
    public function guest_cannot_create_ticket_with_invalid_priority_value()
    {
        $response = $this->from(route('service-management.create'))
            ->post(route('service-management.store'), $this->guestStorePayload([
                'priority' => 9,
            ]));

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('priority');
    }

    /** @test */
    public function authenticated_requester_can_create_ticket_without_guest_only_fields()
    {
        Mail::fake();

        $response = $this->actingAs($this->requester)
            ->post(route('service-management.store'), [
                'title' => 'Solicitud autenticada',
                'description' => 'Detalle para usuario autenticado',
                'request_type_id' => $this->requestType->type_id,
                'priority' => 3,
                'priority_justification' => 'Esta solicitud impacta procesos academicos en curso y requiere respuesta prioritaria.',
            ]);

        $response->assertRedirect(route('service-management.index'));
        $response->assertSessionHas('new_ticket');

        $this->assertDatabaseHas('tickets', [
            'title' => 'Solicitud autenticada',
            'requester_id' => $this->requester->user_id,
            'priority' => 3,
        ]);
    }

    /** @test */
    public function authenticated_requester_cannot_create_high_priority_ticket_without_justification()
    {
        Mail::fake();

        $response = $this->from(route('service-management.create'))
            ->actingAs($this->requester)
            ->post(route('service-management.store'), [
                'title' => 'Solicitud alta sin justificacion',
                'description' => 'Detalle para solicitud de alta prioridad sin justificacion',
                'request_type_id' => $this->requestType->type_id,
                'priority' => 3,
                'priority_justification' => '',
            ]);

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('priority_justification');

        $this->assertDatabaseMissing('tickets', [
            'title' => 'Solicitud alta sin justificacion',
            'requester_id' => $this->requester->user_id,
        ]);
    }

    /** @test */
    public function guest_cannot_create_urgent_ticket_with_non_meaningful_justification()
    {
        Mail::fake();

        $response = $this->from(route('service-management.create'))
            ->post(route('service-management.store'), $this->guestStorePayload([
                'title' => 'Solicitud urgente sin justificacion valida',
                'priority' => 4,
                'priority_justification' => '......................',
            ]));

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('priority_justification');

        $this->assertDatabaseMissing('tickets', [
            'title' => 'Solicitud urgente sin justificacion valida',
        ]);
    }

    /** @test */
    public function guest_topic_with_regional_assignment_requires_institution_and_assigns_configured_responsible()
    {
        if (!Schema::hasTable('request_type_regional_assignments')) {
            $this->markTestSkipped('La tabla request_type_regional_assignments no existe en esta base de pruebas. Ejecuta migraciones para validar este escenario.');
        }

        Mail::fake();

        $regionalContributor = User::create([
            'user_name' => 'Regional Contributor Service Test',
            'user_email' => 'regional_contributor_service_' . uniqid() . '@test.com',
            'password' => self::PASSWORD_HASH,
            'role_id' => $this->contributor->role_id,
            'is_active' => true,
        ]);

        $institution = Institution::create([
            'institution_name' => 'Regional Medellin Test',
            'institution_description' => 'Regional para pruebas',
            'is_active' => true,
        ]);

        RequestTypeRegionalAssignment::create([
            'request_type_id' => $this->requestType->type_id,
            'institution_id' => $institution->institution_id,
            'user_id' => $regionalContributor->user_id,
        ]);

        $responseWithoutInstitution = $this->from(route('service-management.create'))
            ->post(route('service-management.store'), $this->guestStorePayload([
                'title' => 'Solicitud regional sin sede',
            ]));

        $responseWithoutInstitution->assertRedirect(route('service-management.create'));
        $responseWithoutInstitution->assertSessionHasErrors('institution_id');

        $response = $this->post(route('service-management.store'), $this->guestStorePayload([
            'title' => 'Solicitud regional con sede',
            'institution_id' => $institution->institution_id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('new_ticket');

        $ticket = Ticket::where('title', 'Solicitud regional con sede')->latest('ticket_id')->first();
        $this->assertNotNull($ticket);
        $this->assertSame((int) $institution->institution_id, (int) $ticket->institution_id);
        $this->assertSame((int) $regionalContributor->user_id, (int) $ticket->mediator_id);

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $regionalContributor->user_id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function check_requester_returns_exists_true_for_known_document()
    {
        $response = $this->post(route('service-management.checkRequester'), [
            'document_number' => $this->requester->document_number,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
                'user' => [
                    'user_name' => $this->requester->user_name,
                    'user_email' => $this->requester->user_email,
                ],
            ]);
    }

    /** @test */
    public function check_requester_reports_pending_ratings_when_completed_tickets_are_unrated()
    {
        $ticket = $this->makeTicketFor($this->requester, 3);
        $ticket->update(['rating' => null]);

        $response = $this->post(route('service-management.checkRequester'), [
            'document_number' => $this->requester->document_number,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
                'has_pending_ratings' => true,
            ]);

        $this->assertNotEmpty($response->json('pending_tickets'));
    }

    /** @test */
    public function public_track_search_returns_view_for_existing_ticket_number()
    {
        $ticket = $this->makeTicketFor($this->requester);

        $response = $this->from(route('service-management.track'))
            ->post(route('service-management.searchTrack'), [
                'document_number' => $this->requester->document_number,
                'ticket_number' => $ticket->ticket_number,
            ]);

        $response->assertStatus(200);
        $response->assertViewIs('service-management.track');
        $response->assertViewHas('ticket');
    }

    /** @test */
    public function authenticated_requester_can_view_own_ticket()
    {
        $ticket = $this->makeTicketFor($this->requester);

        $response = $this->actingAs($this->requester)
            ->get(route('service-management.show', $ticket->ticket_id));

        $response->assertStatus(200);
    }

    /** @test */
    public function requester_cannot_view_ticket_of_another_user()
    {
        $ticket = $this->makeTicketFor($this->otherRequester);

        $response = $this->actingAs($this->requester)
            ->get(route('service-management.show', $ticket->ticket_id));

        $response->assertStatus(404);
    }

    /** @test */
    public function requester_can_rate_completed_ticket()
    {
        Mail::fake();

        $ticket = $this->makeTicketFor($this->requester, 3);

        $response = $this->actingAs($this->requester)
            ->post(route('service-management.rate', $ticket->ticket_id), [
                'rating' => 5,
                'comment' => 'Excelente servicio',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'rating' => 5,
            'feedback' => 'Excelente servicio',
        ]);
    }

    /** @test */
    public function requester_must_add_comment_when_rating_is_below_five_stars()
    {
        Mail::fake();

        $ticket = $this->makeTicketFor($this->requester, 3);

        $response = $this->from(route('service-management.show', $ticket->ticket_id))
            ->actingAs($this->requester)
            ->post(route('service-management.rate', $ticket->ticket_id), [
                'rating' => 4,
                'comment' => '',
            ]);

        $response->assertRedirect(route('service-management.show', $ticket->ticket_id));
        $response->assertSessionHasErrors('comment');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'rating' => null,
        ]);
    }

    /** @test */
    public function requester_cannot_rate_below_five_with_non_meaningful_comment()
    {
        Mail::fake();

        $ticket = $this->makeTicketFor($this->requester, 3);

        $response = $this->from(route('service-management.show', $ticket->ticket_id))
            ->actingAs($this->requester)
            ->post(route('service-management.rate', $ticket->ticket_id), [
                'rating' => 4,
                'comment' => '...............',
            ]);

        $response->assertRedirect(route('service-management.show', $ticket->ticket_id));
        $response->assertSessionHasErrors('comment');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'rating' => null,
        ]);
    }

    /** @test */
    public function requester_cannot_rate_non_completed_ticket()
    {
        Mail::fake();

        $ticket = $this->makeTicketFor($this->requester, 2);

        $response = $this->actingAs($this->requester)
            ->post(route('service-management.rate', $ticket->ticket_id), [
                'rating' => 3,
                'comment' => 'Aun en progreso',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'rating' => 3,
        ]);
    }

    /** @test */
    public function public_requester_must_add_comment_when_rating_is_below_five_stars()
    {
        Mail::fake();

        $ticket = $this->makeTicketFor($this->requester, 3);

        $response = $this->from(route('service-management.track', [
                'ticket_number' => $ticket->ticket_number,
                'document_number' => $this->requester->document_number,
            ]))
            ->post(route('service-management.ratePublic'), [
                'ticket_number' => $ticket->ticket_number,
                'rating' => 3,
                'comment' => '',
            ]);

        $response->assertRedirect(route('service-management.track', [
            'ticket_number' => $ticket->ticket_number,
            'document_number' => $this->requester->document_number,
        ]));
        $response->assertSessionHasErrors('comment');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'rating' => null,
        ]);
    }

    /** @test */
    public function public_requester_cannot_rate_below_five_with_non_meaningful_comment()
    {
        Mail::fake();

        $ticket = $this->makeTicketFor($this->requester, 3);

        $response = $this->from(route('service-management.track', [
                'ticket_number' => $ticket->ticket_number,
                'document_number' => $this->requester->document_number,
            ]))
            ->post(route('service-management.ratePublic'), [
                'ticket_number' => $ticket->ticket_number,
                'rating' => 2,
                'comment' => '...............',
            ]);

        $response->assertRedirect(route('service-management.track', [
            'ticket_number' => $ticket->ticket_number,
            'document_number' => $this->requester->document_number,
        ]));
        $response->assertSessionHasErrors('comment');

        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'rating' => null,
        ]);
    }

    /** @test */
    public function authenticated_requester_cannot_create_new_ticket_when_has_completed_ticket_pending_rating()
    {
        Mail::fake();

        $this->makeTicketFor($this->requester, 3);

        $response = $this->from(route('service-management.create'))
            ->actingAs($this->requester)
            ->post(route('service-management.store'), [
                'title' => 'Solicitud bloqueada por pendiente de calificar',
                'description' => 'No debe crearse si existe ticket completado sin calificar',
                'request_type_id' => $this->requestType->type_id,
                'priority' => 2,
            ]);

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('rating_pending');

        $this->assertDatabaseMissing('tickets', [
            'title' => 'Solicitud bloqueada por pendiente de calificar',
            'requester_id' => $this->requester->user_id,
        ]);
    }

    /** @test */
    public function authenticated_requester_cannot_create_new_ticket_when_completed_ticket_has_zero_rating()
    {
        Mail::fake();

        $completedWithZeroRating = $this->makeTicketFor($this->requester, 3);
        $completedWithZeroRating->update([
            'rating' => 0,
            'feedback' => null,
        ]);

        $response = $this->from(route('service-management.create'))
            ->actingAs($this->requester)
            ->post(route('service-management.store'), [
                'title' => 'Solicitud bloqueada por rating cero',
                'description' => 'No debe crearse cuando existe ticket completado con rating 0',
                'request_type_id' => $this->requestType->type_id,
                'priority' => 2,
            ]);

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('rating_pending');

        $this->assertDatabaseMissing('tickets', [
            'title' => 'Solicitud bloqueada por rating cero',
            'requester_id' => $this->requester->user_id,
        ]);
    }

    /** @test */
    public function guest_cannot_create_new_ticket_when_existing_requester_has_completed_ticket_pending_rating()
    {
        Mail::fake();

        $this->makeTicketFor($this->requester, 3);

        $payload = $this->guestStorePayload([
            'title' => 'Solicitud guest bloqueada por pendiente',
            'document_number' => $this->requester->document_number,
            'requester_name' => $this->requester->user_name,
            'requester_email' => $this->requester->user_email,
        ]);

        $response = $this->from(route('service-management.create'))
            ->post(route('service-management.store'), $payload);

        $response->assertRedirect(route('service-management.create'));
        $response->assertSessionHasErrors('rating_pending');

        $this->assertDatabaseMissing('tickets', [
            'title' => 'Solicitud guest bloqueada por pendiente',
            'requester_id' => $this->requester->user_id,
        ]);
    }

    /** @test */
    public function requester_can_send_message_from_public_tracking_when_ticket_is_open()
    {
        $ticket = $this->makeTicketFor($this->requester, 2);

        $response = $this->post(route('service-management.trackMessage'), [
            'document_number' => $this->requester->document_number,
            'ticket_number' => $ticket->ticket_number,
            'message' => 'Necesito confirmar un detalle adicional.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ticket_progress', [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $this->requester->user_id,
            'status_update' => 'requester_message',
        ]);
    }

    /** @test */
    public function requester_cannot_send_message_from_public_tracking_when_ticket_is_closed()
    {
        $ticket = $this->makeTicketFor($this->requester, 3);

        $response = $this->post(route('service-management.trackMessage'), [
            'document_number' => $this->requester->document_number,
            'ticket_number' => $ticket->ticket_number,
            'message' => 'Intento de mensaje fuera de tiempo.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(0, TicketProgress::where('ticket_id', $ticket->ticket_id)
            ->where('status_update', 'requester_message')
            ->count());
    }

    /** @test */
    public function requester_can_send_message_with_attachments_from_public_tracking()
    {
        Storage::fake('public');

        $ticket = $this->makeTicketFor($this->requester, 2);

        $response = $this->post(route('service-management.trackMessage'), [
            'document_number' => $this->requester->document_number,
            'ticket_number' => $ticket->ticket_number,
            'message' => 'Adjunto evidencia del incidente.',
            'attachments' => [
                UploadedFile::fake()->create('captura.png', 120, 'image/png'),
                UploadedFile::fake()->create('detalle.pdf', 120, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame(2, TicketEvidence::where('ticket_id', $ticket->ticket_id)->count());

        $progress = TicketProgress::where('ticket_id', $ticket->ticket_id)
            ->where('status_update', 'requester_message')
            ->latest('progress_id')
            ->first();

        $this->assertNotNull($progress);
        $this->assertStringContainsString('[attachments:', (string) $progress->progress_description);
    }

    /** @test */
    public function public_tracking_evidence_download_requires_valid_ticket_context()
    {
        Storage::fake('public');

        $ticket = $this->makeTicketFor($this->requester, 2);
        $evidence = TicketEvidence::create([
            'ticket_id' => $ticket->ticket_id,
            'uploaded_by' => $this->requester->user_id,
            'file_name' => 'test.txt',
            'storage_disk' => 'public',
            'file_path' => 'evidences/test.txt',
            'mime_type' => 'text/plain',
            'file_size' => 10,
            'external_url' => null,
        ]);
        Storage::disk('public')->put('evidences/test.txt', 'contenido');

        $this->get(route('service-management.trackEvidence', [
            'evidence' => $evidence->evidence_id,
            'ticket_number' => $ticket->ticket_number,
            'document_number' => $this->requester->document_number,
        ]))->assertStatus(200);

        $this->get(route('service-management.trackEvidence', [
            'evidence' => $evidence->evidence_id,
            'ticket_number' => $ticket->ticket_number,
            'document_number' => $this->otherRequester->document_number,
        ]))->assertStatus(403);
    }
}
