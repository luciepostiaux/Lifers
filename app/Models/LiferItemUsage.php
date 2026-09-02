<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferItemUsage extends Model
{
    protected $fillable = ['lifer_id', 'item_id', 'usage_tag', 'quantity', 'used_at'];

    protected $casts = [
        'quantity' => 'integer',
        'used_at' => 'datetime',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
