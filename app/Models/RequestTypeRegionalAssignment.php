<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestTypeRegionalAssignment extends Model
{
    protected $table = 'request_type_regional_assignments';

    protected $fillable = [
        'request_type_id',
        'institution_id',
        'user_id',
    ];

    public function requestType()
    {
        return $this->belongsTo(RequestType::class, 'request_type_id', 'type_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'institution_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
