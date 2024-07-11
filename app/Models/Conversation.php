<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conversation) {
            $conversation->token = Str::uuid()->toString();
        });
    }

    protected $fillable = ['token'];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function otherParticipantUsername()
    {
        // Get the current user's ID
        $userId = Auth::id();

        // Retrieve the participants of the conversation
        $participants = $this->participants;

        // Loop through the participants
        foreach ($participants as $participant) {
            // Check if the participant's user ID is not the same as the current user's ID
            if ($participant->user_id !== $userId) {
                // Return the username of the other participant
                return $participant->user->name;
            }
        }

        // If no other participant is found, return null or handle accordingly
        return null;
    }

    public function getLastMessage()
    {
        // Get the last message associated with this conversation
        $lastMessage = $this->messages()->latest()->first();

        // Check if a last message exists
        if ($lastMessage) {
            // Return an array containing the message content and the timestamp
            return [
                'content' => $lastMessage->message,
                'sent_at' => $lastMessage->created_at->toDateTimeString(), // Adjust the format as needed
            ];
        } else {
            // If no last message exists, return null or handle accordingly
            return null;
        }
    }

    public function otherParticipantSecretKey()
    {
        // Get the current user's ID
        $userId = Auth::id();

        // Retrieve the participants of the conversation
        $participants = $this->participants;

        // Loop through the participants
        foreach ($participants as $participant) {
            // Check if the participant's user ID is not the same as the current user's ID
            if ($participant->user_id !== $userId) {
                // Return the secret key of the other participant
                return $participant->user->secret_key;
            }
        }

        // If no other participant is found, return null or handle accordingly
        return null;
    }

    function generateCombinedKey($key1, $key2)
    {
        // Sort the keys to ensure consistent concatenation order
        $keys = [$key1, $key2];
        sort($keys);

        return hash('sha256', $keys[0] . $keys[1]);
    }

    function encryptMessage($message, $combinedKey)
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($message, 'aes-256-cbc', $combinedKey, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    function decryptMessage($encryptedMessage, $combinedKey)
    {
        list($encrypted_data, $iv) = explode('::', base64_decode($encryptedMessage), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $combinedKey, 0, $iv);
    }
}
