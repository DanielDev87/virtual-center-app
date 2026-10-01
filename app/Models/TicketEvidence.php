<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketEvidence extends Model
{
    use HasFactory;

    protected $table = 'ticket_evidences';

    protected $primaryKey = 'evidence_id';

    protected $fillable = [
        'ticket_id',
        'uploaded_by',
        'file_name',
        'storage_disk',
        'file_path',
        'mime_type',
        'file_size',
        'external_url',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }

    public function getPublicUrlAttribute(): ?string
    {
        if (in_array($this->storage_disk, ['filesystem', 'custom'], true) && !empty($this->file_path)) {
            return route('evidences.view', $this->evidence_id);
        }

        if ($this->storage_disk === 'richtext_filesystem' && !empty($this->file_path)) {
            return route('evidences.inline', $this->evidence_id);
        }

        if (!empty($this->external_url)) {
            return $this->external_url;
        }

        if ($this->storage_disk === 'public' && !empty($this->file_path)) {
            return route('evidences.view', $this->evidence_id);
        }

        return null;
    }
}
