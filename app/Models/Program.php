<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'programs';
    protected $primaryKey = 'program_id';
    
    protected $fillable = [
        'faculty_id',
        'program_code',
        'program_name',
        'program_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the faculty that owns the program
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }

    /**
     * Get courses for this program
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'program_id', 'program_id');
    }

    /**
     * Get tickets for this program
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'program_id', 'program_id');
    }
}
