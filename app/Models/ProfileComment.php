<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileComment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'author_lifer_id',
        'receiver_lifer_id',
        'content',
        'status',
        'moderated_at',
    ];

    protected $casts = ['moderated_at' => 'datetime'];

    public function author()
    {
        return $this->belongsTo(Lifer::class, 'author_lifer_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Lifer::class, 'receiver_lifer_id');
    }

    public function images()
    {
        return $this->hasMany(CommentImage::class, 'comment_id');
    }
}
