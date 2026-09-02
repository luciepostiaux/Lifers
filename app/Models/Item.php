<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public const CATEGORY_FOOD = 'Nourriture';

    public const CATEGORY_DRINK = 'Boissons';

    public const CATEGORY_HYGIENE_AND_WELLBEING = 'Hygiène et bien-être';

    public const CATEGORY_TOBACCO_AND_ALCOHOL = 'Tabac et alcool';

    public const CATEGORY_FAMILY_PROTECTION = self::CATEGORY_HYGIENE_AND_WELLBEING;

    public const FAMILY_PROTECTION_NAME = 'Boîte de préservatifs';

    public const CATEGORY_ORDER = [
        self::CATEGORY_FOOD,
        self::CATEGORY_DRINK,
        self::CATEGORY_HYGIENE_AND_WELLBEING,
        self::CATEGORY_TOBACCO_AND_ALCOHOL,
    ];

    protected $fillable = [
        'name',
        'price',
        'units_per_purchase',
        'description',
        'image_path',
        'background_image_path',
        'category',
        'usage_tag',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'units_per_purchase' => 'integer',
    ];

    public static function categoryRank(string $category): int
    {
        $rank = array_search($category, self::CATEGORY_ORDER, true);

        return $rank === false ? count(self::CATEGORY_ORDER) : $rank;
    }

    public function inventories()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_items', 'item_id', 'inventory_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function effects()
    {
        return $this->hasMany(ItemEffect::class);
    }

    public function usages()
    {
        return $this->hasMany(LiferItemUsage::class);
    }
}
