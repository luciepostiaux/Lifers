<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
    
    public function broadcastOn(): array
    {
        $conversation = $this->message->conversation()
            ->with('lifers:id,user_id')
            ->first();

        $channels = [new PresenceChannel('conversation.'.$this->message->conversation_id)];

        if ($conversation?->type === 'private') {
            $recipientUserIds = $conversation->lifers
                ->where('id', '<>', $this->message->sender_lifer_id)
                ->pluck('user_id')
                ->filter()
                ->unique();

            foreach ($recipientUserIds as $userId) {
                $channels[] = new PrivateChannel('App.Models.User.'.$userId);
            }
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->loadMissing('sender'),
            'conversation_type' => $this->message->conversation()->value('type'),
        ];
    }
}
