<?php

namespace App\Console\Commands;

use App\Models\Perso;
use Illuminate\Console\Command;

class IncreaseDailySalary extends Command
{
    protected $signature = 'increase:daily-salary';

    protected $description = 'Augmente quotidiennement le money de chaque personnage basé sur son salaire';

    public function handle()
    {
        $personnages = Perso::all();
        foreach ($personnages as $personnage) {
            if (is_numeric($personnage->salary)) {
                // Assurez-vous que salary est un nombre avant de l'incrémenter
                $personnage->increment('money', $personnage->salary);
            }
        }

        $this->info('Les salaires quotidiens ont été distribués.');
    }
}
