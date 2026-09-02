<?php

namespace App\Services;

use App\Models\DailyJournalAccess;
use App\Models\Lifer;
use App\Models\LiferGameState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyJournalService
{
    public function hasAccess(Lifer $lifer): bool
    {
        return DailyJournalAccess::query()
            ->where('lifer_id', $lifer->id)
            ->whereDate('access_date', today())
            ->exists();
    }

    public function purchase(Lifer $lifer): DailyJournalAccess
    {
        return DB::transaction(function () use ($lifer) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);
            $existing = DailyJournalAccess::query()
                ->where('lifer_id', $lifer->id)
                ->whereDate('access_date', today())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            if ($state->money < DailyJournalAccess::PRICE) {
                throw ValidationException::withMessages([
                    'journal' => 'Il te faut 1 Lif’coin pour acheter le journal du jour.',
                ]);
            }

            $state->decrement('money', DailyJournalAccess::PRICE);

            return DailyJournalAccess::create([
                'lifer_id' => $lifer->id,
                'access_date' => today(),
                'price_paid' => DailyJournalAccess::PRICE,
                'purchased_at' => now(),
            ]);
        });
    }
}
