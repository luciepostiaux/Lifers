<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rewind extends Model
{
    use HasFactory;

    protected $table = 'rewinds';

    protected $fillable = [
        'price',
        'img',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'rewinds_has_users', 'rewind_id', 'user_id')->withTimestamps();
    }
}
