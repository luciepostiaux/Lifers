<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentImage extends Model
{
    use HasFactory;
    protected $fillable = ['image_path'];

    public function comment()
    {
        return $this->belongsTo(ProfileComment::class, 'comment_id');
    }
}
