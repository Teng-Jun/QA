<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UserRegistered
{
    use Dispatchable, SerializesModels;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }
}
