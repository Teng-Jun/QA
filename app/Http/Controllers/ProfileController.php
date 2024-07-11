<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Participant;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Mail;
use DB;

class ProfileController extends Controller
{
    public function loadProfile()
    {
        // Get the currently authenticated user
        $user = Auth::user();
        return view('profile.view-profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        // Retrieve the currently authenticated user
        $user = Auth::user();

        // New array to check for updates
        $updates = [];

        // Check if the name needs to be updated
        if ($validatedData['name'] !== $user->name) {
            // Check if the new name is already taken
            if (User::where('name', $validatedData['name'])->exists()) {
                return back()->withErrors(['name' => 'The provided name is already taken.']);
            }
            $updates['name'] = $validatedData['name'];
        }

        // No changes are made if neither the name nor the 2FA is altered
        if (empty($updates)) {
            return back()->with('info', 'No changes were made to your profile.');
        }

        // Check if the profile was updated in the last 24 hour
        if ($user->last_profile_update && $user->last_profile_update->diffInHours(now()) < 24) {
            return back()->with(['error' => 'You can only update your profile once after 24 hours.']);
        }

        // Update the user profile
        $user->update($updates);

        // Issue a timestamp for the update
        $user->last_profile_update = now();
        $user->save();

        $emailContent = "
            <p>Dear User,</p>
            <p>This is a confirmation that your account name has been successfully updated.</p>
            <p>If you did not make this change, please contact our support team immediately.</p>
            <p>Thank you,<br>Pixiegram</p>
        ";

        // Send the name update confirmation email
        Mail::html($emailContent, function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Name Change Confirmation');
        });

        return redirect()->route('logout')->with('success', 'Profile updated successfully.');
    }

    public function toggle2FA(Request $request)
    {
        $user = Auth::user();

        // Toggle the 2FA status
        $user->two_factor_enabled = !$user->two_factor_enabled;
        $user->save();

        // Set the appropriate success or failure message
        if ($user->two_factor_enabled) {
            return redirect()->route('profile.edit')->with('success', '2FA enabled successfully.');
        } else {
            return redirect()->route('profile.edit')->with('success', '2FA disabled successfully.');
        }
    }

    public function showChangePasswordForm()
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'current_password' => ['required', 'string', Rules\Password::min(8)],
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(8), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*-+:;",<>.?])[A-Za-z\d!@#$%^&*-+:;",<>.?]+$/'],
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);

        // Retrieve the currently authenticated user
        $user = Auth::user();

        // Check if the current password is correct
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        // Check if the new password is the same as the current password
        if (Hash::check($validatedData['password'], $user->password)) {
            return back()->withErrors(['password' => 'The new password cannot be the same as the current password.']);
        }

        // Check if the last profile update (used for password updates too) was more than 24 hours
        if ($user->last_profile_update && $user->last_profile_update->diffInHours(now()) < 24) {
            return back()->withErrors(['password' => 'You can only update your password once after 24 hours.']);
        }

        // Update the user's password
        $user->password = Hash::make($validatedData['password']);
        $user->last_profile_update = now();
        $user->save();

        $emailContent = "
            <p>Dear User,</p>
            <p>This is a confirmation that your password has been successfully updated.</p>
            <p>If you did not make this change, please contact our support team immediately.</p>
            <p>Thank you,<br>Pixiegram</p>
        ";

        // Send the password update confirmation email
        Mail::html($emailContent, function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Update Confirmation');
        });

        return redirect()->route('logout')->with('success', 'Password updated successfully.');
    }

    public function destroyProfile(Request $request)
    {
        // Validate the input
        $request->validate([
            'current_password' => 'required',
        ]);

        $user = Auth::user();

        // Check if the entered password matches the current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'The provided password does not match your current password.');
        }

        // Start transaction to ensure all-or-nothing deletion
        DB::beginTransaction();

        try {
            // Get all conversation IDs from participants table where user ID matches
            $conversationIds = Participant::where('user_id', $user->id)->pluck('conversation_id');

            // Delete entries from participants table where user ID matches
            Participant::where('user_id', $user->id)->delete();

            // Delete all messages where user ID matches
            Message::where('user_id', $user->id)->delete();

            // Delete all conversations based on the retrieved conversation IDs
            Conversation::whereIn('id', $conversationIds)->delete();

            // Delete the user account
            $user->delete();

            // Commit the transaction
            DB::commit();

            // Redirect to login page with success message
            return redirect('/login')->with('success', 'Account deleted successfully.');

        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while deleting your account. Please try again.');
        }
    }
}
