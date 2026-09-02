<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Lifer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConversationController extends Controller
{
    public function index()
    {
        return response()->json(
            $this->activeLifer()->conversations()->orderBy('type')->get(),
        );
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $lifer = $this->activeLifer();
        $conversation->markReceivedMessagesAsReadBy($lifer);
        $conversation->load('lifers');
        $conversation->setRelation(
            'messages',
            $conversation->messagesVisibleTo($lifer)->with('sender')->oldest()->get(),
        );

        return response()->json($conversation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lifer_id' => ['required', 'integer', 'exists:lifer_game_states,lifer_id'],
        ]);

        $sender = $this->activeLifer();
        $recipient = Lifer::active()->findOrFail($validated['lifer_id']);

        if ($recipient->is($sender)) {
            throw ValidationException::withMessages([
                'lifer_id' => 'Vous ne pouvez pas créer une conversation privée avec vous-même.',
            ]);
        }

        $conversation = DB::transaction(function () use ($sender, $recipient) {
            $conversation = Conversation::firstOrCreate(
                ['key' => Conversation::privateKey($sender->id, $recipient->id)],
                ['name' => null, 'type' => Conversation::TYPE_PRIVATE],
            );

            $conversation->lifers()->syncWithoutDetaching([$sender->id, $recipient->id]);

            return $conversation;
        });

        return response()->json(
            $conversation->load('lifers'),
            $conversation->wasRecentlyCreated ? 201 : 200,
        );
    }

    public function storeGroup(Request $request)
    {
        $creator = $this->activeLifer();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'member_ids' => ['required', 'array', 'min:1', 'max:50'],
            'member_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('lifer_game_states', 'lifer_id'),
                Rule::notIn([$creator->id]),
            ],
        ]);

        $conversation = DB::transaction(function () use ($creator, $validated) {
            $conversation = Conversation::create([
                'name' => Str::squish($validated['name']),
                'type' => Conversation::TYPE_GROUP,
                'key' => 'group:'.Str::uuid(),
            ]);

            $conversation->lifers()->attach([
                $creator->id,
                ...$validated['member_ids'],
            ]);

            return $conversation;
        });

        return response()->json($conversation->load('lifers'), 201);
    }

    public function addMembers(Request $request, Conversation $conversation)
    {
        $this->authorize('manageMembers', $conversation);

        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1', 'max:50'],
            'member_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('lifer_game_states', 'lifer_id'),
            ],
        ]);

        $conversation->lifers()->syncWithoutDetaching($validated['member_ids']);

        return response()->json($conversation->load('lifers'));
    }

    public function leaveGroup(Conversation $conversation)
    {
        $this->authorize('leave', $conversation);

        $lifer = $this->activeLifer();

        DB::transaction(function () use ($conversation, $lifer) {
            $lockedConversation = Conversation::query()
                ->whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedConversation->lifers()->detach($lifer->id);

            if (! $lockedConversation->lifers()->exists()) {
                $lockedConversation->delete();
            }
        });

        $general = Conversation::firstOrCreate(
            ['key' => 'general'],
            ['name' => 'Général', 'type' => Conversation::TYPE_GENERAL],
        );
        $general->joinGeneralWithoutPastHistory($lifer);

        return response()->json([
            'redirect_to' => route('social', ['id' => $general->id]),
        ]);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('sendMessage', $conversation);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_lifer_id' => $this->activeLifer()->id,
            'content' => $validated['content'],
        ]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function fetchMessages(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $lifer = $this->activeLifer();
        $conversation->markReceivedMessagesAsReadBy($lifer);

        return response()->json(
            $conversation->messagesVisibleTo($lifer)
                ->with('sender')
                ->oldest()
                ->get(),
        );
    }

    public function markRead(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $conversation->markReceivedMessagesAsReadBy($this->activeLifer());

        return response()->noContent();
    }
}
