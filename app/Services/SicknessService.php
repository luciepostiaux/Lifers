<?php

namespace App\Services;

use App\Models\Lifer;
use App\Models\Sickness;
use Illuminate\Support\Facades\DB;

class SicknessService
{
    private const GAUGES = [
        'hunger',
        'thirst',
        'clean',
        'happiness',
        'entertainment',
        'physical_condition',
        'health',
    ];

    public function contract(Lifer $lifer, Sickness $sickness): bool
    {
        return DB::transaction(function () use ($lifer, $sickness) {
            if (! $lifer->gameState()->lockForUpdate()->exists()) {
                return false;
            }

            $alreadyContracted = DB::table('lifer_sicknesses')
                ->where('lifer_id', $lifer->id)
                ->where('sickness_id', $sickness->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyContracted) {
                return false;
            }

            $contractedAt = now();
            DB::table('lifer_sicknesses')->insert([
                'lifer_id' => $lifer->id,
                'sickness_id' => $sickness->id,
                'contracted_at' => $contractedAt,
                'expected_recovery_at' => $sickness->duration_days
                    ? $contractedAt->copy()->addDays($sickness->duration_days)
                    : null,
                'last_effect_applied_on' => today(),
                'fatal_at' => $sickness->fatal_after_days
                    ? $contractedAt->copy()->addDays($sickness->fatal_after_days)
                    : null,
                'created_at' => $contractedAt,
                'updated_at' => $contractedAt,
            ]);

            $this->applyEffects($lifer, $sickness);

            return true;
        });
    }

    public function applyEffects(Lifer $lifer, Sickness $sickness): void
    {
        $sickness->loadMissing('effects');
        $gauge = $lifer->lifeGauge()->lockForUpdate()->firstOrFail();

        foreach ($sickness->effects as $effect) {
            if (! in_array($effect->gauge, self::GAUGES, true)) {
                continue;
            }

            $gauge->{$effect->gauge} = max(
                0,
                min(100, $gauge->{$effect->gauge} + $effect->effect),
            );
        }

        $gauge->save();
    }
}
