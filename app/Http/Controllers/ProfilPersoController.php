<?php

namespace App\Http\Controllers;

use App\Models\Perso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfilPersoController extends Controller
{
    public function index()
    {
        $perso = auth()->user()->perso()->with(['job', 'enrolledStudies.study', 'images'])->firstOrFail();

        $bodyImageUrl = $perso && $perso->body ? $perso->body->img_perso : null;
        $money = $perso ? $perso->money : 0;

        return Inertia::render('ProfilPerso/Index', [
            'perso' => $perso,
            'age' => $perso ? $perso->calculateAge() : null,
            'bodyImageUrl' => $bodyImageUrl,
            'money' => $money,


        ]);
    }

    public function saveDescription(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
        ]);
        \Illuminate\Support\Facades\Log::info('saveDescription called');

        $user = auth()->user();
        $perso = $user->perso;
        if ($perso) {
            $perso->update([
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Description sauvegardée avec succès.');
        }
        return redirect()->back()->with('error', 'Personnage non trouvé.');
    }

    public function public($persoId)
    {
        $perso = Perso::with(['job', 'images']) // Assure-toi d'ajuster les relations selon ton modèle
            ->findOrFail($persoId);

        return Inertia::render('ProfilPerso/Public', [ // Crée une nouvelle vue pour le profil public
            'perso' => $perso,
            // Tu peux calculer l'âge et passer d'autres informations si nécessaire
            'age' => $perso->calculateAge(),
        ]);
    }
}
