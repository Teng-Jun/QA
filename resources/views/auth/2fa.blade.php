@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
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

        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center bg-primary text-white">
                    <h4>{{ __('Enter OTP') }}</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('2fa.verify') }}">
                        @csrf
                        <div class="form-group">
                            <label for="otp" class="form-label">{{ __('OTP Code') }}</label>
                            <input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" required autofocus>
                            @error('otp')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group text-center mt-3">
                            <button type="submit" class="btn btn-primary">{{ __('Verify OTP') }}</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <span id="timer"></span>
                    </div>
                    <div class="text-center mt-3">
                        <form method="POST" action="{{ route('2fa.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">{{ __('Resend OTP') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
