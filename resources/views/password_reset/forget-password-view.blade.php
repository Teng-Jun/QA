@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-md-8">
            <div class="card">
                @if (session()->has('error'))
                    <div id="errorMessage" class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (session()->has('success'))
                    <div id="successMessage" class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="card-header text-center bg-primary text-white">
                    <h4>{{ __('Forget Password') }}</h4>
                </div>

                <div class="card-body">
                    {{-- <p>{{ __('We will send a link to your email, use that link to reset password.') }}</p> --}}
                    <div class="mt-2">
                        {{-- @if ($errors->any())
                            <div class="col-12">
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-danger">{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif --}}


                    </div>

                    <form method="POST" action="{{ route('forget.password.post') }}">
                        @csrf

                        <div class="form-group row mb-2">
                            <label for="email"
                                class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mt-3 text-center">
                            <div class="col-md-4 offset-md-4">
                                <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"  data-type="image">></div>
                            </div>
                        </div>

                        <div class="form-group row mt-3 text-center">
                            <div class="col-md-8 offset-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    {{ __('Submit') }}
                                </button>
                                <!-- Back Button -->
                                <a href="{{ route('login') }}" class="btn btn-secondary">{{ __('Back') }}</a>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
