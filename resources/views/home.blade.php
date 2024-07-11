@extends('layouts.app')

@section('content')

<div class="container d-flex flex-column justify-content-center align-items-center" style="height: 100vh;">
    {{-- Display success message --}}
    @if (session('success'))
        <div id="successMessage" class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Display error message --}}
    @if (session('error'))
        <div id="errorMessage" class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Display info message --}}
    @if (session('info'))
        <div id="infoMessage" class="alert alert-info" role="alert">
            {{ session('info') }}
        </div>
    @endif

    <div class="col-md-8 text-center">
        <h3 class="mb-4">Chats</h3>
        <div class="chat-list" style="overflow-y: auto; max-height: 60vh;">
            {{-- Check if user has no chats --}}
            @if ($conversations->isEmpty())
                <form action="{{ url('/searchresults') }}" method="GET">
                    <button class="btn btn-primary ms-2" type="submit">Start Chatting Now!</button>
                </form>
            @else
                {{-- Display existing chats --}}
                @foreach($conversations as $conversation)
                    <div class="chat-item-wrapper d-flex align-items-center justify-content-between mb-3">
                        <a href="{{ route('chat.chatwindow', $conversation->token) }}" class="chat-item flex-grow-1 position-relative">
                            <div class="chat-icon position-relative">
                                {{ strtoupper(substr($conversation->otherParticipantUsername(), 0, 1)) }}
                                @if($conversation->new_messages_count > 0)
                                    <span class="badge bg-danger badge-custom position-absolute top-0 start-100 translate-middle">{{ $conversation->new_messages_count }}</span>
                                @endif
                            </div>
                            <div class="chat-details">
                                <div class="chat-name">{{ $conversation->otherParticipantUsername() }}</div>
                                @php
                                    $lastMessage = $conversation->getLastMessage();
                                    $senderKey = Auth::user()->secret_key;
                                    $receiverKey = $conversation->otherParticipantSecretKey();
                                    $combinedKey = $conversation->generateCombinedKey($senderKey, $receiverKey);
                                @endphp
                                <div class="last-message">
                                    @if ($lastMessage)
                                        Last message: {{ $conversation->decryptMessage($lastMessage['content'], $combinedKey) }}
                                        <div class="text-muted">
                                            @php
                                                // Convert to Singapore time
                                                $sentAt = \Carbon\Carbon::parse($lastMessage['sent_at'])->timezone('Asia/Singapore');
                                            @endphp
                                            {{ $sentAt->format('H:i') }} {{-- Display Singapore time --}}
                                        </div>
                                    @else
                                        No messages
                                    @endif
                                </div>
                            </div>
                        </a>
                        <button class="btn btn-link p-0 m-0 delete-chat-btn ms-3" data-bs-toggle="modal" data-bs-target="#deleteChatModal-{{ $conversation->token }}">
                            <i class="fas fa-trash-alt text-danger"></i>
                        </button>
                    </div>
                    
                    <!-- Modal for confirming chat deletion -->
                    <div class="modal fade" id="deleteChatModal-{{ $conversation->token }}" tabindex="-1" aria-labelledby="deleteChatModalLabel-{{ $conversation->token }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteChatModalLabel-{{ $conversation->token }}">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this chat?
                                </div>
                                <div class="modal-footer">
                                    <form action="{{ route('chat.delete', ['token' => $conversation->token]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<style>
    .chat-list {
        padding: 0;
        max-width: 400px;
        margin: 0 auto;
        overflow-y: auto;
        padding-right: 20px;
    }

    .chat-list::-webkit-scrollbar {
        width: 8px;
    }

    .chat-list::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        transition: background-color 0.3s ease, width 0.3s ease;
    }

    .chat-list::-webkit-scrollbar-thumb:hover {
        background-color: rgba(0, 0, 0, 0.5);
        width: 12px;
    }

    .chat-item-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .chat-item {
        display: flex;
        align-items: left;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        text-decoration: none;
        color: #333;
        flex-grow: 1;
        margin-right: 10px;
        position: relative;
    }

    .chat-icon {
        width: 40px;
        height: 40px;
        background-color: #f0f0f0;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 15px;
        font-size: 18px;
        position: relative;
    }

    .badge-custom {
        font-size: 0.75rem;
        padding: 0.5em;
    }

    .chat-details {
        flex-grow: 1;
    }

    .chat-name {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .last-message {
        color: #777;
    }

    .text-muted {
        font-size: 12px;
    }

    .delete-chat-btn {
        margin-left: 10px;
    }
</style>

@endsection
