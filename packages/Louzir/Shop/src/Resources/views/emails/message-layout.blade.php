@php
    $emailChannel = $emailChannel ?? core()->getCurrentChannel();
    $contact = core()->getContactEmailDetails();
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $emailTitle }}</title></head>
<body style="margin:0;background:#faf9f7;color:#1a1c1b;font:15px/1.7 Arial,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border:1px solid #e3e2e0;border-radius:12px">
<tr><td style="padding:28px 24px;background:#efeeec;border-radius:12px 12px 0 0">
    @if ($emailChannel->logo_url)
        <img src="{{ $emailChannel->logo_url }}" alt="{{ $emailChannel->name }}" width="140" style="max-width:140px;height:auto">
    @else
        <strong style="font:24px Georgia,serif">{{ core()->getSenderEmailDetails()['name'] }}</strong>
    @endif
</td></tr>
<tr><td style="padding:28px 24px">
    <h1 style="margin:0 0 20px;font:28px/1.3 Georgia,serif">{{ $emailTitle }}</h1>
    {{ $slot }}
</td></tr>
<tr><td style="padding:20px 24px;border-top:1px solid #e3e2e0;font-size:13px;color:#45464c">
    @lang('shop::order-mail.help') <a href="mailto:{{ $contact['email'] }}" style="color:#45464c">{{ $contact['email'] }}</a>
</td></tr>
</table>
</td></tr></table>
</body></html>
