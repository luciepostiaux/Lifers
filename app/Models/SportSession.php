<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportSession extends Model
{
    use HasFactory;

    protected $table = 'sport_sessions';

    protected $fillable = [
        'name', 'type', 'price', 'duration', 'effect'
    ];
}
