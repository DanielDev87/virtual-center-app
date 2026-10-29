<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAudit extends Model
{
    protected $table = 'ticket_audits';
    protected $primaryKey = 'audit_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ticket_id', 'user_id', 'action', 'old_values', 'new_values', 'created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }
}
