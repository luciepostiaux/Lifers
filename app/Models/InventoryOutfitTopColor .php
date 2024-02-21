<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOutfitTopColor extends Model
{
    use HasFactory;

    protected $table = 'inventories_outfits_tops_has_outfits_tops';

    protected $fillable = [
        'inventories_outfits_tops_id',
        'outfits_tops_id',
        'colors_id',
    ];

    public function inventoryOutfitBottom()
    {
        return $this->belongsTo(InventoriesOutfitShoe::class, 'inventories_outfits_tops_id', 'inv_outfits_tops_fk');
    }

    public function outfitBottom()
    {
        return $this->belongsTo(OutfitShoe::class, 'outfits_tops_id', 'outfits_tops_fk');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'colors_id', 'colors_fk');
    }
}
