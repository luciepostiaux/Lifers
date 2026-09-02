<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        $liferId = $user->activeLifer()->value('id');

        return $liferId !== null && $conversation->lifers()->whereKey($liferId)->exists();
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    public function manageMembers(User $user, Conversation $conversation): bool
    {
        return $conversation->type === Conversation::TYPE_GROUP
            && $this->view($user, $conversation);
    }

    public function leave(User $user, Conversation $conversation): bool
    {
        return $this->manageMembers($user, $conversation);
    }
}
