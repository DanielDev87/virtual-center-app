<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestTypeQuestion extends Model
{
    use HasFactory;

    public const FIELD_TYPES = [
        'text' => 'Texto corto',
        'textarea' => 'Texto largo',
        'number' => 'Número',
        'date' => 'Fecha',
        'select' => 'Lista de opciones',
    ];

    protected $table = 'request_type_questions';
    protected $primaryKey = 'question_id';

    protected $fillable = [
        'request_type_id',
        'label',
        'field_type',
        'options',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function requestType()
    {
        return $this->belongsTo(RequestType::class, 'request_type_id', 'type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}