<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryWearable extends Model
{
    protected $fillable = ['inventory_id', 'wearable_id', 'color_id'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'lifer_id');
    }

    public function wearable()
    {
        return $this->belongsTo(Wearable::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class)->withDefault();
    }
}
