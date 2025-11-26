<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    protected $table = 'job_positions';
    protected $primaryKey = 'job_position_id';
    
    protected $fillable = [
        'position_name',
        'position_description',
        'position_color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get assignments for this job position
     */
    public function assignments()
    {
        return $this->hasMany(TicketAssignment::class, 'job_position_id', 'job_position_id');
    }

    /**
     * Get the users associated with this job position.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'job_position_user', 'job_position_id', 'user_id')
                    ->withTimestamps();
    }
}
