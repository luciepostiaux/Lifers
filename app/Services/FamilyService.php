<?php

namespace App\Services;

use App\Models\FamilyChild;
use App\Models\FamilyPregnancy;
use App\Models\FamilyRequest;
use App\Models\Item;
use App\Models\LifeGauge;
use App\Models\Lifer;
use App\Models\LiferGameState;
use App\Models\LiferIntimacyEvent;
use App\Models\LiferMarriage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FamilyService
{
    public const DAILY_LIMIT = 10;

    public const POSITIVE_GAUGE_EFFECT = 3;

    public const NEED_GAUGE_EFFECT = -5;

    public const PREGNANCY_DURATION_DAYS = 2;

    public function __construct(
        private readonly FamilyActionRandomizer $randomizer,
        private readonly FamilyLifecycleService $familyLifecycleService,
    ) {}

    public function request(Lifer $requester, Lifer $recipient, string $type): FamilyRequest
    {
        $allowedTypes = [
            FamilyRequest::TYPE_MARRIAGE,
            FamilyRequest::TYPE_INTIMACY_PROTECTED,
            FamilyRequest::TYPE_BABY_ATTEMPT,
        ];

        if (! in_array($type, $allowedTypes, true)) {
            throw ValidationException::withMessages(['type' => 'Ce type de demande familiale est invalide.']);
        }

        $this->assertActiveParticipants($requester, $recipient);

        if ($type === FamilyRequest::TYPE_MARRIAGE) {
            $this->assertCanMarry($requester, $recipient);
        }

        if ($type === FamilyRequest::TYPE_BABY_ATTEMPT) {
            $this->assertCanAttemptBaby($requester, $recipient);
        }

        return DB::transaction(function () use ($requester, $recipient, $type) {
            LiferGameState::query()
                ->whereIn('lifer_id', [$requester->id, $recipient->id])
                ->orderBy('lifer_id')
                ->lockForUpdate()
                ->get();

            if ($type === FamilyRequest::TYPE_MARRIAGE) {
                $duplicate = FamilyRequest::query()
                    ->where('type', $type)
                    ->where('status', FamilyRequest::STATUS_PENDING)
                    ->where(function ($query) use ($requester, $recipient) {
                        $query
                            ->where(function ($pair) use ($requester, $recipient) {
                                $pair->where('requester_lifer_id', $requester->id)
                                    ->where('recipient_lifer_id', $recipient->id);
                            })
                            ->orWhere(function ($pair) use ($requester, $recipient) {
                                $pair->where('requester_lifer_id', $recipient->id)
                                    ->where('recipient_lifer_id', $requester->id);
                            });
                    })
                    ->exists();

                if ($duplicate) {
                    throw ValidationException::withMessages(['recipient_lifer_id' => 'Une demande en mariage est déjà en attente entre ces deux Lifers.']);
                }
            } else {
                $eventType = $type === FamilyRequest::TYPE_INTIMACY_PROTECTED
                    ? LiferIntimacyEvent::TYPE_PROTECTED
                    : LiferIntimacyEvent::TYPE_BABY_ATTEMPT;
                $pendingCount = FamilyRequest::query()
                    ->where('requester_lifer_id', $requester->id)
                    ->where('type', $type)
                    ->where('status', FamilyRequest::STATUS_PENDING)
                    ->count();

                if ($this->dailyIntimacyCount($requester, $eventType) + $pendingCount >= self::DAILY_LIMIT) {
                    throw ValidationException::withMessages([
                        'recipient_lifer_id' => 'Ton Lifer a déjà utilisé ou envoyé ses dix demandes disponibles pour cette action.',
                    ]);
                }
            }

            return FamilyRequest::create([
                'requester_lifer_id' => $requester->id,
                'recipient_lifer_id' => $recipient->id,
                'type' => $type,
                'status' => FamilyRequest::STATUS_PENDING,
            ]);
        });
    }

    public function respond(FamilyRequest $familyRequest, Lifer $recipient, bool $accepted): FamilyRequest
    {
        return DB::transaction(function () use ($familyRequest, $recipient, $accepted) {
            $request = FamilyRequest::query()->lockForUpdate()->findOrFail($familyRequest->id);

            if ($request->recipient_lifer_id !== $recipient->id) {
                abort(403);
            }

            if ($request->status !== FamilyRequest::STATUS_PENDING) {
                throw ValidationException::withMessages(['request' => 'Cette demande a déjà reçu une réponse.']);
            }

            if (! $accepted) {
                $request->update([
                    'status' => FamilyRequest::STATUS_REJECTED,
                    'responded_at' => now(),
                ]);

                return $request->fresh();
            }

            $participants = Lifer::query()
                ->whereIn('id', [$request->requester_lifer_id, $request->recipient_lifer_id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $requester = $participants->get($request->requester_lifer_id);
            $lockedRecipient = $participants->get($request->recipient_lifer_id);

            if (! $requester || ! $lockedRecipient) {
                throw ValidationException::withMessages(['request' => 'Un des Lifers n’est plus disponible.']);
            }

            $this->assertActiveParticipants($requester, $lockedRecipient);

            if ($request->type === FamilyRequest::TYPE_MARRIAGE) {
                $this->acceptMarriage($requester, $lockedRecipient);
            } elseif ($request->type === FamilyRequest::TYPE_CHILD_ABANDONMENT) {
                $this->familyLifecycleService->acceptChildAbandonment($request, $requester, $lockedRecipient);
            } else {
                $this->acceptIntimacy($request, $requester, $lockedRecipient);
            }

            $request->update([
                'status' => FamilyRequest::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            return $request->fresh(['intimacyEvent.pregnancy.children']);
        });
    }

    public function cancelRequest(FamilyRequest $familyRequest, Lifer $requester): FamilyRequest
    {
        return DB::transaction(function () use ($familyRequest, $requester) {
            $request = FamilyRequest::query()->lockForUpdate()->findOrFail($familyRequest->id);

            if ($request->requester_lifer_id !== $requester->id) {
                abort(403);
            }

            if ($request->status !== FamilyRequest::STATUS_PENDING) {
                throw ValidationException::withMessages(['request' => 'Seule une demande encore en attente peut être annulée.']);
            }

            $request->update([
                'status' => FamilyRequest::STATUS_CANCELLED,
                'responded_at' => now(),
            ]);

            return $request->fresh();
        });
    }

    public function divorce(Lifer $lifer): LiferMarriage
    {
        return DB::transaction(function () use ($lifer) {
            $marriage = LiferMarriage::query()
                ->where('status', LiferMarriage::STATUS_ACTIVE)
                ->where(function ($query) use ($lifer) {
                    $query->where('first_lifer_id', $lifer->id)
                        ->orWhere('second_lifer_id', $lifer->id);
                })
                ->lockForUpdate()
                ->first();

            if (! $marriage) {
                throw ValidationException::withMessages(['marriage' => 'Ton Lifer ne possède aucun mariage actif.']);
            }

            $marriage->update([
                'status' => LiferMarriage::STATUS_DIVORCED,
                'ended_at' => now(),
                'end_reason' => 'divorce',
            ]);

            return $marriage->fresh();
        });
    }

    private function acceptMarriage(Lifer $first, Lifer $second): void
    {
        $this->assertCanMarry($first, $second);

        LiferMarriage::create([
            'first_lifer_id' => $first->id,
            'second_lifer_id' => $second->id,
            'lower_lifer_id' => min($first->id, $second->id),
            'higher_lifer_id' => max($first->id, $second->id),
            'status' => LiferMarriage::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    private function acceptIntimacy(FamilyRequest $request, Lifer $first, Lifer $second): void
    {
        $eventType = $request->type === FamilyRequest::TYPE_INTIMACY_PROTECTED
            ? LiferIntimacyEvent::TYPE_PROTECTED
            : LiferIntimacyEvent::TYPE_BABY_ATTEMPT;

        if ($eventType === LiferIntimacyEvent::TYPE_BABY_ATTEMPT) {
            $this->assertCanAttemptBaby($first, $second);
        }

        LiferGameState::query()
            ->whereIn('lifer_id', [$first->id, $second->id])
            ->orderBy('lifer_id')
            ->lockForUpdate()
            ->get();

        foreach ([$first, $second] as $participant) {
            if ($this->dailyIntimacyCount($participant, $eventType) >= self::DAILY_LIMIT) {
                throw ValidationException::withMessages(['request' => 'Un des Lifers a atteint sa limite quotidienne pour cette action.']);
            }
        }

        if ($eventType === LiferIntimacyEvent::TYPE_PROTECTED) {
            $this->consumeProtection($first);
        }

        $gauges = LifeGauge::query()
            ->whereIn('lifer_id', [$first->id, $second->id])
            ->orderBy('lifer_id')
            ->lockForUpdate()
            ->get();

        if ($gauges->count() !== 2) {
            throw ValidationException::withMessages(['request' => 'Les jauges d’un des Lifers sont indisponibles.']);
        }

        foreach ($gauges as $gauge) {
            $gauge->update([
                'happiness' => min(100, $gauge->happiness + self::POSITIVE_GAUGE_EFFECT),
                'entertainment' => min(100, $gauge->entertainment + self::POSITIVE_GAUGE_EFFECT),
                'physical_condition' => min(100, $gauge->physical_condition + self::POSITIVE_GAUGE_EFFECT),
                'clean' => max(0, $gauge->clean + self::NEED_GAUGE_EFFECT),
                'hunger' => max(0, $gauge->hunger + self::NEED_GAUGE_EFFECT),
                'thirst' => max(0, $gauge->thirst + self::NEED_GAUGE_EFFECT),
            ]);
        }

        $conceptionSucceeded = $eventType === LiferIntimacyEvent::TYPE_BABY_ATTEMPT
            && $this->randomizer->conceptionSucceeds();
        $event = LiferIntimacyEvent::create([
            'request_id' => $request->id,
            'first_lifer_id' => $first->id,
            'second_lifer_id' => $second->id,
            'type' => $eventType,
            'conception_succeeded' => $conceptionSucceeded,
            'happened_on' => today(),
        ]);

        if ($conceptionSucceeded) {
            $this->createPregnancy($event, $first, $second);
        }
    }

    private function createPregnancy(LiferIntimacyEvent $event, Lifer $first, Lifer $second): FamilyPregnancy
    {
        $mother = $first->sex === Lifer::SEX_FEMALE ? $first : $second;
        $father = $first->sex === Lifer::SEX_MALE ? $first : $second;
        $childrenCount = $this->randomizer->childrenCount();
        $pregnancy = FamilyPregnancy::create([
            'intimacy_event_id' => $event->id,
            'mother_lifer_id' => $mother->id,
            'father_lifer_id' => $father->id,
            'children_count' => $childrenCount,
            'status' => FamilyPregnancy::STATUS_ACTIVE,
            'conceived_at' => now(),
            'due_at' => now()->addDays(self::PREGNANCY_DURATION_DAYS),
        ]);

        for ($birthOrder = 1; $birthOrder <= $childrenCount; $birthOrder++) {
            $child = FamilyChild::create([
                'pregnancy_id' => $pregnancy->id,
                'biological_mother_lifer_id' => $mother->id,
                'biological_father_lifer_id' => $father->id,
                'birth_order' => $birthOrder,
                'sex' => $this->randomizer->childSex(),
                'status' => FamilyChild::STATUS_EXPECTED,
                'conceived_at' => now(),
            ]);

            $child->guardians()->attach([
                $mother->id => ['type' => 'biological', 'has_custody' => true],
                $father->id => ['type' => 'biological', 'has_custody' => true],
            ]);
        }

        return $pregnancy;
    }

    private function consumeProtection(Lifer $lifer): void
    {
        $protectionItemId = Item::query()
            ->where('name', Item::FAMILY_PROTECTION_NAME)
            ->value('id');

        if (! $protectionItemId) {
            throw ValidationException::withMessages(['request' => 'Aucune protection n’est disponible au Life Market.']);
        }

        $inventoryItem = DB::table('inventory_items')
            ->where('inventory_id', $lifer->id)
            ->where('item_id', $protectionItemId)
            ->lockForUpdate()
            ->first();

        if (! $inventoryItem || $inventoryItem->quantity < 1) {
            throw ValidationException::withMessages(['request' => 'Le Lifer à l’origine de la demande ne possède aucune protection.']);
        }

        if ($inventoryItem->quantity === 1) {
            DB::table('inventory_items')
                ->where('inventory_id', $lifer->id)
                ->where('item_id', $protectionItemId)
                ->delete();
        } else {
            DB::table('inventory_items')
                ->where('inventory_id', $lifer->id)
                ->where('item_id', $protectionItemId)
                ->decrement('quantity');
        }
    }

    private function dailyIntimacyCount(Lifer $lifer, string $eventType): int
    {
        return LiferIntimacyEvent::query()
            ->where('type', $eventType)
            ->whereDate('happened_on', today())
            ->where(function ($query) use ($lifer) {
                $query->where('first_lifer_id', $lifer->id)
                    ->orWhere('second_lifer_id', $lifer->id);
            })
            ->count();
    }

    private function assertCanMarry(Lifer $first, Lifer $second): void
    {
        $alreadyMarried = LiferMarriage::query()
            ->where('status', LiferMarriage::STATUS_ACTIVE)
            ->where(function ($query) use ($first, $second) {
                $query->whereIn('first_lifer_id', [$first->id, $second->id])
                    ->orWhereIn('second_lifer_id', [$first->id, $second->id]);
            })
            ->exists();

        if ($alreadyMarried) {
            throw ValidationException::withMessages(['recipient_lifer_id' => 'Un des Lifers est déjà marié.']);
        }
    }

    private function assertCanAttemptBaby(Lifer $first, Lifer $second): void
    {
        if ($first->sex === $second->sex) {
            throw ValidationException::withMessages(['recipient_lifer_id' => 'Une tentative de bébé nécessite deux Lifers de sexes biologiques opposés.']);
        }

        $mother = $first->sex === Lifer::SEX_FEMALE ? $first : $second;
        $pregnancyFromPreviousDay = FamilyPregnancy::query()
            ->where('mother_lifer_id', $mother->id)
            ->where('status', FamilyPregnancy::STATUS_ACTIVE)
            ->whereDate('conceived_at', '<', today())
            ->exists();

        if ($pregnancyFromPreviousDay) {
            throw ValidationException::withMessages(['recipient_lifer_id' => 'Ce Lifer attend déjà une naissance.']);
        }
    }

    private function assertActiveParticipants(Lifer $first, Lifer $second): void
    {
        if ($first->is($second)) {
            throw ValidationException::withMessages(['recipient_lifer_id' => 'Un Lifer ne peut pas s’envoyer cette demande à lui-même.']);
        }

        if (
            $first->status !== Lifer::STATUS_ACTIVE
            || $second->status !== Lifer::STATUS_ACTIVE
            || ! $first->gameState()->exists()
            || ! $second->gameState()->exists()
        ) {
            throw ValidationException::withMessages(['recipient_lifer_id' => 'Les deux Lifers doivent être actifs.']);
        }
    }
}
