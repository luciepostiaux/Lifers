<?php

namespace App\Listeners;

use App\Events\UserStatusChanged;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserLoggedOut
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
    public function handle(Logout $event)
    {
        $event->user->is_online = false;
        $event->user->save();
    }
}
