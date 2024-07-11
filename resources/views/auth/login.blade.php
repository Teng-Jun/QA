@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-md-6">
            {{-- Display success message --}}
            @if (session('success'))
                <div id="successMessage" class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header text-center bg-primary text-white">
                    <h4>{{ __('Login') }}</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="form-group">
                            <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <div class="input-group">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password" required
                                    autocomplete="current-password">
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
                        </div>

                        <!-- Remember Me -->
                        <div class="form-group form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                        </div>

                        <!-- Login Button -->
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">{{ __('Login') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Register Link -->
            <div class="form-group text-center">
                @if (Route::has('register'))
                    <a class="btn btn-link"
                        href="{{ route('forget.password') }}">{{ __('Forget your password?') }}</a><br />
                    <a class="btn btn-link"
                        href="{{ route('register') }}">{{ __('New Member? Click here to register') }}</a>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Function to hide success message after 3 seconds
        window.onload = function() {
            var successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 3000); // 3 seconds
            }
        };

        // <!-- handling toggle password-->
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(password, this);
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
    </script>
@endsection
