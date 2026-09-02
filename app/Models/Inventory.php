<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $primaryKey = 'lifer_id';

    public $incrementing = false;

    protected $fillable = ['lifer_id'];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'inventory_items', 'inventory_id', 'item_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function wearables()
    {
        return $this->hasMany(InventoryWearable::class, 'inventory_id');
    }
}
