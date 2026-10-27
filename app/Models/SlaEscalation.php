<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaEscalation extends Model
{
    protected $table = 'sla_escalations';
    protected $primaryKey = 'escalation_id';

    protected $fillable = [
        'ticket_id',
        'rule_id',
        'threshold_percent',
        'remaining_minutes',
        'recipients_count',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }
}