<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OutfitTop;

class OutfitTopSeeder extends Seeder
{
    public function run()
    {
        OutfitTop::factory()->count(10)->create();
    }
}
