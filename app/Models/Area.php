<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'area_id';
    
    protected $fillable = [
        'faculty_id',
        'area_name',
        'area_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the faculty that owns the area
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }

    /**
     * Get the users associated with this area
     */
    public function users()
    {
        return $this->hasMany(User::class, 'area_id', 'area_id');
    }

    /**
     * Get the request types (topics) associated with this area
     */
    public function requestTypes()
    {
        return $this->hasMany(RequestType::class, 'area_id', 'area_id');
    }
}
