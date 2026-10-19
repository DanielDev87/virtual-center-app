<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAnswer extends Model
{
    use HasFactory;

    protected $table = 'ticket_answers';
    protected $primaryKey = 'answer_id';

    protected $fillable = [
        'ticket_id',
        'question_id',
        'question_label',
        'answer_value',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function question()
    {
        return $this->belongsTo(RequestTypeQuestion::class, 'question_id', 'question_id');
    }
}