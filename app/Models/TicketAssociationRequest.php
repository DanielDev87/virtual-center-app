<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAssociationRequest extends Model
{
    protected $table = 'ticket_association_requests';
    protected $primaryKey = 'association_request_id';

    protected $fillable = [
        'parent_ticket_id',
        'child_ticket_id',
        'requested_by',
        'request_group',
        'reviewed_by',
        'status',
        'request_note',
        'review_note',
        'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function parentTicket()
    {
        return $this->belongsTo(Ticket::class, 'parent_ticket_id', 'ticket_id');
    }

    public function childTicket()
    {
        return $this->belongsTo(Ticket::class, 'child_ticket_id', 'ticket_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }
}
