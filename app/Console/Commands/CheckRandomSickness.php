<?php

namespace App\Console\Commands;

use App\Models\Lifer;
use App\Models\LiferGameState;
use App\Models\Sickness;
use App\Services\GameRandomizer;
use App\Services\SicknessRiskCalculator;
use App\Services\SicknessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckRandomSickness extends Command
{
    protected $signature = 'check:random-sickness';

    protected $description = 'Évalue les maladies aléatoires pour chaque Lifer actif';

    public function handle(
        GameRandomizer $randomizer,
        SicknessRiskCalculator $riskCalculator,
        SicknessService $sicknessService,
    ): int {
        $sicknesses = Sickness::query()
            ->where(function ($query) {
                $query->where('trigger_type', 'random')
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('trigger_type')->where('type', 'random');
                    });
            })
            ->with('effects')
            ->get();

        Lifer::active()
            ->whereHas('gameState', function ($query) {
                $query->whereNull('last_sickness_checked_on')
                    ->orWhereDate('last_sickness_checked_on', '<', today());
            })
            ->each(function (Lifer $lifer) use ($sicknesses, $randomizer, $riskCalculator, $sicknessService) {
                DB::transaction(function () use ($lifer, $sicknesses, $randomizer, $riskCalculator, $sicknessService) {
                    $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);

                    if ($state->last_sickness_checked_on?->isToday()) {
                        return;
                    }

                    $lifer->load('sicknesses');
                    foreach ($sicknesses as $sickness) {
                        if ($lifer->sicknesses->contains($sickness)) {
                            continue;
                        }

                        $chance = $riskCalculator->dailyChance($lifer, $sickness);

                        if ($chance <= 0 || ! $randomizer->succeeds($chance)) {
                            continue;
                        }

                        $sicknessService->contract($lifer, $sickness);
                    }

                    $state->update(['last_sickness_checked_on' => today()]);
                });
            });

        return self::SUCCESS;
    }
}
