@component('mail::message')

**Pesan**:<br>
{!! nl2br(e($message)) !!}

Terima kasih,  
**{{ config('settings.value.app_name') }}**
@endcomponent