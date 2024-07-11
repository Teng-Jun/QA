<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ProfileControllerTest extends TestCase
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

        // Login as test user
        $this->actingAs($this->user);
    }

    public function testUpdateProfile()
    {
        $response = $this->post(route('profile.update'), [
            'name' => 'Updated Test User'
        ]);

        $response->assertStatus(302);

        $this->user->refresh();
        $this->assertEquals('Updated Test User', $this->user->name);
    }

    public function testChangePassword()
    {
        $response = $this->post(route('profile.update-password'), [
            'current_password' => 'Password123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(302);

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $this->user->password));
    }

    public function testToggle2FA()
    {
        $response = $this->post(route('profile.toggle-2fa'));

        $response->assertStatus(302);

        $this->user->refresh();
        $this->assertTrue($this->user->two_factor_enabled);

        $response = $this->post(route('profile.toggle-2fa'));

        $response->assertStatus(302);

        $this->user->refresh();
        $this->assertFalse($this->user->two_factor_enabled);
    }

    public function testDestroyProfile()
    {
        $response = $this->post(route('profile.delete'), [
            'current_password' => 'Password123!',
        ]);

        $response->assertStatus(302);

        $this->assertNull(User::find($this->user->id));
    }

    public function testUpdateProfileWithInvalidData()
    {
        $response = $this->post(route('profile.update'), [
            'name' => '' // Invalid data
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        $this->user->refresh();
        $this->assertEquals('Test User', $this->user->name); // Name should remain unchanged
    }

    public function testUpdateProfileWithoutChanges()
    {
        $response = $this->post(route('profile.update'), [
            'name' => 'Test User' // No changes
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('info', 'No changes were made to your profile.');

        $this->user->refresh();
        $this->assertEquals('Test User', $this->user->name); // Name should remain unchanged
    }

    public function testChangePasswordWithIncorrectCurrentPassword()
    {
        $response = $this->post(route('profile.update-password'), [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('current_password');

        $this->user->refresh();
        $this->assertTrue(Hash::check('Password123!', $this->user->password)); // Password should remain unchanged
    }

    public function testChangePasswordWithSameNewPassword()
    {
        $response = $this->post(route('profile.update-password'), [
            'current_password' => 'Password123!',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $this->user->refresh();
        $this->assertTrue(Hash::check('Password123!', $this->user->password)); // Password should remain unchanged
    }

    public function testUpdateProfileThrottling()
    {
        // First update
        $response = $this->post(route('profile.update'), [
            'name' => 'Updated Test User'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->user->refresh();
        $this->assertEquals('Updated Test User', $this->user->name);

        // Attempt second update within 24 hours
        $response = $this->post(route('profile.update'), [
            'name' => 'Another Test User'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'You can only update your profile once after 24 hours.');

        $this->user->refresh();
        $this->assertEquals('Updated Test User', $this->user->name); // Name should remain unchanged
    }

    public function testDestroyProfileWithIncorrectPassword()
    {
        $response = $this->post(route('profile.delete'), [
            'current_password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'The provided password does not match your current password.');

        $this->assertNotNull(User::find($this->user->id)); // User should not be deleted
    }
}
