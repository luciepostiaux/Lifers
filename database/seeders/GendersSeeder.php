<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gender;

class GendersSeeder extends Seeder
{
    public function run()
    {
        Gender::insert([
            ['name' => 'Physique 1'],
            ['name' => 'Physique 2'],

        ]);
    }
}
