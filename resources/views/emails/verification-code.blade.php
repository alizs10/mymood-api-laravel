@component('mail::message')
# تایید ایمیل

کد تایید: 
{{ $verification_code }}

{{ config('app.name') }}
@endcomponent