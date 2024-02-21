<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOutfitBottomOutfitBottom extends Model
{
    use HasFactory;

    protected $table = 'inventories_outfits_bottoms_has_outfits_bottoms';

    protected $fillable = [
        'inventories_outfits_bottoms_id',
        'outfits_bottoms_id',
        'colors_id',
    ];

    public function inventoryOutfitBottom()
    {
        return $this->belongsTo(InventoriesOutfitBottom::class, 'inventories_outfits_bottoms_id', 'inv_outfits_bottoms_fk');
    }

    public function outfitBottom()
    {
        return $this->belongsTo(OutfitBottom::class, 'outfits_bottoms_id', 'outfits_bottoms_fk');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'colors_id', 'colors_fk');
    }
}
