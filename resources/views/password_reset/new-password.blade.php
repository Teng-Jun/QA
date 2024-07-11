@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-md-8">
            <div class="card">
                {{-- Display error message --}}
                @if (session('error'))
                    <div id="errorMessage" class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Display success message --}}
                @if (session('success'))
                    <div id="successMessage" class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card-header text-center bg-primary text-white">
                    <h4>{{ __('New Password') }}</h4>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('reset.password.post') }}">
                        @csrf
                        <input type="text" name="token" hidden value="{{ $token }}">
                        {{-- If using cache --}}
                        {{-- <input type="text" name="email" hidden value="{{ $email }}"> --}}
                        <div class="form-group row mb-2">
                            {{-- <label for="email"
                                class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> --}}

                            <div class="form-group row mb-2">
                                <label for="password"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Enter new password') }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input id="password" type="password"
                                            class="form-control @error('password') is-invalid @enderror" name="password">

                                        <div class="input-group-append">
                                            <span id="togglePassword" class="input-group-text toggle-password"
                                                style="display: flex; align-items: center; justify-content: center; width: 40px; height: 34px;">
                                                <i class="far fa-eye"></i>
                                            </span>
                                        </div>

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="progress mt-2">
                                        <div id="progress-bar" class="progress-bar"></div>
                                    </div>
                                    <p id="strength-text"></p>
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <label for="password-confirm"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input id="password-confirm" type="password" class="form-control"
                                            name="password_confirmation">
                                        <div class="input-group-append">
                                            <span id="toggleConfirmPassword" class="input-group-text toggle-password"
                                                style="display: flex; align-items: center; justify-content: center; width: 40px; height: 34px;">
                                                <i class="far fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-3 text-center">
                            <div class="col-md-8 offset-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    {{ __('Submit') }}
                                </button>
                            </div>
                        </div>

                    </form>

                    <!-- Back Button -->
                    <div class="form-group row mt-3 text-center">
                        <div class="col-md-8 offset-md-2">
                            <a href="{{ route('login') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Style for handling password strength and updating the progress bar -->
    <style>
        .progress {
            height: 20px;
            background-color: #f3f3f3;
            border-radius: 5px;
            overflow: hidden;
            display: none;
        }

        .progress-bar {
            height: 100%;
            transition: width 0.3s;
        }

        .weak {
            background-color: red;
        }

        .moderate {
            background-color: orange;
        }

        .fair {
            background-color: yellow;
        }

        .strong {
            background-color: green;
        }
    </style>

    <!-- Include zxcvbn library from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/zxcvbn@4.4.2/dist/zxcvbn.js"></script>

    <!-- Script for handling password strength, updating the progress bar and toggle password-->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var progressBar = document.getElementById('progress-bar');
            var progressContainer = document.querySelector('.progress');
            var strengthText = document.getElementById('strength-text');

            progressContainer.style.display = 'none';
            strengthText.style.display = 'none';

            document.getElementById('password').addEventListener('input', function() {
                var password = this.value;
                var result = zxcvbn(password);
                var score = result.score;

                if (password === '') {
                    progressContainer.style.display = 'none';
                    strengthText.style.display = 'none';
                    return;
                }

                progressContainer.style.display = 'block';
                strengthText.style.display = 'block';

                var strength;
                switch (score) {
                    case 0:
                    case 1:
                        progressBar.style.width = '25%';
                        progressBar.className = 'progress-bar weak';
                        strengthText.textContent = 'Weak';
                        strength = 'weak';
                        break;
                    case 2:
                        progressBar.style.width = '50%';
                        progressBar.className = 'progress-bar moderate';
                        strengthText.textContent = 'Moderate';
                        strength = 'moderate';
                        break;
                    case 3:
                        progressBar.style.width = '75%';
                        progressBar.className = 'progress-bar fair';
                        strengthText.textContent = 'Fair';
                        strength = 'fair';
                        break;
                    case 4:
                        progressBar.style.width = '100%';
                        progressBar.className = 'progress-bar strong';
                        strengthText.textContent = 'Strong';
                        strength = 'strong';
                        break;
                }
            });
        });

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPassword = document.querySelector('#password-confirm');

        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(password, this);
        });

        toggleConfirmPassword.addEventListener('click', function() {
            togglePasswordVisibility(confirmPassword, this);
        });

        function togglePasswordVisibility(inputField, toggleElement) {
            // Toggle the type attribute
            const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
            inputField.setAttribute('type', type);

            // Toggle the eye icon
            const icon = toggleElement.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        // Prevent copying the password text
        password.addEventListener('copy', function(event) {
            event.preventDefault();
        });

        // Prevent right-click context menu on the password input
        password.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });

        // Prevent copying the confirm password text
        confirmPassword.addEventListener('copy', function(event) {
            event.preventDefault();
        });

        // Prevent right-click context menu on the confirm password input
        confirmPassword.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });
    </script>
@endsection
