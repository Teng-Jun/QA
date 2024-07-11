<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginUITest extends TestCase
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
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        User::where('email', 'testuser@example.com')->delete();

        parent::tearDown();
    }

    /**
     * Test a valid login attempt.
     *
     * @return void
     */
    public function testValidLogin()
    {
        $email = 'testuser@example.com';
        $password = 'Password123!';

        echo "Testing valid login with email: $email\n";

        $response = $this->post('login', [
            'email' => $email,
            'password' => $password
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticated(); // Check if user is authenticated
    }

    /**
     * Test an invalid login attempt.
     *
     * @return void
     */
    public function testInvalidLogin()
    {
        $email = 'nonexistent@example.com';
        $password = 'Password123!';

        echo "Testing invalid login with email: $email\n";

        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password
        ]);

        $response->assertStatus(302);
        $this->assertGuest(); // Check if user is not authenticated
    }

    /**
     * Test a logout attempt.
     *
     * @return void
     */
    public function testLogout()
    {
        // Log in the user first
        $this->actingAs($this->user);

        $response = $this->post('/logout');

        $response->assertStatus(302);
        $this->assertGuest(); // Check if user is logged out
    }
}
