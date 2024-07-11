<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ForgetPasswordControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the test user is deleted before each test
        User::where('email', 'testuser@example.com')->delete();

        // Create a test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        Mail::fake();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        User::where('email', 'testuser@example.com')->delete();
        DB::table('password_reset_tokens')->where('email', 'testuser@example.com')->delete();

        parent::tearDown();
    }

    public function testForgetPasswordView()
    {
        $response = $this->get(route('forget.password'));
        $response->assertStatus(200);
        $response->assertViewIs('password_reset.forget-password-view');
    }

    public function testForgetPasswordPost()
    {
        $response = $this->post(route('forget.password.post'), [
            'email' => 'testuser@example.com',
            'g-recaptcha-response' => 'dummy-response'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'We have sent an email to reset your password.');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'testuser@example.com'
        ]);
    }

    public function testForgetPasswordPostWithoutCaptcha()
    {
        $response = $this->post(route('forget.password.post'), [
            'email' => 'testuser@example.com'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'The reCAPTCHA field is required.');
    }

    public function testResetPasswordViewWithValidToken()
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => 'testuser@example.com',
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->get(route('reset.password', ['token' => $token]));

        $response->assertStatus(200);
        $response->assertViewIs('password_reset.new-password');
        $response->assertViewHas('token', $token);
    }

    public function testResetPasswordViewWithInvalidToken()
    {
        $response = $this->get(route('reset.password', ['token' => 'invalid-token']));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Invalid request or token expired, please try again.');
    }

    public function testResetPasswordPostWithValidToken()
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => 'testuser@example.com',
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->post(route('reset.password.post'), [
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Password Reset Successful');

        $this->assertTrue(Hash::check('NewPassword123!', $this->user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'token' => $token
        ]);
    }

    public function testResetPasswordPostWithInvalidToken()
    {
        $response = $this->post(route('reset.password.post'), [
            'token' => 'invalid-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Invalid request or token expired, please try again');
    }
}
