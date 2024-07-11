<?php 

namespace App\Listeners;

use App\Events\DuplicateLoginAttempted;
use Illuminate\Support\Facades\Log;

class LogDuplicateLogin
{
    /**
     * Handle the event.
     *
     * @param  DuplicateLoginAttempted  $event
     * @return void
     */
    public function handle(DuplicateLoginAttempted $event)
    {
        $user = $event->user;
        $time = now()->timezone('Asia/Singapore'); //log in SGT (UTC+8)
        $ipAddress = $event->ipAddress;

        Log::warning('Duplicate login attempt detected', [
            'user_id' => $user->id,
            'time' => $time,
            'ip_address' => $ipAddress,
            'status' => 'Duplicate login attempt'
        ]);
    }
}
