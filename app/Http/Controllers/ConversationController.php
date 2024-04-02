<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
    }

    public function show(Conversation $conversation)
    {
        // Afficher une conversation spécifique
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'content' => $request->content,
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();
        return response()->json($message);
    }

    public function fetchMessages(Conversation $conversation)
    {
        $messages = $conversation->messages()->with('sender')->get();

        return response()->json($messages);
    }
}
