<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rewind;

class RewindsSeeder extends Seeder
{
    public function run()
    {
        $rewinds = [
            ['price' => 25.00, 'img' => NULL],
            ['price' => 50.00, 'img' => NULL],
            ['price' => 100.00, 'img' => NULL],
            ['price' => 200.00, 'img' => NULL],
            ['price' => 300.00, 'img' => NULL],
            ['price' => 400.00, 'img' => NULL],
            ['price' => 500.00, 'img' => NULL],
        ];

        foreach ($rewinds as $rewind) {
            Rewind::create($rewind);
        }
    }
}
