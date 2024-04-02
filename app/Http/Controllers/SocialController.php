<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SocialController extends Controller
{
    public function index(int $id = null)
    {
        $user = Auth::user();
        $perso = $user->perso;
        Inertia::share('csrf_token', csrf_token());

        // Récupération des conversations de l'utilisateur authentifié
        $conversations = Conversation::all();


        if ($id === null) {
            $currentConversation = Conversation::firstOrCreate([
                'name' => 'Test',

                // Ajoutez ici d'autres critères si nécessaire
            ]);
            return redirect('/social/' . $currentConversation->id);
        } else {
            $currentConversation = Conversation::findOrFail($id);
        }

        $currentConversationId = $currentConversation->id;
        // Récupération des messages de la conversation courante
        $messages = $currentConversation->messages()->with('sender')->get();
        // Si vous souhaitez également afficher les autres utilisateurs et leurs persos
        $allPerso = User::with('perso')->get()->mapWithKeys(function ($user) {
            return [
                $user->id =>
                [
                    'id' => $user->id,
                    'persoName' => $user->perso->first_name . ' ' . $user->perso->last_name,
                    'isOnline' => $user->is_online,
                ]
            ];
        });
        return Inertia::render('Social/Index', [
            'conversations' => $conversations,
            'messages' => $messages,
            'currentConversationId' => $currentConversationId,
            'allPerso' => $allPerso,
            'user' => $user,
        ]);
    }
}
