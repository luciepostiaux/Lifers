<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemEffect;
use App\Models\Item;

class ItemEffectsSeeder extends Seeder
{
    public function run()
    {
        $item = Item::first();

        ItemEffect::create([
            'item_id' => $item->id,
            'gauge' => 'health',
            'effect' => 10,
        ]);
    }
}
