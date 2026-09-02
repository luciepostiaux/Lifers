<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Lifer;
use Inertia\Inertia;

class SocialController extends Controller
{
    public function index(?int $id = null)
    {
        $lifer = $this->activeLifer(['gameState']);
        Inertia::share('csrf_token', csrf_token());

        $generalConversation = Conversation::firstOrCreate(
            ['key' => 'general'],
            ['name' => 'Général', 'type' => Conversation::TYPE_GENERAL],
        );
        $generalConversation->joinGeneralWithoutPastHistory($lifer);

        $conversations = $lifer->conversations()
            ->with([
                'lifers:id,first_name,last_name',
                'latestMessage.sender:id,first_name,last_name',
            ])
            ->withCount('lifers')
            ->orderByRaw("CASE WHEN type = 'general' THEN 0 WHEN type = 'private' THEN 1 ELSE 2 END")
            ->orderByDesc('updated_at')
            ->get();

        $conversations->each(function (Conversation $conversation) use ($lifer) {
            if ($conversation->type === Conversation::TYPE_GENERAL) {
                $conversation->setAttribute('display_name', 'Général');

                return;
            }

            if ($conversation->type === Conversation::TYPE_GROUP) {
                $conversation->setAttribute('display_name', $conversation->name ?: 'Groupe sans nom');

                return;
            }

            $otherLifer = $conversation->lifers->first(fn (Lifer $member) => ! $member->is($lifer));
            $conversation->setAttribute(
                'display_name',
                $otherLifer ? $otherLifer->first_name.' '.$otherLifer->last_name : 'Conversation privée',
            );
        });

        if ($id === null) {
            return redirect('/social/'.$generalConversation->id);
        }

        $currentConversation = $conversations->firstWhere('id', $id);
        abort_unless($currentConversation, 403);
        $currentConversation->markReceivedMessagesAsReadBy($lifer);

        $allLifers = Lifer::active()
            ->with('user:id,is_online')
            ->get()
            ->mapWithKeys(fn (Lifer $member) => [
                $member->id => [
                    'id' => $member->id,
                    'persoName' => $member->first_name.' '.$member->last_name,
                    'isOnline' => (bool) $member->user->is_online,
                ],
            ]);

        return Inertia::render('Social/Index', [
            'conversations' => $conversations,
            'messages' => $currentConversation->messagesVisibleTo($lifer)->with('sender')->oldest()->get(),
            'currentConversationId' => $currentConversation->id,
            'allPerso' => $allLifers,
            'currentLifer' => [
                'id' => $lifer->id,
                'name' => $lifer->first_name.' '.$lifer->last_name,
            ],
            'money' => $lifer->gameState?->money,
        ]);
    }
}
