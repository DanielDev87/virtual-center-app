<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    use HasFactory;

    protected $table = 'project_comments';
    protected $primaryKey = 'comment_id';
    public $timestamps = true;

    protected $fillable = [
        'tracking_id',
        'user_id',
        'comment_content',
        'comment_type',
        'is_important'
    ];

    protected $casts = [
        'is_important' => 'boolean',
    ];

    // Relaciones
    public function projectTracking()
    {
        return $this->belongsTo(ProjectTracking::class, 'tracking_id', 'tracking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}



