<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTracking extends Model
{
    use HasFactory;

    protected $table = 'project_tracking';
    protected $primaryKey = 'tracking_id';
    public $timestamps = true;

    protected $fillable = [
        'project_name',
        'project_description',
        'institution_id',
        'material_type_id',
        'project_status',
        'start_date',
        'end_date',
        'project_notes',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id');
    }

    public function materialType()
    {
        return $this->belongsTo(MaterialType::class, 'material_type_id', 'material_type_id');
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class, 'tracking_id', 'tracking_id');
    }
}


