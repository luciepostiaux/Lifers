<?php

namespace App\Listeners;

use App\Models\Conversation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddUserToGeneralConversation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event)
    {
        $conversation = Conversation::where('name', 'Général')->first();
        if ($conversation) {
            $conversation->users()->syncWithoutDetaching($event->user->id);
        }
    }
}
