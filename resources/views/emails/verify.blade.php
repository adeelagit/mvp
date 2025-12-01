@component('mail::message')
# Hello {{ $name }},

Thanks for registering! Use the OTP below to verify your email:

# **{{ $otp }}**

This OTP will expire in 10 minutes.

Or click the link below to verify via the link:

@component('mail::button', ['url' => $url])
Verify Email
@endcomponent

If you did not create this account, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
