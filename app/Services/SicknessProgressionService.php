<?php

namespace App\Services;

use App\Models\Lifer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SicknessProgressionService
{
    public function __construct(
        private readonly SicknessService $sicknessService,
        private readonly LiferLifecycleService $lifecycle,
    ) {}

    public function processAll(): array
    {
        $result = ['effects_applied' => 0, 'deaths' => 0];

        Lifer::active()
            ->whereHas('sicknesses')
            ->with(['sicknesses.effects', 'user.roles'])
            ->pluck('id')
            ->each(function (int $liferId) use (&$result) {
                DB::transaction(function () use ($liferId, &$result) {
                    $lifer = Lifer::active()->with('user.roles')->lockForUpdate()->find($liferId);
                    if (! $lifer || ! $lifer->gameState()->exists()) {
                        return;
                    }

                    $lifer->load(['sicknesses.effects']);
                    foreach ($lifer->sicknesses as $sickness) {
                        $fatalAt = $sickness->pivot->fatal_at;
                        if ($fatalAt && now()->gte($fatalAt) && ! $lifer->isDeathProtected()) {
                            $this->lifecycle->die($lifer, $sickness->name.' non traité');
                            $result['deaths']++;

                            return;
                        }

                        if (
                            $sickness->effect_timing !== 'daily'
                            || ($sickness->pivot->expected_recovery_at && now()->gte($sickness->pivot->expected_recovery_at))
                            || ($sickness->pivot->last_effect_applied_on
                                && Carbon::parse($sickness->pivot->last_effect_applied_on)->isToday())
                        ) {
                            continue;
                        }

                        $this->sicknessService->applyEffects($lifer, $sickness);
                        DB::table('lifer_sicknesses')
                            ->where('lifer_id', $lifer->id)
                            ->where('sickness_id', $sickness->id)
                            ->update([
                                'last_effect_applied_on' => today(),
                                'updated_at' => now(),
                            ]);
                        $result['effects_applied']++;
                    }
                });
            });

        return $result;
    }
}
