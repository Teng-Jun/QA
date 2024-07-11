<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
//events
use App\Events\DuplicateLoginAttempted;
use App\Events\MaxLoginAttemptsExceeded;
use App\Events\SuccessfulLogin;

//Listeners
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogFailedLogin;
use App\Listeners\LogDuplicateLogin;
use App\Listeners\LogMaxExceeded;


class EventServiceProvider extends ServiceProvider
{
    //Note: Top XX::class = under events, the classname
    //Bottom XX::class = Under listeners, also classname
    protected $listen = [
        SuccessfulLogin::class => [
            LogSuccessfulLogin::class,
        ],
        DuplicateLoginAttempted::class => [
            LogDuplicateLogin::class,
         ],
        Failed::class => [
            LogFailedLogin::class,
        ],
        MaxLoginAttemptsExceeded::class => [
            LogMaxExceeded::class,
         ],
        \App\Events\UserRegistered::class => [
            \App\Listeners\CaptureTokenListener::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
