<?php

namespace App\Console\Commands;

use App\Services\NaturalMortalityService;
use App\Services\SicknessProgressionService;
use App\Services\SicknessTriggerService;
use Illuminate\Console\Command;

class ProcessDailyLifers extends Command
{
    protected $signature = 'lifers:daily-tick';

    protected $description = 'Exécute dans un ordre déterministe le cycle quotidien des Lifers';

    public function handle(
        SicknessProgressionService $progression,
        SicknessTriggerService $triggers,
        NaturalMortalityService $mortality,
    ): int {
        foreach ([
            'increase:daily-salary',
            'decrease:life-gauges',
            'update:life-gauges-from-subscriptions',
        ] as $command) {
            if ($this->call($command) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        $progressionResult = $progression->processAll();

        if ($this->call('resolve:expired-sicknesses') !== self::SUCCESS) {
            return self::FAILURE;
        }

        $triggeredSicknesses = $triggers->processAll();

        if ($this->call('check:random-sickness') !== self::SUCCESS) {
            return self::FAILURE;
        }

        $mortalityResult = $mortality->processAll();

        $this->components->info(sprintf(
            'Cycle terminé : %d effet(s), %d maladie(s) de négligence, %d décès par maladie, %d décès naturels et %d décès par négligence.',
            $progressionResult['effects_applied'],
            $triggeredSicknesses,
            $progressionResult['deaths'],
            $mortalityResult['natural_deaths'],
            $mortalityResult['neglect_deaths'],
        ));

        return self::SUCCESS;
    }
}
