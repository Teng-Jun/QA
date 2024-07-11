<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Mail\Mailable;

class RegistrationTest extends TestCase
{
    /**
     * Test a valid registration process.
     *
     * @return void
     */
    public function testValidRegistration()
    {
        // Mock email sending
        Mail::fake();

        // Delete existing user if any
        User::where('email', 'testuser@example.com')->delete();

        // Register a user
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'g-recaptcha-response' => 'dummy-captcha-response',
        ]);

        $response->assertStatus(302);

        // Check if user data is stored in cache
        $token = Cache::get('test_user_registration_token');
        $this->assertNotNull($token);

        $userData = Cache::get('register_' . $token);
        $this->assertEquals('testuser@example.com', $userData['email']);
    }

    /**
     * Test email verification process.
     *
     * @return void
     */
    public function testEmailVerification()
    {
        // Delete existing user if any
        User::where('email', 'testuser@example.com')->delete();

        // Store user data in cache
        $verificationToken = Str::random(64);
        Cache::put('register_' . $verificationToken, [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
            'secret_key' => Str::random(16),
        ], now()->addMinutes(15));

        // Verify email
        $response = $this->get('/verify-email/' . $verificationToken);
        $response->assertStatus(302);

        // Check if user data is moved from cache to database
        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    /**
     * Test invalid email verification process.
     *
     * @return void
     */
    public function testInvalidEmailVerification()
    {
        // Attempt to verify email with an invalid token
        $response = $this->get('/verify-email/invalid-token');
        $response->assertStatus(302);

        $response->assertSessionHas('error', 'Invalid or expired verification link.');
    }
}
