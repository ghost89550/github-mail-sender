<?php declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegister;

class UserRegisterListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserRegister $event
     * @return void
     */
    public function handle(UserRegister $event)
    {
//        dd($event);
    }
}
