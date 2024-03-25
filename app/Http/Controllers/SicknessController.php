<?php

namespace App\Http\Controllers;

use App\Models\Sickness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SicknessController extends Controller
{

    public function treatSickness(Request $request)
    {
        $user = Auth::user();
        $perso = $user->perso;
        $sickness = Sickness::findOrFail($request->sicknessId);

        // Vérifier si le personnage a suffisamment d'argent
        if ($perso->money < $sickness->treatment_cost) {
            return back()->withErrors('Vous n\'avez pas assez d\'argent pour ce traitement.');
        }

        // Payer pour le traitement et retirer la maladie
        $perso->decrement('money', $sickness->treatment_cost);
        $perso->sicknesses()->detach($sickness->id);

        return back()->with('success', 'Traitement réussi, vous êtes guéri de la maladie.');
    }

    public function visitDoctor()
    {
        $user = Auth::user();
        $perso = $user->perso;

        // Vérifier si le personnage a suffisamment d'argent
        if ($perso->money < 150) {
            return back()->withErrors('Vous n\'avez pas assez d\'argent pour cette visite.');
        }

        // Payer pour la visite et restaurer la santé
        $perso->decrement('money', 150);
        $perso->lifeGauge->update(['health' => 100]);

        return back()->with('success', 'Visite réussie, votre santé est maintenant à 100%.');
    }
}
