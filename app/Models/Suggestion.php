<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    use HasFactory;

    protected $table = 'suggestions';

    protected $fillable = [
        'user_id',
        'content',
        'status',
    ];

    protected $casts = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
