<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentImage extends Model
{
    protected $fillable = ['comment_id', 'owner_lifer_id', 'image_path'];

    public function comment()
    {
        return $this->belongsTo(ProfileComment::class);
    }

    public function owner()
    {
        return $this->belongsTo(Lifer::class, 'owner_lifer_id');
    }
}
