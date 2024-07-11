<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class MessageController extends Controller
{
    public function sendMessage(Request $request, $token)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $conversation = Conversation::where('token', $token)->whereHas('participants', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $senderKey = Auth::user()->secret_key;
        $receiverKey = $conversation->otherParticipantSecretKey();

        $combinedKey = $conversation->generateCombinedKey($senderKey, $receiverKey);
        $encryptedMessage = $conversation->encryptMessage($request->message, $combinedKey);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'message' => $encryptedMessage,
            'sent_at' => \Carbon\Carbon::now()
        ]);

        // Increment the new messages count for all participants except the sender
        $conversation->participants()->where('user_id', '!=', Auth::id())->increment('new_messages_count');

        return redirect()->route('chat.chatwindow', ['token' => $conversation->token]);
    }
}
