@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="container">
        <!-- empty query -->
        @if(empty($query) && count($randomUsers) > 0)
            <h3>Recommended People to Chat With:</h3>
            <div class="user-list custom-scrollbar" style="max-height: 50vh; overflow-y: auto;">
                <ul class="list-group">
                    @foreach ($randomUsers as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <!-- Column to display user info -->
                            <div class="d-flex align-items-center">
                                <div class="chat-icon">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                <h4 style="margin-bottom: 0;">
                                    <a href="#" style="color: black; text-decoration: none;">{{$user->name}}</a>
                                </h4>
                            </div>
                            <!-- Chat button -->
                            <form action="{{ url('/chat/create') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <button class="btn btn-outline-success" type="submit">
                                    Chat with {{$user->name}}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @elseif(empty($query))
            <h3>Empty Query! Enter a search term to look for users</h3>   
        <!-- less than 3 characters -->
        @elseif(strlen($query) < 3 )
            <h3>Please enter 3 characters or more!</h3>
        <!-- Display search results -->
        @elseif(count($users) > 0)
            <div class="user-list custom-scrollbar" style="max-height: 50vh; overflow-y: auto;">
                <ul class="list-group">
                    @foreach ($users as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <!-- Column to display user info -->
                            <div class="d-flex align-items-center">
                                <div class="chat-icon">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                <h4 style="margin-bottom: 0;">
                                    <a href="#" style="color: black; text-decoration: none;">{{$user->name}}</a>
                                </h4>
                            </div>
                            <!-- Chat button -->
                            <form action="{{ url('/chat/create') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <button class="btn btn-outline-success" type="submit">
                                    Chat with {{$user->name}}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        <!-- result if search query doesn't exist -->
        @elseif (($query))
            <h3>No matching user found for "{{ $query }}"</h3>
        @endif

        <!-- Back Button -->
        <div class="form-group row mt-3 text-center">
            <div class="col-md-8 offset-md-2">
                <a href="{{ url('/') }}" class="btn btn-secondary">Back</a>
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
</style>

@endsection
