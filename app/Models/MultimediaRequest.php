<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultimediaRequest extends Model
{
    use HasFactory;

    protected $primaryKey = 'request_id';

    protected $fillable = [
        'ticket_id',
        'request_type',
        'delivery_date',
        'script_url',
        'observation',
        'camera',
        'microphone',
        'lights',
        'sharepoint_url',
        'other_url',
        'subarea_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
