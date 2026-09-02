<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyJournalAccess extends Model
{
    public const PRICE = 1;

    protected $fillable = [
        'lifer_id',
        'access_date',
        'price_paid',
        'purchased_at',
    ];

    protected $casts = [
        'access_date' => 'date',
        'price_paid' => 'integer',
        'purchased_at' => 'datetime',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }
}
