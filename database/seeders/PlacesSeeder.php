<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Place;

class PlacesSeeder extends Seeder
{
    public function run()
    {
        Place::insert([
            ['name' => 'Hôpital', 'img' => 'images/places/hopital.webp'],
            ['name' => "Centre de recherche d'emploi", 'img' => 'images/places/poleemploi.webp'],
            ['name' => "Université", 'img' => 'images/places/university.webp'],

        ]);
    }
}
