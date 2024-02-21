<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersoOutfit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'perso_outfits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventories_outfits_bottoms_id',
        'inventories_outfits_tops_id',
    ];

    /**
     * Get the outfit's bottom.
     */
    public function bottom()
    {
        return $this->belongsTo(OutfitBottom::class, 'inventories_outfits_bottoms_id');
    }

    /**
     * Get the outfit's top.
     */
    public function top()
    {
        return $this->belongsTo(OutfitTop::class, 'inventories_outfits_tops_id');
    }

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
}
