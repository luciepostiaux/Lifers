<?php

namespace Database\Seeders;

use App\Models\OutfitBottom;
use Illuminate\Database\Seeder;

class OutfitBottomSeeder extends Seeder
{
    public function run()
    {
        OutfitBottom::factory()->count(10)->create();
    }
}
