<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    use HasFactory;

    protected $table = 'request_types';
    protected $primaryKey = 'type_id';

    protected $fillable = [
        'type_name',
        'type_description',
        'type_icon',
        'type_color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get tickets of this type
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'request_type_id', 'type_id');
    }
}
