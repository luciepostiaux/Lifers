<?php

namespace App\Services;

use App\Models\FamilyChild;
use App\Models\FamilyChildGauge;
use App\Models\FamilyPregnancy;
use App\Models\FamilyRequest;
use App\Models\GivenName;
use App\Models\Lifer;
use App\Models\LiferGameState;
use App\Models\LiferMarriage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FamilyLifecycleService
{
    public const CHILD_GAUGE_DECREASE = 10;

    public const CHILD_RED_THRESHOLD = 15;

    public const ADULT_AFTER_DAYS = 54;

    public const CARE_FEED = 'feed';

    public const CARE_WASH = 'wash';

    public const CARE_CUDDLE = 'cuddle';

    public const FEED_COST = 5;

    public const WASH_COST = 5;

    public const CARE_GAIN = 20;

    public const ABANDONMENT_COST = 100;

    public const ADOPTION_LIMIT = 3;

    public const ADOPTION_PERIOD_DAYS = 3;

    public function nameExpectedChild(
        Lifer $actor,
        FamilyPregnancy $pregnancy,
        FamilyChild $child,
        string $firstName,
        string $lastName,
    ): FamilyChild {
        return DB::transaction(function () use ($actor, $pregnancy, $child, $firstName, $lastName) {
            $lockedPregnancy = FamilyPregnancy::query()
                ->with(['mother:id,last_name', 'father:id,last_name'])
                ->lockForUpdate()
                ->findOrFail($pregnancy->id);
            $lockedChild = FamilyChild::query()->lockForUpdate()->findOrFail($child->id);

            if (
                $lockedPregnancy->status !== FamilyPregnancy::STATUS_ACTIVE
                || $lockedChild->pregnancy_id !== $lockedPregnancy->id
                || $lockedChild->status !== FamilyChild::STATUS_EXPECTED
            ) {
                throw ValidationException::withMessages(['child' => 'Cet enfant ne peut plus être renommé avant sa naissance.']);
            }

            $hasCustody = DB::table('family_child_guardians')
                ->where('child_id', $lockedChild->id)
                ->where('lifer_id', $actor->id)
                ->where('has_custody', true)
                ->exists();

            if (! $hasCustody) {
                abort(403);
            }

            $allowedLastNames = collect([
                $lockedPregnancy->mother?->last_name,
                $lockedPregnancy->father?->last_name,
            ])->filter()->unique()->values();

            if (! $allowedLastNames->contains($lastName)) {
                throw ValidationException::withMessages(['last_name' => 'Le nom doit être celui de l’un des deux parents.']);
            }

            $lockedChild->update([
                'first_name' => trim($firstName),
                'last_name' => $lastName,
            ]);

            return $lockedChild->fresh();
        });
    }

    public function birthDuePregnancies(): int
    {
        $births = 0;

        FamilyPregnancy::query()
            ->where('status', FamilyPregnancy::STATUS_ACTIVE)
            ->where('due_at', '<=', now())
            ->pluck('id')
            ->each(function (int $pregnancyId) use (&$births) {
                $births += DB::transaction(function () use ($pregnancyId) {
                    $pregnancy = FamilyPregnancy::query()
                        ->with(['mother:id,last_name', 'father:id,last_name'])
                        ->lockForUpdate()
                        ->findOrFail($pregnancyId);

                    if ($pregnancy->status !== FamilyPregnancy::STATUS_ACTIVE || $pregnancy->due_at->isFuture()) {
                        return 0;
                    }

                    $children = FamilyChild::query()
                        ->where('pregnancy_id', $pregnancy->id)
                        ->orderBy('birth_order')
                        ->lockForUpdate()
                        ->get();
                    $defaultLastName = $this->defaultLastName($pregnancy);

                    foreach ($children as $child) {
                        if ($child->status !== FamilyChild::STATUS_EXPECTED) {
                            continue;
                        }

                        $firstName = $child->first_name ?: GivenName::query()
                            ->where('sex', $child->sex)
                            ->where('is_active', true)
                            ->inRandomOrder()
                            ->value('name');

                        if (! $firstName) {
                            throw new RuntimeException("Aucun prénom actif n'est disponible pour une naissance.");
                        }

                        $bornAt = $pregnancy->due_at->copy();
                        $child->update([
                            'first_name' => $firstName,
                            'last_name' => $child->last_name ?: $defaultLastName,
                            'status' => FamilyChild::STATUS_DEPENDENT,
                            'born_at' => $bornAt,
                            'adult_at' => $bornAt->copy()->addDays(self::ADULT_AFTER_DAYS),
                        ]);
                        FamilyChildGauge::query()->create([
                            'child_id' => $child->id,
                            'hunger' => 100,
                            'hygiene' => 100,
                            'affection' => 100,
                            'last_decreased_on' => today(),
                        ]);
                    }

                    $pregnancy->update([
                        'status' => FamilyPregnancy::STATUS_COMPLETED,
                        'completed_at' => now(),
                    ]);

                    return $children->count();
                });
            });

        return $births;
    }

    public function releaseAdultChildren(): int
    {
        $released = 0;

        FamilyChild::query()
            ->whereIn('status', [FamilyChild::STATUS_DEPENDENT, FamilyChild::STATUS_ORPHANED])
            ->where('adult_at', '<=', now())
            ->pluck('id')
            ->each(function (int $childId) use (&$released) {
                $released += DB::transaction(function () use ($childId) {
                    $child = FamilyChild::query()->lockForUpdate()->findOrFail($childId);

                    if (
                        ! in_array($child->status, [FamilyChild::STATUS_DEPENDENT, FamilyChild::STATUS_ORPHANED], true)
                        || ! $child->adult_at
                        || $child->adult_at->isFuture()
                    ) {
                        return 0;
                    }

                    DB::table('family_child_guardians')
                        ->where('child_id', $child->id)
                        ->update(['has_custody' => false, 'updated_at' => now()]);
                    $child->gauges()->delete();
                    $child->update(['status' => FamilyChild::STATUS_AVAILABLE]);

                    return 1;
                });
            });

        return $released;
    }

    public function careForChild(Lifer $actor, FamilyChild $child, string $care): FamilyChildGauge
    {
        $careRules = [
            self::CARE_FEED => ['gauge' => 'hunger', 'cost' => self::FEED_COST],
            self::CARE_WASH => ['gauge' => 'hygiene', 'cost' => self::WASH_COST],
            self::CARE_CUDDLE => ['gauge' => 'affection', 'cost' => 0],
        ];

        if (! isset($careRules[$care])) {
            throw ValidationException::withMessages(['care' => 'Ce soin est invalide.']);
        }

        return DB::transaction(function () use ($actor, $child, $care, $careRules) {
            $lockedChild = FamilyChild::query()->lockForUpdate()->findOrFail($child->id);
            $this->assertCanCareForChild($actor, $lockedChild);
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($actor->id);
            $gauge = FamilyChildGauge::query()->lockForUpdate()->findOrFail($lockedChild->id);
            $rule = $careRules[$care];

            if ($state->money < $rule['cost']) {
                throw ValidationException::withMessages(['care' => 'Ton Lifer n’a pas assez de Lif’coins pour ce soin.']);
            }

            if ($rule['cost'] > 0) {
                $state->decrement('money', $rule['cost']);
            }

            $gauge->{$rule['gauge']} = min(100, $gauge->{$rule['gauge']} + self::CARE_GAIN);
            $gauge->red_since = min($gauge->hunger, $gauge->hygiene, $gauge->affection) <= self::CHILD_RED_THRESHOLD
                ? ($gauge->red_since ?: today())
                : null;
            $gauge->save();

            return $gauge->fresh();
        });
    }

    public function careForAllChildren(Lifer $actor): int
    {
        return DB::transaction(function () use ($actor) {
            $childIds = DB::table('family_child_guardians')
                ->join('family_children', 'family_children.id', '=', 'family_child_guardians.child_id')
                ->whereIn('family_child_guardians.lifer_id', $this->caregiverLiferIds($actor))
                ->where('family_child_guardians.has_custody', true)
                ->whereIn('family_children.status', [FamilyChild::STATUS_DEPENDENT, FamilyChild::STATUS_ORPHANED])
                ->orderBy('family_children.id')
                ->pluck('family_children.id');

            if ($childIds->isEmpty()) {
                throw ValidationException::withMessages(['care' => 'Aucun enfant de ton foyer n’a besoin de soins.']);
            }

            FamilyChild::query()->whereKey($childIds)->orderBy('id')->lockForUpdate()->get();
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($actor->id);
            $gauges = FamilyChildGauge::query()->whereKey($childIds)->orderBy('child_id')->lockForUpdate()->get();
            $gaugesNeedingCare = $gauges->filter(fn (FamilyChildGauge $gauge) => $gauge->hunger < 100
                || $gauge->hygiene < 100
                || $gauge->affection < 100);

            if ($gaugesNeedingCare->isEmpty()) {
                throw ValidationException::withMessages(['care' => 'Tous les besoins des enfants sont déjà comblés.']);
            }

            $totalCost = $gaugesNeedingCare->count() * (self::FEED_COST + self::WASH_COST);

            if ($state->money < $totalCost) {
                throw ValidationException::withMessages([
                    'care' => "Il faut {$totalCost} Lif’coins pour s’occuper des enfants qui en ont besoin.",
                ]);
            }

            $state->decrement('money', $totalCost);

            foreach ($gaugesNeedingCare as $gauge) {
                $gauge->hunger = min(100, $gauge->hunger + self::CARE_GAIN);
                $gauge->hygiene = min(100, $gauge->hygiene + self::CARE_GAIN);
                $gauge->affection = min(100, $gauge->affection + self::CARE_GAIN);
                $gauge->red_since = min($gauge->hunger, $gauge->hygiene, $gauge->affection) <= self::CHILD_RED_THRESHOLD
                    ? ($gauge->red_since ?: today())
                    : null;
                $gauge->save();
            }

            return $gaugesNeedingCare->count();
        });
    }

    public function decreaseChildGauges(): array
    {
        $result = ['updated' => 0, 'deaths' => 0];

        FamilyChild::query()
            ->whereIn('status', [FamilyChild::STATUS_DEPENDENT, FamilyChild::STATUS_ORPHANED])
            ->whereHas('gauges', function ($query) {
                $query->whereNull('last_decreased_on')
                    ->orWhereDate('last_decreased_on', '<', today());
            })
            ->pluck('id')
            ->each(function (int $childId) use (&$result) {
                DB::transaction(function () use ($childId, &$result) {
                    $child = FamilyChild::query()->lockForUpdate()->findOrFail($childId);
                    $gauge = FamilyChildGauge::query()->lockForUpdate()->find($childId);

                    if (
                        ! $gauge
                        || $gauge->last_decreased_on?->isToday()
                        || ! in_array($child->status, [FamilyChild::STATUS_DEPENDENT, FamilyChild::STATUS_ORPHANED], true)
                    ) {
                        return;
                    }

                    $gauge->hunger = max(0, $gauge->hunger - self::CHILD_GAUGE_DECREASE);
                    $gauge->hygiene = max(0, $gauge->hygiene - self::CHILD_GAUGE_DECREASE);
                    $gauge->affection = max(0, $gauge->affection - self::CHILD_GAUGE_DECREASE);
                    $gauge->last_decreased_on = today();
                    $isRed = min($gauge->hunger, $gauge->hygiene, $gauge->affection) <= self::CHILD_RED_THRESHOLD;

                    if (! $isRed) {
                        $gauge->red_since = null;
                    } elseif (! $gauge->red_since) {
                        $gauge->red_since = today();
                    }

                    if ($isRed && $gauge->red_since?->lte(today()->subDays(3))) {
                        DB::table('family_child_guardians')
                            ->where('child_id', $child->id)
                            ->update(['has_custody' => false, 'updated_at' => now()]);
                        $gauge->delete();
                        $child->update([
                            'status' => FamilyChild::STATUS_DEAD,
                            'died_at' => now(),
                            'death_cause' => 'Négligence des besoins essentiels',
                        ]);
                        $result['deaths']++;

                        return;
                    }

                    $gauge->save();
                    $result['updated']++;
                });
            });

        return $result;
    }

    public function renounceChild(Lifer $actor, FamilyChild $child): void
    {
        DB::transaction(function () use ($actor, $child) {
            $lockedChild = FamilyChild::query()->lockForUpdate()->findOrFail($child->id);
            $guardian = DB::table('family_child_guardians')
                ->where('child_id', $lockedChild->id)
                ->where('lifer_id', $actor->id)
                ->where('has_custody', true)
                ->lockForUpdate()
                ->first();

            if (! $guardian) {
                abort(403);
            }

            $otherCustodianExists = DB::table('family_child_guardians')
                ->where('child_id', $lockedChild->id)
                ->where('lifer_id', '!=', $actor->id)
                ->where('has_custody', true)
                ->exists();

            if (! $otherCustodianExists) {
                throw ValidationException::withMessages([
                    'child' => 'Tu es le seul responsable de cet enfant. Utilise l’abandon pour le confier à l’orphelinat.',
                ]);
            }

            DB::table('family_child_guardians')
                ->where('child_id', $lockedChild->id)
                ->where('lifer_id', $actor->id)
                ->update([
                    'has_custody' => false,
                    'renounced_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    public function requestOrAbandonChild(Lifer $actor, FamilyChild $child): ?FamilyRequest
    {
        return DB::transaction(function () use ($actor, $child) {
            $lockedChild = FamilyChild::query()->lockForUpdate()->findOrFail($child->id);
            $custodianIds = DB::table('family_child_guardians')
                ->where('child_id', $lockedChild->id)
                ->where('has_custody', true)
                ->orderBy('lifer_id')
                ->lockForUpdate()
                ->pluck('lifer_id');

            if (! $custodianIds->contains($actor->id)) {
                abort(403);
            }

            if (! in_array($lockedChild->status, [FamilyChild::STATUS_DEPENDENT, FamilyChild::STATUS_ORPHANED], true)) {
                throw ValidationException::withMessages(['child' => 'Cet enfant ne peut pas être confié à l’orphelinat.']);
            }

            if ($custodianIds->count() === 1) {
                $state = LiferGameState::query()->lockForUpdate()->findOrFail($actor->id);
                $this->chargeAbandonment($state, self::ABANDONMENT_COST);
                $this->moveChildToOrphanage($lockedChild);

                return null;
            }

            if ($custodianIds->count() !== 2) {
                throw ValidationException::withMessages(['child' => 'La garde de cet enfant doit être clarifiée avant son abandon.']);
            }

            $otherCustodianId = $custodianIds->first(fn (int $id) => $id !== $actor->id);
            $duplicate = FamilyRequest::query()
                ->where('child_id', $lockedChild->id)
                ->where('type', FamilyRequest::TYPE_CHILD_ABANDONMENT)
                ->where('status', FamilyRequest::STATUS_PENDING)
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages(['child' => 'Une demande d’abandon est déjà en attente pour cet enfant.']);
            }

            return FamilyRequest::query()->create([
                'requester_lifer_id' => $actor->id,
                'recipient_lifer_id' => $otherCustodianId,
                'child_id' => $lockedChild->id,
                'type' => FamilyRequest::TYPE_CHILD_ABANDONMENT,
                'status' => FamilyRequest::STATUS_PENDING,
            ]);
        });
    }

    public function acceptChildAbandonment(FamilyRequest $request, Lifer $first, Lifer $second): void
    {
        if ($request->type !== FamilyRequest::TYPE_CHILD_ABANDONMENT || ! $request->child_id) {
            throw ValidationException::withMessages(['request' => 'Cette demande d’abandon est invalide.']);
        }

        $child = FamilyChild::query()->lockForUpdate()->findOrFail($request->child_id);
        $custodianIds = DB::table('family_child_guardians')
            ->where('child_id', $child->id)
            ->where('has_custody', true)
            ->orderBy('lifer_id')
            ->lockForUpdate()
            ->pluck('lifer_id');

        if ($custodianIds->sort()->values()->all() !== collect([$first->id, $second->id])->sort()->values()->all()) {
            throw ValidationException::withMessages(['request' => 'La garde de cet enfant a changé depuis la demande.']);
        }

        $states = LiferGameState::query()
            ->whereIn('lifer_id', [$first->id, $second->id])
            ->orderBy('lifer_id')
            ->lockForUpdate()
            ->get();

        foreach ($states as $state) {
            $this->chargeAbandonment($state, intdiv(self::ABANDONMENT_COST, 2));
        }

        $this->moveChildToOrphanage($child);
    }

    public function adoptChild(Lifer $actor, FamilyChild $child): array
    {
        return DB::transaction(function () use ($actor, $child) {
            $lockedChild = FamilyChild::query()->lockForUpdate()->findOrFail($child->id);

            if ($lockedChild->status !== FamilyChild::STATUS_ORPHANED || ! $lockedChild->born_at || $lockedChild->calculateAge() >= 18) {
                throw ValidationException::withMessages(['child' => 'Cet enfant n’est pas disponible à l’adoption.']);
            }

            $adopters = collect([$actor]);
            $marriage = $actor->activeMarriage();

            if ($marriage) {
                $marriage->loadMissing(['firstLifer', 'secondLifer']);
                $spouse = $marriage->spouseOf($actor);

                if ($spouse?->status === Lifer::STATUS_ACTIVE && $spouse->gameState()->exists()) {
                    $adopters->push($spouse);
                }
            }

            $adopters = $adopters->unique('id')->sortBy('id')->values();
            Lifer::query()->whereKey($adopters->pluck('id'))->orderBy('id')->lockForUpdate()->get();

            foreach ($adopters as $adopter) {
                $recentAdoptions = DB::table('family_child_guardians')
                    ->where('lifer_id', $adopter->id)
                    ->whereNotNull('adopted_at')
                    ->where('adopted_at', '>=', now()->subDays(self::ADOPTION_PERIOD_DAYS))
                    ->count();

                if ($recentAdoptions >= self::ADOPTION_LIMIT) {
                    throw ValidationException::withMessages([
                        'child' => 'Un des Lifers du foyer a déjà adopté trois enfants pendant cette année de jeu.',
                    ]);
                }
            }

            foreach ($adopters as $adopter) {
                $existingType = DB::table('family_child_guardians')
                    ->where('child_id', $lockedChild->id)
                    ->where('lifer_id', $adopter->id)
                    ->value('type');
                DB::table('family_child_guardians')->updateOrInsert(
                    ['child_id' => $lockedChild->id, 'lifer_id' => $adopter->id],
                    [
                        'type' => $existingType ?: 'adoptive',
                        'has_custody' => true,
                        'adopted_at' => now(),
                        'renounced_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }

            $lockedChild->update(['status' => FamilyChild::STATUS_DEPENDENT]);

            return $adopters->all();
        });
    }

    private function defaultLastName(FamilyPregnancy $pregnancy): string
    {
        if (! $pregnancy->mother?->last_name || ! $pregnancy->father?->last_name) {
            throw new RuntimeException("Le nom d'un parent est indisponible pour la naissance.");
        }

        $parentsAreMarried = LiferMarriage::query()
            ->where('lower_lifer_id', min($pregnancy->mother_lifer_id, $pregnancy->father_lifer_id))
            ->where('higher_lifer_id', max($pregnancy->mother_lifer_id, $pregnancy->father_lifer_id))
            ->where('status', LiferMarriage::STATUS_ACTIVE)
            ->exists();

        return $parentsAreMarried
            ? $pregnancy->father->last_name
            : $pregnancy->mother->last_name;
    }

    private function assertCanCareForChild(Lifer $actor, FamilyChild $child): void
    {
        if (! in_array($child->status, [FamilyChild::STATUS_DEPENDENT, FamilyChild::STATUS_ORPHANED], true)) {
            throw ValidationException::withMessages(['care' => 'Cet enfant ne peut pas recevoir ce soin.']);
        }

        $hasCustody = DB::table('family_child_guardians')
            ->where('child_id', $child->id)
            ->whereIn('lifer_id', $this->caregiverLiferIds($actor))
            ->where('has_custody', true)
            ->exists();

        if (! $hasCustody) {
            abort(403);
        }
    }

    private function caregiverLiferIds(Lifer $actor): array
    {
        $ids = [$actor->id];
        $marriage = $actor->activeMarriage();

        if ($marriage) {
            $marriage->loadMissing(['firstLifer', 'secondLifer']);
            $spouse = $marriage->spouseOf($actor);

            if ($spouse?->status === Lifer::STATUS_ACTIVE) {
                $ids[] = $spouse->id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function chargeAbandonment(LiferGameState $state, int $cost): void
    {
        if ($state->money < $cost) {
            throw ValidationException::withMessages([
                'child' => "Il faut {$cost} Lif’coins pour confier cet enfant à l’orphelinat.",
            ]);
        }

        $state->decrement('money', $cost);
    }

    private function moveChildToOrphanage(FamilyChild $child): void
    {
        DB::table('family_child_guardians')
            ->where('child_id', $child->id)
            ->update(['has_custody' => false, 'updated_at' => now()]);
        $child->update(['status' => FamilyChild::STATUS_ORPHANED]);
    }
}
