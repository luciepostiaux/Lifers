<?php

namespace App\Http\Controllers;

use App\Models\LiferGameState;
use App\Models\LiferSubscription;
use App\Models\SportSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function subscribeToGym(Request $request)
    {
        $validated = $request->validate([
            'sportSessionId' => ['required', 'integer', 'exists:sport_sessions,id'],
        ]);

        $lifer = $this->activeLifer();
        $plan = SportSession::whereKey($validated['sportSessionId'])->where('type', 'gym')->firstOrFail();

        DB::transaction(function () use ($lifer, $plan) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);
            $active = LiferSubscription::query()
                ->where('lifer_id', $lifer->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if ($active?->sport_session_id === $plan->id) {
                throw ValidationException::withMessages([
                    'sportSessionId' => 'Cet abonnement est déjà actif.',
                ]);
            }

            if ($state->money < $plan->price) {
                throw ValidationException::withMessages([
                    'sportSessionId' => 'Vous n’avez pas assez d’argent pour cet abonnement.',
                ]);
            }

            $active?->update(['status' => 'cancelled', 'ends_at' => now()]);
            $state->decrement('money', $plan->price);
            $state->update(['last_sport_activity_on' => today()]);

            LiferSubscription::create([
                'lifer_id' => $lifer->id,
                'sport_session_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->duration_days),
                'status' => 'active',
            ]);
        });

        return back()->with('message', 'Abonnement à la salle de sport réussi.');
    }

    public function cancelGymSubscription()
    {
        $lifer = $this->activeLifer();

        $updated = LiferSubscription::query()
            ->where('lifer_id', $lifer->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled', 'ends_at' => now()]);

        if (! $updated) {
            throw ValidationException::withMessages([
                'subscription' => 'Aucun abonnement actif à annuler.',
            ]);
        }

        return back()->with('message', 'Votre abonnement a été annulé.');
    }
}
