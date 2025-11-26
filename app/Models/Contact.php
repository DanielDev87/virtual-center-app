<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $primaryKey = 'contact_id';

    protected $fillable = [
        'tag_id',
        'contact_name',
        'contact_email',
        'contact_image',
    ];

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
