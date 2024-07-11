<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;

//Note: Doesn't have a corresponding event, as this is using Laravel's inbuilt functionality

class LogFailedLogin
{
    //To count how many times the user failed to login
    use ThrottlesLogins;

    /**
     * Handle failed logins.
     *
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {

        $credentials = $event->credentials;
        $time = now()->timezone('Asia/Singapore'); //log in SGT (UTC+8)
        $ipAddress = request()->ip();


        //To see how many times the dude failed to login
        $request = request();
        $throttleKey = $this->throttleKey($request);
        $attempts = $this->limiter()->attempts($throttleKey);
        //$attempts +1 to make the attempts start from 1 (Readability purposes)
        $attempts += 1;

        // If the user enters correct email, wrong password
        if ($event->user) {
            Log::warning('Login failed', [
                'user_id' => $event->user->id,
                'time' => $time,
                'ip_address' => $ipAddress,
                'attempts' => $attempts,
                'status' => 'Failed login attempt (UID captured)'
            ]);
        //if the user enters incorrect email that doesn't exist    
        } else {
            Log::warning('Login failed', [
                'email' => $credentials['email'],
                'time' => $time,
                'ip_address' => $ipAddress,
                'attempts' => $attempts,
                'status' => 'Failed login attempt (Email captured)'
            ]);
        }
    }
    /**
     * Get the throttle key for the given request.
     * for logging purposes one
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return strtolower($request->input('email')).'|'.$request->ip();
    }
}