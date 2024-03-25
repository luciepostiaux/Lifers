<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'perso_id', 'type', 'name', 'start_date', 'end_date', 'status'
    ];
    protected $casts = [
        'end_date' => 'datetime',
        // incluez ici d'autres champs si nécessaire
    ];

    public function sportSession()
    {
        return $this->belongsTo(SportSession::class, 'name', 'name');
    }
    public function perso()
    {
        return $this->belongsTo(Perso::class, 'perso_id');
    }
}
