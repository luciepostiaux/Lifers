<?php

namespace App\Events;

use App\Models\SmsMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SmsMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $smsMessage;

    public function __construct(SmsMessage $smsMessage)
    {
        $this->smsMessage = $smsMessage;
    }

    public function broadcastOn()
    {

        return new PrivateChannel('sms-channel.' . $this->smsMessage->receiver_id);
    }
}
