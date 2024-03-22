<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'perso_id', 'type', 'start_date', 'end_date', 'status'
    ];

    public function perso()
    {
        return $this->belongsTo(Perso::class);
    }
}
