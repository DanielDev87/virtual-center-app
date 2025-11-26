<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketProgress extends Model
{
    use HasFactory;

    protected $table = 'ticket_progress';
    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'progress_description',
        'progress_percentage',
        'status_update',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
    ];

    /**
     * Get the ticket this progress belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    /**
     * Get the user who made this progress update
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
