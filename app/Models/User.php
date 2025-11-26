<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = true;

    protected $fillable = [
        'user_name',
        'user_email',
        'password',
        'user_phone',
        'user_bio',
        'user_avatar',
        'role_id',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id', 'role_id');
    }

    public function jobPositions()
    {
        return $this->belongsToMany(JobPosition::class, 'job_position_user', 'user_id', 'job_position_id')
                    ->withTimestamps();
    }

    public function assignedTasks()
    {
        return $this->hasMany(TaskList::class, 'assigned_to', 'user_id');
    }

    public function createdTasks()
    {
        return $this->hasMany(TaskList::class, 'assigned_by', 'user_id');
    }

    public function projectComments()
    {
        return $this->hasMany(ProjectComment::class, 'user_id', 'user_id');
    }

    public function mediationForms()
    {
        return $this->hasMany(MediationForm::class, 'created_by', 'user_id');
    }
}


