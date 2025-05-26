@component('mail::message')

**Pesan**:<br>
{!! nl2br(e($data->message)) !!}

**{{ config('settings.value.app_name') }}**
@endcomponent