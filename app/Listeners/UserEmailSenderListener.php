<?php declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegister;
use App\Mail\GreetingsMail;
use Illuminate\Support\Facades\Mail;

class UserEmailSenderListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  UserRegister $event
     * @return void
     */
    public function handle(UserRegister $event): void
    {
        Mail::to($event->user)
            ->send(new GreetingsMail());
    }
}
