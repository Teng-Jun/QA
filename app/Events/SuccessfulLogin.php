<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Auth;


//An event to handle Successful logins
class SuccessfulLogin
{
    use Dispatchable, SerializesModels;

    public $user;
    public $ipAddress;

    public function __construct($user, $ipAddress)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
    }
}