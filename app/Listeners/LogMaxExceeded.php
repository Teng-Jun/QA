<?php

namespace App\Listeners;

use App\Events\MaxLoginAttemptsExceeded;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LogMaxExceeded
{
        /**
     * Handle the event.
     *
     * @param  MaxLoginAttemptsExceeded  $event
     * @return void
     */

    public function handle(MaxLoginAttemptsExceeded $event)
    {
        $credentials = $event->credentials;
        $time = now()->timezone('Asia/Singapore'); //log in SGT (UTC+8)
        $ipAddress = request()->ip();

        // Find user by email
        //Explanation: At this point, the user isn't validated yet, hence,
        //can't directly get the userID.
        $user = User::where('email', $credentials['email'])->first();

        $logData = [
            //i.e, If user has an ID, get the ID, else, return null
            //Should never return null anyways, because you need a valid email to
            //login, and valid email to timeout
            'user_id' => $user ? $user->id : null,
            'time' => $time,
            'ip_address' => $ipAddress,
            'status' => 'Too many logins'
        ];


        Log::warning('Max login attempts exceeded', $logData);
    }
}
