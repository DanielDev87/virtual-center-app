<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    use HasFactory;

    protected $primaryKey = 'sprint_id';

    protected $fillable = [
        'ticket_id',
        'name',
        'start_date',
        'end_date',
        'goal',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'sprint_id', 'sprint_id');
    }
}
