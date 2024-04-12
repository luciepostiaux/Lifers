<?php

namespace App\Http\Controllers;

use App\Models\Perso;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    public function sendRequest(Request $request)
    {
        $user = auth()->user(); // Récupérer l'utilisateur connecté

        // Vérifier si l'utilisateur a un perso associé
        if (!$user->perso_id) {
            return back()->withErrors(['error' => 'Aucun personnage associé à cet utilisateur.']);
        }

        $perso = Perso::find($user->perso_id);

        // Vérifier si le personnage existe
        if (!$perso) {
            return back()->withErrors(['error' => 'Personnage non trouvé.']);
        }

        // Envoyer la demande d'amitié
        $perso->sendFriendRequestTo($request->friendId);

        return back()->with('success', 'Demande d\'ami envoyée !');
    }
}
