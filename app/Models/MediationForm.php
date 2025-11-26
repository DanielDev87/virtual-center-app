<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediationForm extends Model
{
    use HasFactory;

    protected $table = 'mediation_forms';
    protected $primaryKey = 'form_id';
    public $timestamps = true;

    protected $fillable = [
        'form_title',
        'form_description',
        'form_data',
        'created_by',
        'form_status',
        'form_date'
    ];

    protected $casts = [
        'form_data' => 'array',
        'form_date' => 'date',
    ];

    // Relaciones
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function htmlDescriptions()
    {
        return $this->hasMany(FormHtmlDescription::class, 'form_id', 'form_id');
    }
}


