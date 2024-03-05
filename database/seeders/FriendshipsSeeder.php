<?php

namespace Database\Seeders;

use App\Models\Perso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FriendshipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perso1 = Perso::find(1);
        $perso2 = Perso::find(2);

        if ($perso1 && $perso2) {
            $perso1->friends()->attach($perso2->id);
            $perso2->friends()->attach($perso1->id);
        }
    }
}
