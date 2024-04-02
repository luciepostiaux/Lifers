<?php

namespace App\Listeners;

use App\Events\UserStatusChanged;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserLoggedIn
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
    public function handle(Login $event)
    {
        $event->user->is_online = true;
        $event->user->save();
    }
}
