<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
     * Programs linked through pivot table (many-to-many).
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'course_program', 'course_id', 'program_id')
            ->withTimestamps();
    }

    /**
     * Keep legacy program_id aligned with the first linked program.
     */
    public function syncPrograms(array $programIds): void
    {
        $programIds = array_values(array_unique(array_map('intval', array_filter($programIds, function ($value) {
            return $value !== null && $value !== '';
        }))));

        if (Schema::hasTable('course_program')) {
            $this->programs()->sync($programIds);
        }

        if (!empty($programIds)) {
            if ((int) $this->program_id !== (int) $programIds[0]) {
                $this->forceFill(['program_id' => (int) $programIds[0]])->save();
            }
        }
    }

    /**
     * Get tickets for this course
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'course_id', 'course_id');
    }
}
