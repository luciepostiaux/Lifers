<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class InventoryItem extends Pivot
{
    protected $table = 'inventory_items';

    public $incrementing = false;

    protected $fillable = ['inventory_id', 'item_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];
}
