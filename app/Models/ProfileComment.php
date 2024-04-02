<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileComment extends Model
{
    use HasFactory;

    public function author()
    {
        return $this->belongsTo(Perso::class, 'author_perso_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Perso::class, 'receiver_perso_id');
    }

    public function images()
    {
        return $this->hasMany(CommentImage::class, 'comment_id');
    }

}
