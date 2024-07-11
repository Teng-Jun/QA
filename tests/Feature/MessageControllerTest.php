<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Participant;
use Illuminate\Support\Facades\Hash;

class MessageControllerTest extends TestCase
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
            'secret_key' => 'secretkey1'
        ]);

        $this->user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'testuser2@example.com',
            'password' => Hash::make('Password123!'),
            'secret_key' => 'secretkey2'
        ]);

        // Log in as the first test user
        $this->actingAs($this->user1);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        Message::where('user_id', $this->user1->id)->delete();
        Message::where('user_id', $this->user2->id)->delete();
        Participant::whereIn('user_id', [$this->user1->id, $this->user2->id])->delete();
        Conversation::whereHas('participants', function ($query) {
            $query->whereIn('user_id', [$this->user1->id, $this->user2->id]);
        })->delete();
        User::where('email', 'testuser1@example.com')->delete();
        User::where('email', 'testuser2@example.com')->delete();

        parent::tearDown();
    }

    public function testSendMessage()
    {
        $conversation = Conversation::create();
        $conversation->participants()->createMany([
            ['user_id' => $this->user1->id],
            ['user_id' => $this->user2->id],
        ]);

        $response = $this->post(route('chat.message', ['token' => $conversation->token]), [
            'message' => 'Hello, this is a test message.',
        ]);

        $response->assertStatus(302);

        $combinedKey = $conversation->generateCombinedKey($this->user1->secret_key, $this->user2->secret_key);
        $encryptedMessage = $conversation->encryptMessage('Hello, this is a test message.', $combinedKey);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $this->user1->id
        ]);
    }

    public function testFetchMessages()
    {
        $conversation = Conversation::create();
        $conversation->participants()->createMany([
            ['user_id' => $this->user1->id],
            ['user_id' => $this->user2->id],
        ]);

        $combinedKey = $conversation->generateCombinedKey($this->user1->secret_key, $this->user2->secret_key);
        $encryptedMessage = $conversation->encryptMessage('Test message', $combinedKey);

        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user1->id,
            'message' => $encryptedMessage,
        ]);

        $response = $this->get(route('chat.messages', ['token' => $conversation->token]));

        $response->assertStatus(200);
        $response->assertViewIs('chat.messages');
        $response->assertSee('Test message');
    }
}
