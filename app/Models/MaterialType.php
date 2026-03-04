<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialType extends Model
{
    use HasFactory;

    protected $table = 'material_types';
    protected $primaryKey = 'material_type_id';
    public $timestamps = true;

    protected $fillable = [
        'material_type_name',
        'material_type_description',
        'material_type_icon',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function projectTracking()
    {
        return $this->hasMany(ProjectTracking::class, 'material_type_id', 'material_type_id');
    }
}



