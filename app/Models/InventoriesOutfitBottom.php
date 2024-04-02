<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoriesOutfitBottom extends Model
{
    use HasFactory;

    protected $table = 'inventories_outfits_bottoms';

    protected $fillable = ['inventory_id', 'outfit_bottom_id', 'color_id'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function outfitBottom()
    {
        return $this->belongsTo(OutfitBottom::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class)->withDefault();
    }
}
