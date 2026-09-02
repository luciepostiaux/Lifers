<?php

namespace App\Services;

use App\Models\BodyType;
use App\Models\FamilyChild;
use App\Models\FamilyPregnancy;
use App\Models\FamilyRequest;
use App\Models\Inventory;
use App\Models\LifeGauge;
use App\Models\Lifer;
use App\Models\LiferGameState;
use App\Models\LiferMarriage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiferLifecycleService
{
    public function create(User $user, BodyType $bodyType, array $identity): Lifer
    {
        return DB::transaction(function () use ($user, $bodyType, $identity) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->activeLifer()->exists()) {
                throw ValidationException::withMessages([
                    'lifer' => 'Ce compte possède déjà un Lifer actif.',
                ]);
            }

            $lifer = $lockedUser->lifers()->create([
                'first_name' => $identity['first_name'],
                'last_name' => $identity['last_name'],
                'sex' => $bodyType->sex,
                'born_at' => now(),
                'status' => Lifer::STATUS_ACTIVE,
            ]);

            $this->initializeGameState($lifer, $bodyType);

            return $lifer->fresh(['gameState.bodyType', 'lifeGauge', 'inventory']);
        });
    }

    public function reincarnate(User $user, FamilyChild $familyChild, BodyType $bodyType): Lifer
    {
        return DB::transaction(function () use ($user, $familyChild, $bodyType) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->activeLifer()->exists()) {
                throw ValidationException::withMessages([
                    'lifer' => 'Ce compte possède déjà un Lifer actif.',
                ]);
            }

            $lockedChild = FamilyChild::query()->lockForUpdate()->findOrFail($familyChild->id);

            if (
                $lockedChild->status !== FamilyChild::STATUS_AVAILABLE
                || $lockedChild->claimed_lifer_id !== null
                || ! $lockedChild->adult_at
                || $lockedChild->adult_at->isFuture()
                || blank($lockedChild->first_name)
                || blank($lockedChild->last_name)
            ) {
                throw ValidationException::withMessages([
                    'family_child_id' => 'Cette identité familiale n’est plus disponible.',
                ]);
            }

            if ($bodyType->sex !== $lockedChild->sex) {
                throw ValidationException::withMessages([
                    'body_type_id' => 'Cette apparence ne correspond pas à cette identité.',
                ]);
            }

            $lifer = $lockedUser->lifers()->create([
                'first_name' => $lockedChild->first_name,
                'last_name' => $lockedChild->last_name,
                'sex' => $lockedChild->sex,
                'born_at' => now(),
                'status' => Lifer::STATUS_ACTIVE,
            ]);

            $this->initializeGameState($lifer, $bodyType);

            $lockedChild->update([
                'status' => FamilyChild::STATUS_CLAIMED,
                'claimed_lifer_id' => $lifer->id,
            ]);

            return $lifer->fresh(['gameState.bodyType', 'lifeGauge', 'inventory']);
        });
    }

    public function die(Lifer $lifer, string $cause): Lifer
    {
        $cause = trim($cause);

        if ($cause === '') {
            throw ValidationException::withMessages([
                'death_cause' => 'La cause du décès est obligatoire.',
            ]);
        }

        return DB::transaction(function () use ($lifer, $cause) {
            $lockedLifer = Lifer::query()->lockForUpdate()->findOrFail($lifer->id);

            if ($lockedLifer->status !== Lifer::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'lifer' => 'Ce Lifer est déjà mort.',
                ]);
            }

            $lockedLifer->update([
                'status' => Lifer::STATUS_DEAD,
                'died_at' => now(),
                'age_at_death' => $lockedLifer->calculateAge(),
                'death_cause' => $cause,
            ]);

            LiferMarriage::query()
                ->where('status', LiferMarriage::STATUS_ACTIVE)
                ->where(function ($query) use ($lockedLifer) {
                    $query->where('first_lifer_id', $lockedLifer->id)
                        ->orWhere('second_lifer_id', $lockedLifer->id);
                })
                ->update([
                    'status' => LiferMarriage::STATUS_WIDOWED,
                    'ended_at' => now(),
                    'end_reason' => 'death',
                    'updated_at' => now(),
                ]);

            FamilyRequest::query()
                ->where('status', FamilyRequest::STATUS_PENDING)
                ->where(function ($query) use ($lockedLifer) {
                    $query->where('requester_lifer_id', $lockedLifer->id)
                        ->orWhere('recipient_lifer_id', $lockedLifer->id);
                })
                ->update([
                    'status' => FamilyRequest::STATUS_CANCELLED,
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);

            FamilyPregnancy::query()
                ->where('mother_lifer_id', $lockedLifer->id)
                ->where('status', FamilyPregnancy::STATUS_ACTIVE)
                ->update([
                    'status' => FamilyPregnancy::STATUS_LOST,
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            $guardedChildIds = DB::table('family_child_guardians')
                ->where('lifer_id', $lockedLifer->id)
                ->where('has_custody', true)
                ->pluck('child_id');
            DB::table('family_child_guardians')
                ->where('lifer_id', $lockedLifer->id)
                ->where('has_custody', true)
                ->update(['has_custody' => false, 'updated_at' => now()]);

            foreach ($guardedChildIds as $childId) {
                $hasActiveCustodian = DB::table('family_child_guardians')
                    ->join('lifers', 'lifers.id', '=', 'family_child_guardians.lifer_id')
                    ->where('family_child_guardians.child_id', $childId)
                    ->where('family_child_guardians.has_custody', true)
                    ->where('lifers.status', Lifer::STATUS_ACTIVE)
                    ->exists();

                if (! $hasActiveCustodian) {
                    DB::table('family_children')
                        ->where('id', $childId)
                        ->where('status', 'dependent')
                        ->update(['status' => 'orphaned', 'updated_at' => now()]);
                }
            }

            $lockedLifer->profileImages()->get()->each->delete();
            $lockedLifer->gameState()->delete();

            return $lockedLifer->fresh();
        });
    }

    private function initializeGameState(Lifer $lifer, BodyType $bodyType): void
    {
        LiferGameState::create([
            'lifer_id' => $lifer->id,
            'body_type_id' => $bodyType->id,
            'money' => 900,
        ]);

        LifeGauge::create(['lifer_id' => $lifer->id]);
        Inventory::create(['lifer_id' => $lifer->id]);
    }
}
