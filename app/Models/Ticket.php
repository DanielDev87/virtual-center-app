<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'ticket_number',
        'title',
        'type',
        'request_type_id',
        'resume_number',
        'status',
        'requester_id',
        'requester_url',
        'requester_info',
        'mediator_id',
        'mediator_info',
        'check_points',
        'total_points',
        'rating',
        'priority',
        'progress_percentage',
        'faculty_id',
        'program_id',
        'course_id',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id');
    }

    public function mediator()
    {
        return $this->belongsTo(User::class, 'mediator_id', 'user_id');
    }

    public function requestType()
    {
        return $this->belongsTo(RequestType::class, 'request_type_id', 'type_id');
    }

    public function progress()
    {
        return $this->hasMany(TicketProgress::class, 'ticket_id', 'ticket_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get all assignments for this ticket
     */
    public function assignments()
    {
        return $this->hasMany(TicketAssignment::class, 'ticket_id', 'ticket_id');
    }

    /**
     * Get active mediators for this ticket
     */
    public function activeMediators()
    {
        return $this->belongsToMany(User::class, 'ticket_assignments', 'ticket_id', 'user_id')
                    ->wherePivot('status', 'active')
                    ->withPivot('job_position_id', 'assigned_at', 'notes', 'assignment_id');
    }
}
