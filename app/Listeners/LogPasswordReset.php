<?php

namespace App\Listeners;

use IlluminateAuthEventsPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogPasswordReset
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
    public function handle(PasswordReset $event): void
    {
        activity()
            ->causedBy($event->user)
            ->event('password_reset')
            ->log("User {$event->user->name} reset their password.");
    }
}
