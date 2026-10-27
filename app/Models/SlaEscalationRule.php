<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaEscalationRule extends Model
{
    protected $table = 'sla_escalation_rules';
    protected $primaryKey = 'rule_id';

    protected $fillable = [
        'request_type_id',
        'priority',
        'threshold_percent',
        'escalate_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function requestType()
    {
        return $this->belongsTo(RequestType::class, 'request_type_id', 'type_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'escalate_user_id', 'user_id');
    }
}