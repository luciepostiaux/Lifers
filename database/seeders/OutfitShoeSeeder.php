<?php

namespace Database\Seeders;

use App\Models\OutfitShoe;
use Illuminate\Database\Seeder;

class OutfitShoeSeeder extends Seeder
{
    public function run()
    {
        OutfitShoe::factory()->count(10)->create();
    }
}
