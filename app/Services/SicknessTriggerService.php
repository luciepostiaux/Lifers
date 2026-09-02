<?php

namespace App\Services;

use App\Models\Lifer;
use App\Models\LiferGameState;
use App\Models\LiferSicknessTriggerState;
use App\Models\Sickness;
use Illuminate\Support\Facades\DB;

class SicknessTriggerService
{
    public function __construct(private readonly SicknessService $sicknessService) {}

    public function processAll(): int
    {
        $contracted = 0;
        $sicknesses = Sickness::query()
            ->whereNotNull('trigger_type')
            ->where('trigger_type', '<>', 'random')
            ->with('effects')
            ->get();

        Lifer::active()
            ->whereHas('gameState', fn ($query) => $query
                ->whereNull('last_sickness_trigger_checked_on')
                ->orWhereDate('last_sickness_trigger_checked_on', '<', today()))
            ->with(['gameState', 'lifeGauge', 'sicknesses', 'subscriptions'])
            ->each(function (Lifer $lifer) use ($sicknesses, &$contracted) {
                foreach ($sicknesses as $sickness) {
                    if ($lifer->sicknesses->contains('id', $sickness->id)) {
                        continue;
                    }

                    if ($this->conditionIsActive($lifer, $sickness)) {
                        $state = LiferSicknessTriggerState::query()->firstOrCreate(
                            ['lifer_id' => $lifer->id, 'sickness_id' => $sickness->id],
                            ['started_on' => today(), 'last_checked_on' => today()],
                        );
                        $state->update(['last_checked_on' => today()]);

                        $requiredDays = $sickness->trigger_type === 'gauge_streak'
                            ? max(1, (int) $sickness->trigger_days)
                            : 1;
                        if ($state->started_on->lte(today()->subDays($requiredDays - 1))) {
                            if ($this->sicknessService->contract($lifer, $sickness)) {
                                $contracted++;
                            }
                            LiferSicknessTriggerState::query()
                                ->where('lifer_id', $lifer->id)
                                ->where('sickness_id', $sickness->id)
                                ->delete();
                        }
                    } else {
                        LiferSicknessTriggerState::query()
                            ->where('lifer_id', $lifer->id)
                            ->where('sickness_id', $sickness->id)
                            ->delete();
                    }
                }

                LiferGameState::query()->whereKey($lifer->id)->update([
                    'last_sickness_trigger_checked_on' => today(),
                ]);
            });

        return $contracted;
    }

    private function conditionIsActive(Lifer $lifer, Sickness $sickness): bool
    {
        $config = $sickness->trigger_config ?? [];

        return match ($sickness->trigger_type) {
            'days_without_sport' => $this->hasAvoidedSport($lifer, (int) $sickness->trigger_days),
            'days_without_item' => $this->hasAvoidedItem(
                $lifer,
                (string) ($config['usage_tag'] ?? ''),
                (int) $sickness->trigger_days,
            ),
            'gauge_streak' => $this->gaugesMatch($lifer, $config),
            default => false,
        };
    }

    private function hasAvoidedSport(Lifer $lifer, int $days): bool
    {
        $hasActiveSubscription = $lifer->subscriptions->contains(
            fn ($subscription) => $subscription->status === 'active' && $subscription->ends_at->isFuture(),
        );

        if ($hasActiveSubscription) {
            return false;
        }

        $lastActivity = $lifer->gameState->last_sport_activity_on ?? $lifer->created_at;

        return $lastActivity->lte(today()->subDays(max(1, $days)));
    }

    private function hasAvoidedItem(Lifer $lifer, string $usageTag, int $days): bool
    {
        if ($usageTag === '' || $lifer->created_at->gt(today()->subDays(max(1, $days)))) {
            return false;
        }

        return ! DB::table('lifer_item_usages')
            ->where('lifer_id', $lifer->id)
            ->where('usage_tag', $usageTag)
            ->where('used_at', '>=', now()->subDays(max(1, $days)))
            ->exists();
    }

    private function gaugesMatch(Lifer $lifer, array $config): bool
    {
        $conditions = collect($config['gauges'] ?? []);
        if ($conditions->isEmpty() || ! $lifer->lifeGauge) {
            return false;
        }

        $matches = $conditions->map(function (array $condition) use ($lifer) {
            $gauge = $condition['gauge'] ?? null;
            $threshold = (int) ($condition['threshold'] ?? 0);
            $value = $gauge ? $lifer->lifeGauge->{$gauge} : null;

            return $value !== null && match ($condition['operator'] ?? '<=') {
                '<=' => $value <= $threshold,
                '>=' => $value >= $threshold,
                default => false,
            };
        });

        return ($config['match'] ?? 'all') === 'any'
            ? $matches->contains(true)
            : $matches->every(fn (bool $matches) => $matches);
    }
}
