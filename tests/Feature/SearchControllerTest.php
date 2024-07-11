<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SearchControllerTest extends TestCase
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
            'password' => bcrypt('Password123!'),
        ]);

        // Login as test user
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        User::where('email', 'testuser@example.com')->delete();
        User::where('email', 'anotheruser@example.com')->delete(); // Ensure cleanup of additional users

        parent::tearDown();
    }

    public function testSearchWithQuery()
    {
        $response = $this->get(route('searchresults', ['query' => 'Test']));

        $response->assertStatus(200);
        $response->assertSee('Test User');
    }

    public function testSearchWithoutQuery()
    {
        // Create additional users for randomness
        User::create([
            'name' => 'Another User',
            'email' => 'anotheruser@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->get(route('searchresults'));

        $response->assertStatus(200);
        $response->assertSee('Recommended People to Chat With:');
        $response->assertSee('Test User');
    }

    public function testSearchWithShortQuery()
    {
        $response = $this->get(route('searchresults', ['query' => 'Te']));

        $response->assertStatus(200);
        $response->assertSee('Please enter 3 characters or more!');
    }

    public function testSearchWithNoMatchingUser()
    {
        $response = $this->get(route('searchresults', ['query' => '']));

        $response->assertStatus(200);
        $response->assertSee('Recommended People to Chat With');
    }
}
