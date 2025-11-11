@component('mail::message')
# Hello {{ $name }},

Please verify your email address to complete your registration.

@component('mail::button', ['url' => $url])
Verify Email
@endcomponent

If you did not create this account, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
