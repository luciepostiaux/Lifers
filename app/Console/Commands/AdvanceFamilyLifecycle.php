<?php

namespace App\Console\Commands;

use App\Services\FamilyLifecycleService;
use Illuminate\Console\Command;

class AdvanceFamilyLifecycle extends Command
{
    protected $signature = 'advance:family-lifecycle';

    protected $description = 'Déclenche les naissances arrivées à terme et le passage des enfants à l’âge adulte';

    public function handle(FamilyLifecycleService $familyLifecycleService): int
    {
        $births = $familyLifecycleService->birthDuePregnancies();
        $adults = $familyLifecycleService->releaseAdultChildren();
        $gauges = $familyLifecycleService->decreaseChildGauges();

        $this->info("Cycle familial mis à jour : {$births} naissance(s), {$adults} passage(s) à l’âge adulte, {$gauges['updated']} besoin(s) actualisé(s), {$gauges['deaths']} décès.");

        return self::SUCCESS;
    }
}
