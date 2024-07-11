@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="row justify-content-center flex-grow-1">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ $conversation->otherParticipantUsername() }}</div>

                <div class="card-body d-flex flex-column h-100 p-3 justify-content-end position-relative">
                    <div id="messages" class="messages mb-3 custom-scrollbar" style="flex-grow: 1; max-height: 60vh; overflow-y: auto;">
                        @include('chat.messages', ['messages' => $conversation->messages])
                    </div>
                    <button id="scrollToBottom" class="btn btn-primary rounded-circle position-absolute" style="bottom: 80px; left: 50%; transform: translateX(-50%); width: 40px; height: 40px; position: relative;">
                        <i class="fas fa-arrow-down"></i>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle p-2 border border-light rounded-circle"></span>
                    </button>
                    <form id="messageForm" action="{{ route('chat.message', ['token' => $conversation->token]) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" id="message" name="message" class="form-control" placeholder="Type a message.." required maxlength="2000">
                            <button class="btn btn-primary rounded-circle ms-2" type="submit">
                                <i class="fas fa-arrow-right text-white"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Buttons for chat actions -->
        <div class="mt-3 text-center">
            <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteChatModal">Delete Chat</button>
            <a href="{{ url('/') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

<!-- Modal for confirming chat deletion -->
<div class="modal fade" id="deleteChatModal" tabindex="-1" aria-labelledby="deleteChatModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteChatModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this chat?
            </div>
            <div class="modal-footer">
                <form id="deleteChatForm" action="{{ route('chat.delete', ['token' => $conversation->token]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
    .chat-list {
        padding: 0;
        max-width: 600px;
        margin: 0 auto;
    }

    .chat-item {
        display: flex;
        align-items: left;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 20px;
        text-decoration: none;
        color: #333;
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

    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        transition: background-color 0.3s ease, width 0.3s ease;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: rgba(0, 0, 0, 0.5);
        width: 12px;
    }

    #scrollToBottom {
        display: none;
        line-height: 1;
        padding: 0;
    }
    
    .badge {
        font-size: 0.75rem;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var messagesDiv = document.getElementById('messages');
    var scrollToBottomBtn = document.getElementById('scrollToBottom');
    var token = '{{ $conversation->token }}';
    var isUserAtBottom = true;

    function scrollToBottom() {
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function updateBadge() {
        $.ajax({
            url: `/chat/${token}/new-messages-count`,
            method: 'GET',
            success: function(data) {
                var badge = scrollToBottomBtn.querySelector('.badge');
                if (data.newMessagesCount > 0) {
                    badge.textContent = data.newMessagesCount;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        });
    }

    function fetchMessages(immediate = false) {
        var currentScrollPosition = messagesDiv.scrollTop;

        $.ajax({
            url: '{{ route("chat.messages", ["token" => $conversation->token]) }}',
            method: 'GET',
            success: function(data) {
                $('#messages').html(data);

                if (immediate) {
                    scrollToBottom();
                } else {
                    messagesDiv.scrollTop = localStorage.getItem('scrollPosition') || currentScrollPosition;
                }
                
                // Update badge after fetching messages
                updateBadge();
            }
        });
    }

    function resetNewMessagesCount() {
        $.ajax({
            url: `/chat/${token}/reset-new-messages-count`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function() {
                updateBadge();
            }
        });
    }

    setInterval(function() {
        localStorage.setItem('scrollPosition', messagesDiv.scrollTop);
        fetchMessages();
    }, 5000);

    $('#messageForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("chat.message", ["token" => $conversation->token]) }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function() {
                $('#message').val('');
                fetchMessages(true); // Fetch messages immediately on new message
            }
        });
    });

    messagesDiv.addEventListener('scroll', function() {
        if (messagesDiv.scrollTop + messagesDiv.clientHeight < messagesDiv.scrollHeight - 10) {
            scrollToBottomBtn.style.display = 'block';
            isUserAtBottom = false;
        } else {
            scrollToBottomBtn.style.display = 'none';
            isUserAtBottom = true;

            // Reset the new messages count when scrolled to the bottom
            resetNewMessagesCount();
        }
    });

    scrollToBottomBtn.addEventListener('click', function() {
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    });

    // Scroll to the bottom when the page loads
    scrollToBottom();
});
</script>

@endsection
