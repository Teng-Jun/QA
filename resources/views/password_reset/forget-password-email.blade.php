{{-- Email layout to send to users --}}

<h1>{{ __('Reset Password') }}</h1>
<p>Dear User,</p>
<p>We received a request to reset the password for your account. Please use the link below to proceed:</p>
<p><a href="{{ route('reset.password', $token) }}">{{ __('Reset Password') }}</a></p>
<p>If you did not request this password reset, please disregard this email.</p>
<p>Thank you,</p>
<p>Pixiegram</p>
