<?php

namespace App\Console\Commands;

use App\Models\Perso;
use Illuminate\Console\Command;

class DecreaseLifeGauges extends Command
{
    protected $signature = 'decrease:life-gauges';
    protected $description = 'Diminue quotidiennement les jauges de vie de chaque personnage';

    public function handle()
    {
        $personnages = Perso::with('lifeGauge')->get();
        foreach ($personnages as $personnage) {
            if ($personnage->lifeGauge) {
                // Utilisez la méthode max pour s'assurer que les jauges ne tombent pas en dessous de 0
                $personnage->lifeGauge->update([
                    'hunger' => max(0, $personnage->lifeGauge->hunger - 30),
                    'thirst' => max(0, $personnage->lifeGauge->thirst - 35),
                    'clean' => max(0, $personnage->lifeGauge->clean - 25),
                    'happiness' => max(0, $personnage->lifeGauge->happiness - 25),
                    'entertainment' => max(0, $personnage->lifeGauge->entertainment - 30),
                    'physical_condition' => max(0, $personnage->lifeGauge->physical_condition - 20),
                    'health' => max(0, $personnage->lifeGauge->health - 10),
                ]);
            }
        }

        $this->info('Les jauges de vie ont été mises à jour.');
    }
}
