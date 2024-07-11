<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class CookieController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->session_id !== session()->getId()) {
                    Auth::logout();
                    return redirect('/login')->withErrors('Your session has been invalidated.');
                }
            }
            return $next($request);
        });
    }
}