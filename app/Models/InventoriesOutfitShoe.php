<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoriesOutfitShoe extends Model
{
    use HasFactory;

    protected $table = 'inventories_outfits_shoes';

    protected $fillable = ['inventory_id', 'outfit_shoe_id', 'color_id'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function outfitShoe()
    {
        return $this->belongsTo(OutfitShoe::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class)->withDefault();
    }
}
