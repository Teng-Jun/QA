<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterControllerTest extends TestCase
{
    public function testPasswordValidation()
    {
        $controller = new RegisterController();

        // Create requests with various invalid passwords and assert the expected validation errors

        // Test with a password that is too short
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        try {
            $controller->register($request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('password', $errors);
        }

        // Test with a password missing an uppercase letter
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password1!',
            'password_confirmation' => 'password1!',
        ]);

        try {
            $controller->register($request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('password', $errors);
            $this->assertEquals('The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.', $errors['password'][0]);
        }

        // Test with a password missing a special character
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        try {
            $controller->register($request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('password', $errors);
            $this->assertEquals('The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.', $errors['password'][0]);
        }

        // Test with a valid password
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        try {
            $controller->register($request);
        } catch (ValidationException $e) {
            $this->fail('Valid password should not trigger a validation exception.');
        }

        $this->assertTrue(true, 'Valid password passes validation.');
    }
}
