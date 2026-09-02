<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        if ($conversation->isStaffConversation() && ! $user->canModerate()) {
            return false;
        }

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
            && ! $conversation->isStaffConversation()
            && $this->view($user, $conversation);
    }

    public function leave(User $user, Conversation $conversation): bool
    {
        return $this->manageMembers($user, $conversation);
    }
}
