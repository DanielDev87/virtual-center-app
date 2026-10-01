<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class RequestType extends Model
{
    use HasFactory;

    protected $table = 'request_types';
    protected $primaryKey = 'type_id';

    protected $fillable = [
        'type_name',
        'type_description',
        'gestor_id',
        'department_id',
        'sla_hours',
        'type_icon',
        'type_color',
        'is_active',
        'incident_active',
        'incident_title',
        'incident_message',
        'incident_started_at',
        'area_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'incident_active' => 'boolean',
        'incident_started_at' => 'datetime',
    ];

    /**
     * Get tickets of this type
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'request_type_id', 'type_id');
    }

    /**
     * Get the gestor (contributor) responsible for this type
     */
    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id', 'user_id');
    }

    /**
     * Get all collaborators assigned to this topic.
     */
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'request_type_user', 'request_type_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Resolve the preferred mediator for ticket assignment.
     */
    public function resolvePreferredMediatorId(?int $excludeUserId = null): ?int
    {
        if (!Schema::hasTable('request_type_user')) {
            $gestorId = $this->gestor_id ? (int) $this->gestor_id : null;
            return ($gestorId !== null && $gestorId !== $excludeUserId) ? $gestorId : null;
        }

        $collaboratorIds = $this->collaborators->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($collaboratorIds->isEmpty()) {
            return $this->gestor_id ? (int) $this->gestor_id : null;
        }

        $gestorId = $this->gestor_id ? (int) $this->gestor_id : null;

        if ($gestorId !== null && $collaboratorIds->contains($gestorId) && $gestorId !== $excludeUserId) {
            return $gestorId;
        }

        $candidate = $collaboratorIds->first(fn ($id) => $id !== $excludeUserId);

        return $candidate !== null ? (int) $candidate : null;
    }

    /**
     * Get the area this request type belongs to
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }

    /**
     * Regional assignments configured for this topic.
     */
    public function regionalAssignments()
    {
        return $this->hasMany(RequestTypeRegionalAssignment::class, 'request_type_id', 'type_id');
    }
}
