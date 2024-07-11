<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Carbon\Carbon;

class TwoFactorController extends Controller
{
    public function show2faForm()
    {
        if (!Session::has('2fa:user:id')) {
            return redirect()->route('login');
        }

        $user = User::find(Session::get('2fa:user:id'));

        // Send OTP code via email directly from the controller
        $otpCode = $user->two_factor_code;
        Mail::raw("Your OTP code is: $otpCode. This code will expire in 2 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your OTP Code');
        });

        return view('auth.2fa', [
            'expires_at' => $user->two_factor_expires_at,
            'success_message' => 'An email has been sent with the OTP code.'
        ]);
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user = User::find(Session::get('2fa:user:id'));

        if ($user && $user->two_factor_code == $request->otp && Carbon::now()->lessThanOrEqualTo($user->two_factor_expires_at)) {
            $user->update(['two_factor_code' => null, 'two_factor_expires_at' => null]);
            Auth::loginUsingId($user->id);
            Session::forget('2fa:user:id');

            $request->session()->regenerate();

            // Update the session ID in the database
            $user->session_id = session()->getId();
            $user->save();

            // Ensure the user is authenticated and session is handled
            if (Auth::check()) {
                return redirect()->route('home');
            } else {
                return redirect()->route('login')->withErrors(['Authentication failed.']);
            }
        }

        return redirect()->route('2fa.index')->withErrors(['otp' => 'Invalid or expired OTP code.']);
    }


    public function resendOtp(Request $request)
    {
        $user = User::find(Session::get('2fa:user:id'));

        if ($user) {
            $otp = rand(100000, 999999);
            $user->update([
                'two_factor_code' => $otp,
                'two_factor_expires_at' => Carbon::now()->addMinutes(2)
            ]);

            // Send OTP email
            Mail::raw("Your OTP code is: $otp. This code will expire in 2 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your OTP Code');
            });

            return redirect()->route('2fa.index')->with('success', 'A new OTP has been sent to your email.');
        }

        return redirect()->route('login')->with('error', 'Something went wrong. Please try again.');
    }
}
