<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $table = 'institutions';
    protected $primaryKey = 'institution_id';
    public $timestamps = true;

    protected $fillable = [
        'institution_name',
        'institution_description',
        'institution_logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function projectTracking()
    {
        return $this->hasMany(ProjectTracking::class, 'institution_id', 'institution_id');
    }
}

