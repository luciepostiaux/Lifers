<?php

namespace Database\Seeders;

use App\Models\SportSession;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportSessionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SportSession::create(['name' => 'premium', 'type' => 'gym', 'price' => 100, 'duration' => 1, 'effect' => 20]);
        SportSession::create(['name' => 'standard', 'type' => 'gym', 'price' => 130, 'duration' => 3, 'effect' => 40]);
        SportSession::create(['name' => 'basic', 'type' => 'gym', 'price' => 200, 'duration' => 7, 'effect' => 60]);
    }
}
