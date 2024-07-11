<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TwoFactorControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the test user is deleted before each test
        User::where('email', 'testuser@example.com')->delete();

        // Create a new test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('Password123!'),
            'two_factor_code' => '123456',
            'two_factor_expires_at' => Carbon::now()->addMinutes(2),
        ]);

        // Mock the email sending
        Mail::fake();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        User::where('email', 'testuser@example.com')->delete();

        parent::tearDown();
    }

    public function testShow2faForm()
    {
        Session::put('2fa:user:id', $this->user->id);

        $response = $this->get(route('2fa.index'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.2fa');
        $response->assertSee('Enter OTP');
    }

    public function testVerify2fa()
    {
        Session::put('2fa:user:id', $this->user->id);

        $response = $this->post(route('2fa.verify'), [
            'otp' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));

        $this->assertNull($this->user->fresh()->two_factor_code);
        $this->assertNull($this->user->fresh()->two_factor_expires_at);
        $this->assertTrue(Auth::check());
    }

    public function testVerify2faWithInvalidOtp()
    {
        Session::put('2fa:user:id', $this->user->id);

        $response = $this->post(route('2fa.verify'), [
            'otp' => '654321',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('2fa.index'));
        $response->assertSessionHasErrors('otp');

        $this->assertNotNull($this->user->fresh()->two_factor_code);
        $this->assertNotNull($this->user->fresh()->two_factor_expires_at);
        $this->assertFalse(Auth::check());
    }

    public function testResendOtp()
    {
        Session::put('2fa:user:id', $this->user->id);

        $response = $this->post(route('2fa.resend'));

        $response->assertStatus(302);
        $response->assertRedirect(route('2fa.index'));
        $response->assertSessionHas('success', 'A new OTP has been sent to your email.');

        $newOtp = $this->user->fresh()->two_factor_code;
        $this->assertNotEquals('123456', $newOtp);
    }
}
