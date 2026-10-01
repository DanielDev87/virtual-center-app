<?php

namespace App\Http\Controllers;

use App\Models\TicketEvidence;
use App\Services\CustomObjectStorageService;
use App\Services\LocalEvidenceStorageService;
use App\Services\LocalRichTextImageStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class TicketEvidenceController extends Controller
{
    public function view(TicketEvidence $evidence)
    {
        $user = Auth::user();
        $ticket = $evidence->ticket()->with(['assignments', 'requestType.regionalAssignments'])->first();

        if (!$user || !$ticket || !$this->canAccessEvidence($user, $ticket)) {
            abort(403, 'No autorizado para acceder a esta evidencia.');
        }

        if ($evidence->storage_disk === 'google_drive' && !empty($evidence->external_url)) {
            return redirect()->away($evidence->external_url);
        }

        if ($evidence->storage_disk === 'public' && !empty($evidence->file_path)) {
            if (!Storage::disk('public')->exists($evidence->file_path)) {
                abort(404, 'La evidencia no existe en el almacenamiento.');
            }

            return Storage::disk('public')->download($evidence->file_path, $evidence->file_name);
        }

        if ($evidence->storage_disk === 'filesystem' && !empty($evidence->file_path)) {
            $storage = app(LocalEvidenceStorageService::class);
            $absolutePath = $storage->resolveAbsolutePath($evidence->file_path);

            if (!File::exists($absolutePath)) {
                abort(404, 'La evidencia no existe en el almacenamiento fisico configurado.');
            }

            return response()->download($absolutePath, $evidence->file_name);
        }

        if ($evidence->storage_disk === 'richtext_filesystem' && !empty($evidence->file_path)) {
            $storage = app(LocalRichTextImageStorageService::class);
            $absolutePath = $storage->resolveAbsolutePath($evidence->file_path);

            if (!File::exists($absolutePath)) {
                abort(404, 'La evidencia no existe en el almacenamiento fisico de contenido enriquecido.');
            }

            return response()->download($absolutePath, $evidence->file_name);
        }

        if ($evidence->storage_disk === 'custom' && !empty($evidence->file_path)) {
            $customStorage = app(CustomObjectStorageService::class);
            $downloadResponse = $customStorage->download($evidence->file_path, $evidence->file_name);

            if ($downloadResponse !== null) {
                return $downloadResponse;
            }

            abort(404, 'La evidencia no existe en el almacenamiento custom configurado.');
        }

        abort(404, 'No se encontró un origen válido para la evidencia.');
    }

    public function inline(TicketEvidence $evidence)
    {
        $user = Auth::user();
        $ticket = $evidence->ticket()->with(['assignments', 'requestType.regionalAssignments'])->first();

        if (!$user || !$ticket || !$this->canAccessEvidence($user, $ticket)) {
            abort(403, 'No autorizado para acceder a este recurso.');
        }

        if (empty($evidence->mime_type) || !str_starts_with((string) $evidence->mime_type, 'image/')) {
            abort(404, 'Este recurso no es una imagen embebible.');
        }

        if ($evidence->storage_disk === 'filesystem' && !empty($evidence->file_path)) {
            $storage = app(LocalEvidenceStorageService::class);
            $absolutePath = $storage->resolveAbsolutePath($evidence->file_path);

            if (!File::exists($absolutePath)) {
                abort(404, 'La evidencia no existe en el almacenamiento fisico configurado.');
            }

            return response()->file($absolutePath, [
                'Content-Type' => $evidence->mime_type,
                'Content-Disposition' => 'inline; filename="' . $evidence->file_name . '"',
            ]);
        }

        if ($evidence->storage_disk === 'richtext_filesystem' && !empty($evidence->file_path)) {
            $storage = app(LocalRichTextImageStorageService::class);
            $absolutePath = $storage->resolveAbsolutePath($evidence->file_path);

            if (!File::exists($absolutePath)) {
                abort(404, 'La evidencia no existe en el almacenamiento fisico de contenido enriquecido.');
            }

            return response()->file($absolutePath, [
                'Content-Type' => $evidence->mime_type,
                'Content-Disposition' => 'inline; filename="' . $evidence->file_name . '"',
            ]);
        }

        if ($evidence->storage_disk === 'public' && !empty($evidence->file_path)) {
            if (!Storage::disk('public')->exists($evidence->file_path)) {
                abort(404, 'La evidencia no existe en el almacenamiento publico.');
            }

            return response()->file(Storage::disk('public')->path($evidence->file_path), [
                'Content-Type' => $evidence->mime_type,
                'Content-Disposition' => 'inline; filename="' . $evidence->file_name . '"',
            ]);
        }

        abort(404, 'No se encontro un origen valido para mostrar en linea.');
    }

    private function canAccessEvidence($user, $ticket): bool
    {
        $role = $user->role?->role_name;

        if (in_array($role, ['Admin', 'Monitor', 'Super Admin Tecnico'], true)) {
            return true;
        }

        if ($role === 'Requester' && (int) $ticket->requester_id === (int) $user->user_id) {
            return true;
        }

        if ($role === 'Admin Área') {
            $requestType = $ticket->requestType;
            if (!$requestType || (int) $requestType->area_id !== (int) $user->area_id) {
                return false;
            }

            if (!Schema::hasTable('request_type_regional_assignments')) {
                return true;
            }

            $regionalAssignments = $requestType->regionalAssignments;
            if ($regionalAssignments->isEmpty()) {
                return true;
            }

            return $regionalAssignments
                ->where('user_id', $user->user_id)
                ->contains(fn ($assignment) => (int) $assignment->institution_id === (int) $ticket->institution_id);
        }

        if (in_array($role, ['Contributor', 'Operario'], true)) {
            if ((int) $ticket->mediator_id === (int) $user->user_id) {
                return true;
            }

            return $ticket->assignments()
                ->where('user_id', $user->user_id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }
}
