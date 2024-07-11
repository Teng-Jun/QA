<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class ForgetPasswordController extends Controller
{
    function forgetPassword()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view("password_reset.forget-password-view");
    }

    function forgetPasswordPost(Request $request)
    {
        // Check if the reCAPTCHA response is empty
        if (empty($request->input('g-recaptcha-response'))) {
            return redirect()->route('forget.password')->with('error', 'The reCAPTCHA field is required.');
        }

        $request->validate([
            'email' => "required|email",
        ]);

        $email = $request->email;
        $tokenExpirationMinutes = 15;

        // Check if a token exists for the provided email
        $existingToken = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if ($existingToken) {
            $tokenCreatedAt = Carbon::parse($existingToken->created_at);
            $timeDiffInMinutes = Carbon::now()->diffInMinutes($tokenCreatedAt);

            if (abs($timeDiffInMinutes) < $tokenExpirationMinutes) {
                return redirect()->to(route('forget.password'))
                    ->with('error', 'A password reset email has already been sent. Please check your email or try again later.');
            }

            // Delete the old token if 15 minutes have passed
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        Mail::send("password_reset.forget-password-email", ['token' => $token], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Reset Password');
        });

        return redirect()->to(route('forget.password'))
            ->with('success', 'We have sent an email to reset your password.');
    }

    function resetPassword($token)
    {
        // Fetch the reset token record from the database
        $resetToken = DB::table("password_reset_tokens")->where("token", $token)->first();

        // Check if the token is valid
        if (!$resetToken) {
            return redirect()->route("forget.password")->with("error", "Invalid request or token expired, please try again.");
        }

        // Check if the token has expired
        $tokenCreatedAt = Carbon::parse($resetToken->created_at);
        $tokenExpirationMinutes = 15; // Set your token expiration minutes here
        $timeDiffInMinutes = Carbon::now()->diffInMinutes($tokenCreatedAt);

        if (abs($timeDiffInMinutes) >= $tokenExpirationMinutes) {
            return redirect()->route("forget.password")->with("error", "The password reset token has expired. Please request a new password reset.");
        }
        return view('password_reset.new-password', compact('token'));
    }

    function resetPasswordPost(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(8), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*-+:;",<>.?])[A-Za-z\d!@#$%^&*-+:;",<>.?]+$/'],
            'password_confirmation' => "required"
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);

        // Fetch the reset token record from the database
        $updatePassword = DB::table("password_reset_tokens")->where([
            "token" => $request->token
        ])->first();

        // Check if the token is valid
        if (!$updatePassword) {
            return redirect()->route("reset.password", ['token' => $request->token])
                ->with("error", "Invalid request or token expired, please try again");
        }

        // Update the user's password
        User::where("email", $updatePassword->email)->update(["password" => Hash::make($request->password)]);

        // Delete the reset token record based on the token
        DB::table("password_reset_tokens")->where("token", $request->token)->delete();

        // DB::table("password_reset_tokens")->where(["email" => $request->email])->delete();

        return redirect()->to(route("login"))->with("success", "Password Reset Successful");
    }
}
