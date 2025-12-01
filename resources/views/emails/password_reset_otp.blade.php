@component('mail::message')
# Hello {{ $name }}

You requested to reset your password.

Your password reset OTP is:

# **{{ $otp }}**

This OTP expires in 10 minutes.

If you did not request this, please ignore this email.

Thanks,  
{{ config('app.name') }}
@endcomponent
