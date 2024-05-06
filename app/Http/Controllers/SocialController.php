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

        $conversations = Conversation::all();


        if ($id === null) {
            $currentConversation = Conversation::firstOrCreate([
                'name' => 'Général',

            ]);
            return redirect('/social/' . $currentConversation->id);
        } else {
            $currentConversation = Conversation::findOrFail($id);
        }

        $currentConversationId = $currentConversation->id;
        $messages = $currentConversation->messages()->with('sender')->get();
        $allPerso = User::with('perso', 'roles')
            ->get()
            ->mapWithKeys(function ($user) {
                // Vérifier si l'utilisateur est un admin
                $isAdmin = $user->roles->contains('name', 'admin');

                return [
                    $user->id => [
                        'id' => $user->id,
                        'persoName' => $user->perso?->first_name . ' ' . $user->perso?->last_name,
                        'isOnline' => $user->is_online,
                        'isAdmin' => $isAdmin, // Indiquer si l'utilisateur est admin
                    ],
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
