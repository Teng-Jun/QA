<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Participant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ChatController extends Controller
{
    public function createChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if a conversation already exists between these users
        $conversation = Conversation::whereHas('participants', function ($query) use ($request) {
            $query->where('user_id', Auth::id());
        })->whereHas('participants', function ($query) use ($request) {
            $query->where('user_id', $request->user_id);
        })->first();

        if (!$conversation) {
            // Create a new conversation
            $conversation = Conversation::create();

            // Add participants
            Participant::create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
            ]);

            Participant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $request->user_id,
            ]);
        }

        return redirect()->route('chat.chatwindow', ['token' => $conversation->token]);
    }

    public function show($token)
    {
        $conversation = Conversation::with('messages.user')->where('token', $token)->firstOrFail();

        if (!$conversation->participants->contains('user_id', Auth::id())) {
            abort(403, 'Unauthorized');
        }

        $senderKey = Auth::user()->secret_key;
        $receiverKey = $conversation->otherParticipantSecretKey();
        $combinedKey = $conversation->generateCombinedKey($senderKey, $receiverKey);

        foreach ($conversation->messages as $message) {
            $message->message = $conversation->decryptMessage($message->message, $combinedKey);
        }

        // Reset new messages count for the current user only when they view the conversation
        $conversation->participants()->where('user_id', Auth::id())->update(['new_messages_count' => 0]);

        return view('chat.chatwindow', compact('conversation'));
    }

    public function deleteChat($token)
    {
        // Retrieve the conversation
        $conversation = Conversation::where('token', $token)->firstOrFail();

        // Check if the authenticated user is a participant in the conversation
        if (!$conversation->participants->contains('user_id', Auth::id())) {
            abort(403, 'Unauthorized'); // Return a 403 Forbidden error if not authorized
        }

        // Delete the conversation
        $conversation->delete();

        // Redirect the user back to the home page
        return Redirect::route('home')->with('success', 'Chat deleted successfully.');
    }

    public function fetchMessages($token)
    {
        $conversation = Conversation::with('messages.user')->where('token', $token)->firstOrFail();

        // Check if the authenticated user is a participant in the conversation
        if (!$conversation->participants->contains('user_id', Auth::id())) {
            abort(403, 'Unauthorized'); // Return a 403 Forbidden error if not authorized
        }

        $senderKey = Auth::user()->secret_key;
        $receiverKey = $conversation->otherParticipantSecretKey();

        $combinedKey = $conversation->generateCombinedKey($senderKey, $receiverKey);

        // Decrypt each message
        foreach ($conversation->messages as $message) {
            $message->message = $conversation->decryptMessage($message->message, $combinedKey);
        }

        return view('chat.messages', ['messages' => $conversation->messages]);
    }

    public function getNewMessagesCount($token)
    {
        $conversation = Conversation::where('token', $token)->firstOrFail();

        if (!$conversation->participants->contains('user_id', Auth::id())) {
            abort(403, 'Unauthorized');
        }

        $newMessagesCount = $conversation->participants()->where('user_id', Auth::id())->value('new_messages_count');

        return response()->json(['newMessagesCount' => $newMessagesCount]);
    }

    public function resetNewMessagesCount($token)
    {
        $conversation = Conversation::where('token', $token)->firstOrFail();

        if (!$conversation->participants->contains('user_id', Auth::id())) {
            abort(403, 'Unauthorized');
        }

        $conversation->participants()->where('user_id', Auth::id())->update(['new_messages_count' => 0]);

        return response()->json(['success' => true]);
    }
}
