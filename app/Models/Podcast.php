<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;

    protected $primaryKey = 'podcast_id';

    protected $fillable = [
        'embed_code',
    ];
}
