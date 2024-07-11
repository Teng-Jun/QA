<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

//To track when max attempts exceeded + attempt to login again
class MaxLoginAttemptsExceeded
{
    use Dispatchable, SerializesModels;

    public $credentials;
    public $ipAddress;

    public function __construct($credentials, $ipAddress)
    {
        $this->credentials = $credentials;
        $this->ipAddress = $ipAddress;
    }
}