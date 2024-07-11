<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;

class HomeController extends Controller
{
    public function index()
    {
        // Retrieve conversations where the authenticated user is a participant
        $conversations = Conversation::whereHas('participants', function ($query) {
            $query->where('user_id', Auth::id());
        })->get();

        // Calculate the total number of unread messages
        $totalUnreadMessages = 0;
        foreach ($conversations as $conversation) {
            $participant = $conversation->participants()->where('user_id', Auth::id())->first();
            if ($participant) {
                $conversation->new_messages_count = $participant->new_messages_count;
                $totalUnreadMessages += $participant->new_messages_count;
            } else {
                $conversation->new_messages_count = 0;
            }
        }

        // Pass conversations and total unread messages to the view
        return view('home', compact('conversations', 'totalUnreadMessages'));
    }
}
