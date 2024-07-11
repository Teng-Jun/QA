<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Pixiegram') }}</title>

    <!-- Bootstrap Styles -->
    <link href="{{ secure_asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"/>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Captcha v2 -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Custom Styles -->
    <style>
        .navbar-brand {
            font-size: 24px;
            margin-left: 20px;
        }

        #successMessage, #errorMessage, #infoMessage {
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: auto;
            max-width: 300px;
            transition: opacity 0.5s ease-in-out;
        }
        .fade-in {
            opacity: 1;
        }

        .fade-out {
            opacity: 0;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fw-bold bg-light fixed-top">
        <div class="container-fluid align-items-center">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'Pixiegram') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <!-- Search Bar -->
                        <form class="d-flex" action="{{ url('/searchresults') }}" method="GET">
                            <input class="form-control me-2" type="search" name="query" placeholder="Start a Chat with.." aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                        <!-- Chats Button with Notification Badge -->
                        <a href="{{ url('/') }}" class="btn btn-outline-primary position-relative ms-3">
                            Chats
                            @if($totalUnreadMessages > 0)
                                <span class="badge bg-danger badge-custom">{{ $totalUnreadMessages }}</span>
                            @endif
                        </a>
                    @endauth
                </ul>
                
                @auth
                    <!-- User Dropdown -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ url('profile/edit') }}">Edit Profile</a></li>
                                <li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    <div id="app">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Function to show and hide messages with fade-in and fade-out effect
        document.addEventListener('DOMContentLoaded', function() {
            var successMessage = document.getElementById('successMessage');
            var errorMessage = document.getElementById('errorMessage');
            var infoMessage = document.getElementById('infoMessage');

            // Function to handle the fading
            function handleFade(messageElement) {
                messageElement.classList.add('fade-in');
                setTimeout(function() {
                    messageElement.classList.remove('fade-in');
                    messageElement.classList.add('fade-out');
                    setTimeout(function() {
                        messageElement.style.display = 'none';
                    }, 500); // Match the duration of the fade-out transition
                }, 3000); // Display for 3 seconds before fading out
            }

            // Show messages with fade-in effect
            if (successMessage) {
                successMessage.style.display = 'block';
                handleFade(successMessage);
            }

            if (errorMessage) {
                errorMessage.style.display = 'block';
                handleFade(errorMessage);
            }

            if (infoMessage) {
                infoMessage.style.display = 'block';
                handleFade(infoMessage);
            }
        });

        // Auto logout after 15 minutes of inactivity
        let logoutTime = 15 * 60 * 1000; // 15 minutes in milliseconds
        let warningTime = logoutTime - (1 * 60 * 1000); // 1 minute before logout
        let timeout;
        let warningTimeout;

        function resetTimer() {
            clearTimeout(timeout);
            clearTimeout(warningTimeout);
            timeout = setTimeout(logout, logoutTime);
            warningTimeout = setTimeout(showLogoutWarning, warningTime);
        }

        function showLogoutWarning() {
            if (confirm("You will be logged out soon due to inactivity. Do you want to extend your session?")) {
                resetTimer();
            } else {
                logout();
            }
        }

        function logout() {
            document.getElementById('logout-form').submit();
        }

        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;

        // Log out when the user closes the browser/tab
        window.addEventListener('beforeunload', function (e) {
            navigator.sendBeacon('/logout', JSON.stringify({
                _token: '{{ csrf_token() }}'
            }));
        });
    </script>
</body>
</html>
