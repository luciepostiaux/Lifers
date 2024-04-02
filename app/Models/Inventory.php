<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'perso_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    public function perso()
    {
        return $this->belongsTo(Perso::class, 'perso_id');
    }
    public function outfitsBottoms()
    {
        return $this->belongsToMany(OutfitBottom::class, 'inventories_outfits_bottoms', 'inventories_id', 'outfit_bottom_id');
    }
    public function outfitsTops()
    {
        return $this->belongsToMany(OutfitTop::class, 'inventories_outfits_tops', 'inventories_id', 'outfit_top_id');
    }
    public function outfitsShoes()
    {
        return $this->belongsToMany(OutfitShoe::class, 'inventories_outfits_shoes', 'inventories_id', 'outfit_shoe_id');
    }
    public function items()
    {
        return $this->belongsToMany(Item::class, 'inventories_has_items', 'inventories_id', 'items_id')
            ->withPivot('quantity');
    }
}
