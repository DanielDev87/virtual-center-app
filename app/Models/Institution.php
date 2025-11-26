<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $table = 'institutions';
    protected $primaryKey = 'institution_id';
    
    protected $fillable = [
        'institution_name',
        'institution_description',
        'institution_logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get faculties for this institution
     */
    public function faculties()
    {
        return $this->hasMany(Faculty::class, 'institution_id', 'institution_id');
    }

    /**
     * Get tickets for this institution
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'institution_id', 'institution_id');
    }
}
