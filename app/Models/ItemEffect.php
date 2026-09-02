<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemEffect extends Model
{
    protected $fillable = ['item_id', 'gauge', 'effect'];

    protected $casts = ['effect' => 'integer'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
