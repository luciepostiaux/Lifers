<?php

namespace App\Services;

use App\Models\Lifer;
use App\Models\Sickness;
use Illuminate\Support\Facades\DB;

class SicknessRiskCalculator
{
    public function dailyChance(Lifer $lifer, Sickness $sickness): float
    {
        $baseChance = (float) ($sickness->chance_by_age[$this->ageRange($lifer->calculateAge())] ?? 0);
        $riskConfig = $sickness->risk_config ?? [];
        $multiplier = 1.0;

        foreach ($riskConfig['exposures'] ?? [] as $exposure) {
            $tag = $exposure['usage_tag'] ?? null;
            $windowDays = max(1, (int) ($exposure['window_days'] ?? 14));
            $usesForDoubleRisk = max(1, (int) ($exposure['uses_for_double_risk'] ?? 10));

            if (! $tag) {
                continue;
            }

            $uses = (int) DB::table('lifer_item_usages')
                ->where('lifer_id', $lifer->id)
                ->where('usage_tag', $tag)
                ->where('used_at', '>=', now()->subDays($windowDays))
                ->sum('quantity');

            $multiplier += $uses / $usesForDoubleRisk;
        }

        $maximumMultiplier = max(1, (float) ($riskConfig['maximum_multiplier'] ?? 1));

        return min(100, $baseChance * min($multiplier, $maximumMultiplier));
    }

    private function ageRange(int $age): string
    {
        return match (true) {
            $age >= 80 => '80+',
            $age >= 60 => '60-79',
            $age >= 35 => '35-59',
            default => '18-34',
        };
    }
}
