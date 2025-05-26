@component('mail::message')
# Pesan Kontak Baru

Anda menerima pesan baru dari form kontak di website:

**Nama**: {{ $contactData['name'] }}  
**Email**: {{ $contactData['email'] }}  
**Telepon**: {{ $contactData['phone'] }}  
**Subjek**: {{ $contactData['subject'] }}  

**Pesan**:
{{ $contactData['message'] }}

Terima kasih,  
{{ config('settings.value.app_name') }}
@endcomponent