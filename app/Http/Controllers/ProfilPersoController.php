<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfilPersoController extends Controller
{
    public function index()
    {
        $perso = auth()->user()->perso()->with(['job', 'enrolledStudies.study', 'images'])->firstOrFail();

        $bodyImageUrl = $perso && $perso->body ? $perso->body->img_perso : null;

        return Inertia::render('ProfilPerso/Index', [
            'perso' => $perso,
            'age' => $perso ? $perso->calculateAge() : null,
            'bodyImageUrl' => $bodyImageUrl,

        ]);
    }

    public function saveDescription(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
        ]);

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
}
