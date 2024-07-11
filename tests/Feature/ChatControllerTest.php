<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Participant;
use Illuminate\Support\Facades\Hash;

class ChatControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure test users are deleted before each test
        User::where('email', 'testuser1@example.com')->delete();
        User::where('email', 'testuser2@example.com')->delete();

        // Create test users
        $this->user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'testuser1@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'testuser2@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Log in as the first test user
        $this->actingAs($this->user1);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        Participant::whereIn('user_id', [$this->user1->id, $this->user2->id])->delete();
        Conversation::whereHas('participants', function ($query) {
            $query->whereIn('user_id', [$this->user1->id, $this->user2->id]);
        })->delete();
        User::where('email', 'testuser1@example.com')->delete();
        User::where('email', 'testuser2@example.com')->delete();

        parent::tearDown();
    }

    public function testCreateChat()
    {
        $response = $this->post(route('chat.create'), [
            'user_id' => $this->user2->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('conversations', []); // Check that conversation is created
        $this->assertDatabaseHas('participants', [
            'user_id' => $this->user1->id,
        ]);
        $this->assertDatabaseHas('participants', [
            'user_id' => $this->user2->id,
        ]);
    }

    public function testShowChatWindow()
    {
        $conversation = Conversation::create();
        $conversation->participants()->createMany([
            ['user_id' => $this->user1->id],
            ['user_id' => $this->user2->id],
        ]);

        $response = $this->get(route('chat.chatwindow', ['token' => $conversation->token]));

        $response->assertStatus(200);
        $response->assertViewIs('chat.chatwindow');
    }

    public function testDeleteChat()
    {
        $conversation = Conversation::create();
        $conversation->participants()->createMany([
            ['user_id' => $this->user1->id],
            ['user_id' => $this->user2->id],
        ]);

        $response = $this->delete(route('chat.delete', ['token' => $conversation->token]));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
    }
}

