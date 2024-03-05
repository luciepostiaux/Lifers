<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoriesOutfitTop extends Model
{
    use HasFactory;

    protected $table = 'inventories_outfits_tops';

    protected $fillable = ['inventory_id', 'outfit_top_id', 'color_id'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function outfitTop()
    {
        return $this->belongsTo(OutfitTop::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class)->withDefault();
    }
}
