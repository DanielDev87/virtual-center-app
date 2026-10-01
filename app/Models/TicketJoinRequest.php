<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketJoinRequest extends Model
{
    protected $table = 'ticket_join_requests';
    protected $primaryKey = 'join_request_id';

    protected $fillable = [
        'ticket_id',
        'requester_id',
        'reviewed_by',
        'status',
        'request_note',
        'review_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }
}
