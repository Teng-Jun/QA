<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

// Events
use App\Events\SuccessfulLogin;
use App\Events\DuplicateLoginAttempted;
use App\Events\MaxLoginAttemptsExceeded;
// Logs
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use ThrottlesLogins;

    // Maximum number of login attempts
    protected $maxAttempts = 3;
    // Throttle duration in minutes
    protected $decayMinutes = 5;

    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function username()
    {
        return 'email';
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if there are too many login attempts
        if ($this->hasTooManyLoginAttempts($request)) {
            event(new MaxLoginAttemptsExceeded($credentials, $request->ip()));
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if (Auth::attempt($credentials)) {
            // Regenerate session ID to prevent session fixation attacks
            $request->session()->regenerate();

            // Reset the login attempts upon successful login
            $this->clearLoginAttempts($request);

            $user = Auth::user();

            // Check if the session already exists (i.e., user logged in somewhere else?)
            if ($user->session_id && $user->session_id !== session()->getId()) {
                // Log the user out of the previous session
                $previousSessionId = $user->session_id;
                $user->session_id = null;
                $user->save();

                Session::getHandler()->destroy($previousSessionId);
            }

            // Update the user's session_id with the new session ID
            $user->session_id = session()->getId();
            $user->save();

            // Check if the user has 2FA enabled
            if ($user->two_factor_enabled) {
                $otp = rand(100000, 999999);
                $user->update([
                    'two_factor_code' => $otp,
                    'two_factor_expires_at' => Carbon::now()->addMinutes(2)
                ]);

                // Store the user ID in session for OTP verification
                Session::put('2fa:user:id', $user->id);

                // Log the user out
                Auth::logout();

                return redirect()->route('2fa.index')->with('success', 'OTP sent to your email. The code will expire in 2 minutes.');
            }

            // Log successful login
            event(new SuccessfulLogin($user, $request->ip()));
            return redirect()->intended('/home');
        }

        // Note: LogFailedLogin automatically called here

        // Increment login attempts upon failed login
        $this->incrementLoginAttempts($request);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or you have logged in elsewhere',
        ]);
    }

    // Too many login attempts custom message (Without the timer)
    protected function sendLockoutResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => [__('Too many login attempts. Please try again later.')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->session_id = null;
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
