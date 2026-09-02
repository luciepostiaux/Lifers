<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferSubscription extends Model
{
    protected $fillable = [
        'lifer_id',
        'sport_session_id',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function sportSession()
    {
        return $this->belongsTo(SportSession::class);
    }
}
