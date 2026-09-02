<?php

namespace App\Console\Commands;

use App\Models\Lifer;
use App\Models\LiferGameState;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DecreaseLifeGauges extends Command
{
    protected $signature = 'decrease:life-gauges';

    protected $description = 'Diminue quotidiennement les jauges de chaque Lifer actif';

    public function handle(): int
    {
        Lifer::active()
            ->whereHas('gameState', function ($query) {
                $query->whereNull('last_gauges_decreased_on')
                    ->orWhereDate('last_gauges_decreased_on', '<', today());
            })
            ->each(function (Lifer $lifer) {
                DB::transaction(function () use ($lifer) {
                    $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);

                    if ($state->last_gauges_decreased_on?->isToday()) {
                        return;
                    }

                    $gauge = $lifer->lifeGauge()->lockForUpdate()->firstOrFail();
                    $decayMultiplier = (float) ($lifer->sicknesses()
                        ->max('daily_decay_multiplier') ?: 1);
                    $gauge->update([
                        'hunger' => max(0, $gauge->hunger - (int) round(30 * $decayMultiplier)),
                        'thirst' => max(0, $gauge->thirst - (int) round(35 * $decayMultiplier)),
                        'clean' => max(0, $gauge->clean - (int) round(25 * $decayMultiplier)),
                        'happiness' => max(0, $gauge->happiness - (int) round(25 * $decayMultiplier)),
                        'entertainment' => max(0, $gauge->entertainment - (int) round(30 * $decayMultiplier)),
                        'physical_condition' => max(0, $gauge->physical_condition - (int) round(20 * $decayMultiplier)),
                        'health' => max(0, $gauge->health - (int) round(10 * $decayMultiplier)),
                    ]);
                    $state->update(['last_gauges_decreased_on' => today()]);
                });
            });

        $this->info('Les jauges de vie ont été mises à jour.');

        return self::SUCCESS;
    }
}
