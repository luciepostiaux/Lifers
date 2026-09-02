<?php

namespace App\Services;

use App\Models\Lifer;
use App\Models\LiferGameState;
use Illuminate\Support\Facades\DB;

class NaturalMortalityService
{
    public const GREEN_THRESHOLD = 61;

    public const RED_THRESHOLD = 15;

    public const MAXIMUM_AGE = 110;

    public const NEGLECT_DAYS = 7;

    public function __construct(
        private readonly GameRandomizer $randomizer,
        private readonly LiferLifecycleService $lifecycle,
    ) {}

    public function processAll(): array
    {
        $result = ['checked' => 0, 'natural_deaths' => 0, 'neglect_deaths' => 0];

        Lifer::active()->whereHas('gameState')->pluck('id')->each(function (int $liferId) use (&$result) {
            $outcome = $this->processOne($liferId);

            if ($outcome !== null) {
                $result['checked']++;
            }
            if ($outcome === 'natural') {
                $result['natural_deaths']++;
            }
            if ($outcome === 'neglect') {
                $result['neglect_deaths']++;
            }
        });

        return $result;
    }

    public function baseDailyChance(int $age): float
    {
        if ($age < 70) {
            return 0;
        }
        if ($age >= self::MAXIMUM_AGE) {
            return 100;
        }

        $x = ($age - 70) / 40;

        return max(0, min(100, 2 + (38 * $x) - (116 * ($x ** 2)) + (176 * ($x ** 3))));
    }

    private function processOne(int $liferId): ?string
    {
        return DB::transaction(function () use ($liferId) {
            $lifer = Lifer::active()->with('user.roles')->lockForUpdate()->find($liferId);
            if (! $lifer) {
                return null;
            }

            $state = LiferGameState::query()->lockForUpdate()->find($liferId);
            $gauges = $lifer->lifeGauge()->lockForUpdate()->first();
            if (! $state || ! $gauges || $state->last_mortality_checked_on?->isToday()) {
                return null;
            }

            $vitalValues = [$gauges->hunger, $gauges->thirst, $gauges->health];
            $allRed = collect($vitalValues)->every(fn (int $value) => $value <= self::RED_THRESHOLD);
            $allGreen = collect($vitalValues)->every(fn (int $value) => $value >= self::GREEN_THRESHOLD);

            $state->vital_red_since = $allRed ? ($state->vital_red_since ?: today()) : null;
            $state->vital_green_streak_days = $allGreen
                ? min(10, $state->vital_green_streak_days + 1)
                : 0;
            $state->last_mortality_checked_on = today();
            $state->save();

            if ($lifer->isDeathProtected()) {
                return 'protected';
            }

            if ($allRed && $state->vital_red_since?->lte(today()->subDays(self::NEGLECT_DAYS))) {
                $this->lifecycle->die($lifer, 'Négligence des besoins vitaux');

                return 'neglect';
            }

            $age = $lifer->calculateAge();
            $baseChance = $this->baseDailyChance($age);
            $healthProtection = min(0.5, $state->vital_green_streak_days * 0.05);
            $adjustedChance = $age >= self::MAXIMUM_AGE
                ? 100
                : $baseChance * (1 - $healthProtection);

            if ($this->randomizer->succeeds($adjustedChance)) {
                $this->lifecycle->die($lifer, 'Mort naturelle liée à l’âge');

                return 'natural';
            }

            return 'alive';
        });
    }
}
