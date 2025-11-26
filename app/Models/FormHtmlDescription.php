<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormHtmlDescription extends Model
{
    use HasFactory;

    protected $table = 'form_html_descriptions';
    protected $primaryKey = 'description_id';
    public $timestamps = true;

    protected $fillable = [
        'form_id',
        'html_content',
        'content_type'
    ];

    // Relaciones
    public function mediationForm()
    {
        return $this->belongsTo(MediationForm::class, 'form_id', 'form_id');
    }
}


