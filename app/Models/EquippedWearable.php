<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquippedWearable extends Model
{
    protected $fillable = ['lifer_id', 'slot', 'inventory_wearable_id'];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function inventoryWearable()
    {
        return $this->belongsTo(InventoryWearable::class);
    }
}
