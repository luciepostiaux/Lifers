<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perso;
use App\Models\Diploma;

class AdminController extends Controller
{
    public function grantDiploma(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,id',
            'diplomaId' => 'required|exists:diplomas,id',
        ]);

        $perso = Perso::where('user_id', $request->userId)->first();

        // Vérifier si le personnage possède déjà le diplôme
        if ($perso && !$perso->diplomas()->where('diplomas_id', $request->diplomaId)->exists()) {
            // Le personnage n'a pas ce diplôme, alors on peut l'ajouter
            $perso->diplomas()->attach($request->diplomaId);
            return back()->with('success', 'Diplôme attribué avec succès.');
        }

        // Le personnage possède déjà ce diplôme
        return back()->with('error', 'Le personnage possède déjà ce diplôme.');
    }


    public function removeDiploma(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,id',
            'diplomaId' => 'required|exists:diplomas,id',
        ]);

        $perso = Perso::where('user_id', $request->userId)->first();

        // Vérifiez si le personnage a déjà le diplôme.
        if ($perso->diplomas()->where('diplomas_id', $request->diplomaId)->exists()) {
            // Le personnage a le diplôme, alors on peut le retirer.
            $perso->diplomas()->detach($request->diplomaId);
            return back()->with('success', 'Diplôme retiré avec succès.');
        } else {
            // Le personnage n'a pas ce diplôme.
            return back()->with('warning', 'Le personnage n\'avait déjà pas ce diplôme.');
        }
    }
}
