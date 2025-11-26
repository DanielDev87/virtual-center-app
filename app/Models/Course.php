<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'course_id';
    
    protected $fillable = [
        'program_id',
        'course_code',
        'course_name',
        'course_description',
        'credits',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credits' => 'integer',
    ];

    /**
     * Get the program that owns the course
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    /**
     * Get tickets for this course
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'course_id', 'course_id');
    }
}
