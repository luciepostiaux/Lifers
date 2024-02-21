<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOutfitShoesColor extends Model
{
    use HasFactory;

    protected $table = 'inventories_outfits_shoes_has_outfits_shoes';

    protected $fillable = [
        'inventories_outfits_shoes_id',
        'outfits_shoes_id',
        'colors_id',
    ];

    public function inventoryOutfitBottom()
    {
        return $this->belongsTo(InventoriesOutfitShoe::class, 'inventories_outfits_shoes_id', 'inv_outfits_shoes_fk');
    }

    public function outfitBottom()
    {
        return $this->belongsTo(OutfitShoe::class, 'outfits_shoes_id', 'outfits_shoes_fk');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'colors_id', 'colors_fk');
    }
}
