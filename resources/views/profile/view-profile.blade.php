@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-md-6">
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

                {{-- Display info message --}}
                @if (session('info'))
                    <div id="infoMessage" class="alert alert-info" role="alert">
                        {{ session('info') }}
                    </div>
                @endif

                <div class="card-header text-center bg-primary text-white">
                    <h4>Edit Profile</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
                        @csrf
                        <div class="form-group row mb-2 justify-content-center align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <label for="name" class="col-form-label"><b>{{ __('Name') }}</b></label>
                                    <input id="name" type="text"
                                        class="form-control @error('name') is-invalid @enderror" name="name"
                                        value="{{ $user->name }}" required autocomplete="name" autofocus
                                        style="margin-left: 10px;" oninput="checkForChanges()">
                                </div>

                                @error('name')
                                    <span class="invalid-feedback custom-invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row mt-3 text-center">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-block mb-5" id="saveChangesBtn" disabled>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="form-group row text-center">
                        <div class="col-md-12 d-flex justify-content-center">
                            <a href="{{ route('profile.change-password') }}" class="btn btn-outline-secondary me-3 mb-3">
                                Change Password
                            </a>
                            <form method="POST" action="{{ route('profile.toggle-2fa') }}">
                                @csrf
                                <button type="submit" class="btn mb-3"
                                    style="background-color: {{ $user->two_factor_enabled ? '#dc3545' : '#007bff' }}; color: white;">
                                    {{ $user->two_factor_enabled ? 'Disable 2FA' : 'Enable 2FA' }}
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="form-group row text-center">
                        <div class="col-md-12">
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-danger mb-3" data-bs-toggle="modal"
                                data-bs-target="#deleteAccountModal">
                                Delete Account
                            </button>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="form-group row text-center">
                        <div class="col-md-12">
                            <a href="{{ route('home') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete your account? This action cannot be undone.
                    <form id="deleteAccountForm" method="POST" action="{{ route('profile.delete') }}">
                        @csrf
                        <div class="form-group mt-3">
                            <label for="delete_current_password mb-3">Current Password:</label>
                            <div class="input-group">
                                <input id="delete_current_password" type="password" class="form-control"
                                    name="current_password" required>
                                <div class="input-group-append">
                                    <span id="toggleCurrentPassword" class="input-group-text toggle-password"
                                        style="display: flex; align-items: center; justify-content: center; width: 40px; height: 34px;">
                                        <i class="far fa-eye"></i>
                                    </span>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="deleteAccountForm" class="btn btn-danger">Delete Account</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkForChanges() {
            var originalName = "{{ $user->name }}";
            var currentName = document.getElementById('name').value;
            var saveChangesBtn = document.getElementById('saveChangesBtn');

            if (originalName !== currentName) {
                saveChangesBtn.disabled = false;
            } else {
                saveChangesBtn.disabled = true;
            }
        }

        // <!-- handling toggle password-->
        const togglePassword = document.querySelector('#toggleCurrentPassword');
        const password = document.querySelector('#delete_current_password');

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
    <style>
        .custom-invalid-feedback {
            display: block;
            margin-left: 53px;
        }
    </style>
@endsection
