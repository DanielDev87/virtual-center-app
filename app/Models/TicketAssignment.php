<?php

namespace App\Models;

use App\Mail\TicketAssigned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class TicketAssignment extends Model
{
    protected $table = 'ticket_assignments';
    protected $primaryKey = 'assignment_id';
    
    protected $fillable = [
        'ticket_id',
        'user_id',
        'job_position_id',
        'assigned_by',
        'status',
        'notes',
        'assigned_at',
        'returned_alert_read_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $assignment): void {
            self::notifyAssignedCollaborator($assignment);
        });

        static::updated(function (self $assignment): void {
            if (!$assignment->wasChanged('status')) {
                return;
            }

            if (($assignment->status ?? null) !== 'active') {
                return;
            }

            if (($assignment->getOriginal('status') ?? null) === 'active') {
                return;
            }

            self::notifyAssignedCollaborator($assignment);
        });
    }

    protected static function notifyAssignedCollaborator(self $assignment): void
    {
        if (($assignment->status ?? null) !== 'active') {
            return;
        }

        $assignment->loadMissing(['ticket.requestType', 'mediator']);

        $recipient = $assignment->mediator;
        if (!$recipient || empty($recipient->user_email)) {
            return;
        }

        $roleName = $recipient->role?->role_name;
        if (!is_string($roleName) || !str_contains(strtolower($roleName), 'contributor')) {
            return;
        }

        try {
            Mail::to($recipient->user_email)->send(new TicketAssigned($assignment));
        } catch (\Throwable $e) {
            \Log::error('Error sending assignment email: ' . $e->getMessage());
        }
    }

    /**
     * Get the ticket for this assignment
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    /**
     * Get the mediator (user) for this assignment
     */
    public function mediator()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the job position for this assignment
     */
    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id', 'job_position_id');
    }

    /**
     * Get the user who made the assignment
     */
    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }
}
