<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketEvidence;

class FinalTicketEvidenceService
{
    public function store(Ticket $ticket, array $files, int $userId): array
    {
        $storage = app(LocalEvidenceStorageService::class);
        $evidenceIds = [];

        foreach ($files as $file) {
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $stored = $storage->store($file, (string) $ticket->ticket_number);

            $evidence = TicketEvidence::create([
                'ticket_id' => $ticket->ticket_id,
                'uploaded_by' => $userId,
                'file_name' => $file->getClientOriginalName(),
                'storage_disk' => 'filesystem',
                'file_path' => $stored['relative_path'],
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'external_url' => null,
            ]);

            $evidenceIds[] = $evidence->evidence_id;
        }

        return $evidenceIds;
    }
}
