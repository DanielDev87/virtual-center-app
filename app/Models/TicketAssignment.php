<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

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
