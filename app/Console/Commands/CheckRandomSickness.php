<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Perso;
use App\Models\Sickness;
use Illuminate\Support\Facades\DB;

class CheckRandomSickness extends Command
{
    protected $signature = 'check:random-sickness';
    protected $description = 'Check each perso for random sickness based on chance and age';

    public function handle()
    {
        $personnages = Perso::with('lifeGauge')->get();
        $randomSicknesses = Sickness::where('type', 'random')->get();

        foreach ($personnages as $perso) {
            foreach ($randomSicknesses as $sickness) {
                $age = $perso->calculateAge();
                // Vérifiez si 'chance' est déjà un tableau, sinon décodez-le
                $chance = is_array($sickness->chance) ? $sickness->chance : json_decode($sickness->chance, true);
                $ageRange = $this->getAgeRange($age);
                $sicknessChance = $chance[$ageRange] ?? 0;

                if (rand(1, 100) <= $sicknessChance && $perso->lifeGauge) {

                    // Insérer la maladie pour le personnage
                    DB::table('perso_has_sickness')->insert([
                        'perso_id' => $perso->id,
                        'sickness_id' => $sickness->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->info("Perso {$perso->first_name} has contracted {$sickness->name}");

                    // Récupérer les effets de la maladie
                    $effects = DB::table('sickness_effects')
                        ->where('sickness_id', $sickness->id)
                        ->get();

                    // Appliquer les effets au personnage
                    foreach ($effects as $effect) {
                        $currentValue = $perso->lifeGauge->{$effect->gauge};
                        $newValue = max(0, $currentValue + $effect->effect); // Assurez-vous que les jauges ne descendent pas en dessous de 0.
                        $perso->lifeGauge->update([$effect->gauge => $newValue]);
                    }
                }
            }
        }
    }


    private function getAgeRange($age)
    {
        if ($age >= 80) return '80+';
        if ($age >= 60) return '60-79';
        if ($age >= 35) return '35-59';
        return '18-34';
    }
}
