<?php

namespace App\Http\Controllers;

use App\Models\LiferGameState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SicknessController extends Controller
{
    public function treatSickness(Request $request)
    {
        $validated = $request->validate([
            'sicknessId' => ['required', 'integer', 'exists:sicknesses,id'],
        ]);

        $lifer = $this->activeLifer();

        DB::transaction(function () use ($lifer, $validated) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);
            $sickness = $lifer->sicknesses()
                ->whereKey($validated['sicknessId'])
                ->first();

            if (! $sickness) {
                throw ValidationException::withMessages([
                    'sicknessId' => 'Votre Lifer ne possède pas cette maladie.',
                ]);
            }

            if ($sickness->treatment_cost === null) {
                throw ValidationException::withMessages([
                    'sicknessId' => 'Cette maladie ne possède actuellement aucun traitement.',
                ]);
            }

            if ($state->money < $sickness->treatment_cost) {
                throw ValidationException::withMessages([
                    'sicknessId' => 'Vous n’avez pas assez d’argent pour ce traitement.',
                ]);
            }

            $state->decrement('money', $sickness->treatment_cost);
            $lifer->sicknesses()->detach($sickness->id);
        });

        return back()->with('success', 'Traitement réussi, votre Lifer est guéri.');
    }

    public function visitDoctor()
    {
        $lifer = $this->activeLifer();

        DB::transaction(function () use ($lifer) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);
            $lifeGauge = $lifer->lifeGauge()->lockForUpdate()->firstOrFail();

            if ($state->money < 150) {
                throw ValidationException::withMessages([
                    'doctor' => 'Vous n’avez pas assez d’argent pour cette visite.',
                ]);
            }

            $state->decrement('money', 150);
            $lifeGauge->update(['health' => 100]);
        });

        return back()->with('success', 'Visite réussie, votre santé est maintenant à 100 %.');
    }
}
