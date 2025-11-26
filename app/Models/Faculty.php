<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $table = 'faculties';
    protected $primaryKey = 'faculty_id';
    
    protected $fillable = [
        'institution_id',
        'faculty_name',
        'faculty_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the institution that owns the faculty
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id');
    }

    /**
     * Get programs for this faculty
     */
    public function programs()
    {
        return $this->hasMany(Program::class, 'faculty_id', 'faculty_id');
    }

    /**
     * Get areas for this faculty
     */
    public function areas()
    {
        return $this->hasMany(Area::class, 'faculty_id', 'faculty_id');
    }

    /**
     * Get tickets for this faculty
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'faculty_id', 'faculty_id');
    }
}
