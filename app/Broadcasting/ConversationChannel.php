<?php

namespace App\Broadcasting;

use App\Models\Conversation;
use App\Models\User;

class ConversationChannel
{
    public function join(User $user, Conversation $conversation): array|bool
    {
        if (! $user->can('view', $conversation)) {
            return false;
        }

        $lifer = $user->activeLifer()->first();

        return $lifer ? [
            'id' => $lifer->id,
            'name' => $lifer->first_name.' '.$lifer->last_name,
            'staff_role' => $lifer->staffRole(),
        ] : false;
    }
}
