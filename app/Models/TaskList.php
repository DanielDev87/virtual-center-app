<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskList extends Model
{
    use HasFactory;

    protected $table = 'task_lists';
    protected $primaryKey = 'task_id';
    public $timestamps = true;

    protected $fillable = [
        'task_name',
        'task_description',
        'assigned_to',
        'assigned_by',
        'assignment_date',
        'due_date',
        'task_status',
        'task_notes',
        'is_urgent'
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'due_date' => 'date',
        'is_urgent' => 'boolean',
    ];

    // Relaciones
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }
}



