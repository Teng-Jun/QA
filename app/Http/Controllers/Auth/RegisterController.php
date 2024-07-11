<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Events\UserRegistered;
use App\Mail\VerifyEmail;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function register(Request $request)
    {
        // Check if the reCAPTCHA response is empty
        if (empty($request->input('g-recaptcha-response'))) {
            return redirect()->route('register')->with('error', 'The reCAPTCHA field is required.');
        }

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(8), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*-+:;",<>.?])[A-Za-z\d!@#$%^&*-+:;",<>.?]+$/'],
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);

        // Hash the password
        $hashedPassword = Hash::make($validatedData['password']);

        // Generate a unique verification token
        $verificationToken = Str::random(64);

        // Fire the event
        event(new UserRegistered($verificationToken));

        // Store user data in cache with a 10-minute expiration
        Cache::put('register_' . $verificationToken, [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $hashedPassword,
            'secret_key' => Str::random(16),
        ], now()->addMinutes(15));

        // Generate the verification link
        $verificationLink = route('verify.email', ['token' => $verificationToken]);

        // // Send verification email
        // $emailContent = "Dear User,\n\nPlease verify your email address by clicking the link below:\n\n";
        // $emailContent .= $verificationLink . "\n\n";
        // $emailContent .= "If you did not request this verification, please disregard this email.\n\n";
        // $emailContent .= "Thank you,\nPixiegram";

        // Mail::raw($emailContent, function ($message) use ($validatedData) {
        //     $message->to($validatedData['email'])
        //         ->subject('Verify Your Email Address');
        // });

        // Create the HTML content
        $emailContent = "
            <p>Dear User,</p>
            <p>Please verify your email address by clicking the link below:</p>
            <p><a href=\"{$verificationLink}\">Verify Email Address</a></p>
            <p>If you did not request this verification, please disregard this email.</p>
            <p>Thank you,<br>Pixiegram</p>
        ";

        // Send verification email
        Mail::html($emailContent, function ($message) use ($validatedData) {
            $message->to($validatedData['email'])
                ->subject('Verify Your Email Address');
        });

        // Redirect to login page with success message
        return redirect()->route('login')->with('success', 'A verification email has been sent to your email address.');
    }

    /**
     * Handle email verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyEmail(Request $request, $token)
    {
        $userData = Cache::get('register_' . $token);

        if (!$userData) {
            return redirect()->route('register')->with('error', 'Invalid or expired verification link.');
        }

        // Create a new user record
        $user = User::create($userData);

        // Clear the temporary user data from cache
        Cache::forget('register_' . $token);

        // Redirect to login page with success message
        return redirect()->route('login')->with('success', 'Your account has been verified and created successfully!');
    }
}
