<?php

namespace App\Listeners;

use App\Events\SuccessfulLogin as EventsSuccessfulLogin;
use Illuminate\Auth\Events\SuccessfulLogin;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * Handle success logins.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(EventsSuccessfulLogin $event)
    {
        $user = $event->user;
        $time = now()->timezone('Asia/Singapore'); //log in SGT (UTC+8)
        $ipAddress = request()->ip();
        Log::info('User logged in', [
            'user_id' => $user->id,
            'time' => $time,
            'ip_address' => $ipAddress,
            'status' => 'Successful login'
        ]);
    }
}