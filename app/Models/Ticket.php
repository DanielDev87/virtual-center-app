<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    private const DEFAULT_PRIORITY_SLA_HOURS = [
        1 => 72,
        2 => 48,
        3 => 24,
        4 => 8,
    ];

    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'ticket_number',
        'parent_ticket_id',
        'title',
        'type',
        'request_type_id',
        'institution_id',
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
        'current_phase',
        'faculty_id',
        'program_id',
        'course_id',
        'resource_link',
        'is_reopened',
        'reopened_at',
        'response_overdue_notified_at',
        'feedback',
    ];

    protected $casts = [
        'current_phase' => 'string',
        'reopened_at' => 'datetime',
        'response_overdue_notified_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id');
    }

    public function parentTicket()
    {
        return $this->belongsTo(self::class, 'parent_ticket_id', 'ticket_id');
    }

    public function childTickets()
    {
        return $this->hasMany(self::class, 'parent_ticket_id', 'ticket_id');
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

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id');
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

    public function sprints()
    {
        return $this->hasMany(Sprint::class, 'ticket_id', 'ticket_id');
    }

    public function projectTasks()
    {
        return $this->hasMany(ProjectTask::class, 'ticket_id', 'ticket_id');
    }

    public function evidences()
    {
        return $this->hasMany(TicketEvidence::class, 'ticket_id', 'ticket_id');
    }

    public function joinRequests()
    {
        return $this->hasMany(TicketJoinRequest::class, 'ticket_id', 'ticket_id');
    }

    public function associationRequests()
    {
        return $this->hasMany(TicketAssociationRequest::class, 'parent_ticket_id', 'ticket_id');
    }

    /**
     * Horas SLA según prioridad del ticket.
     */
    public function getPrioritySlaHoursAttribute(): ?int
    {
        if (!$this->priority) {
            return null;
        }

        $map = config('sla.priority_hours', self::DEFAULT_PRIORITY_SLA_HOURS);
        return isset($map[(int) $this->priority]) ? (int) $map[(int) $this->priority] : null;
    }

    /**
     * Fecha/hora límite de respuesta calculada desde la creación del ticket.
     */
    public function getResponseDeadlineAttribute()
    {
        if (!$this->created_at || !$this->priority_sla_hours) {
            return null;
        }

        return app(\App\Services\BusinessHoursService::class)
            ->addMinutes($this->created_at->copy(), $this->priority_sla_hours * 60);
    }

    /**
     * Horas restantes para cumplir el SLA (negativo si está vencido).
     */
    public function getRemainingResponseHoursAttribute(): ?int
    {
        if (!$this->response_deadline) {
            return null;
        }

        $minutes = app(\App\Services\BusinessHoursService::class)
            ->businessMinutesBetween(now(), $this->response_deadline);
        return (int) ceil($minutes / 60);
    }

    /**
     * Indica si el ticket superó su tiempo objetivo de respuesta.
     */
    public function getIsResponseOverdueAttribute(): bool
    {
        if (!$this->response_deadline) {
            return false;
        }

        if (in_array((int) $this->status, [3, 4], true)) {
            return false;
        }

        return now()->greaterThan($this->response_deadline);
    }

}
