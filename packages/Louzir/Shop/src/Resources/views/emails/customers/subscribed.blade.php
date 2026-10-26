@php
    $emailChannel = \Webkul\Core\Models\Channel::find($subscribersList->channel_id) ?? core()->getCurrentChannel();
    $emailTitle = trans('shop::app.emails.customers.subscribed.subject');
    $intro = core()->getConfigData('emails.configure.customer_messages.newsletter_message', $emailChannel->code, app()->getLocale()) ?: trans('shop::order-mail.newsletter-message');
    $intro = strtr($intro, [':store' => core()->getSenderEmailDetails()['name'], ':customer' => $subscribersList->customer?->name ?? '']);
@endphp
@component('shop::emails.message-layout', compact('emailChannel', 'emailTitle'))
    <p style="white-space:pre-line">{{ $intro }}</p>
    <p><a href="{{ route('shop.home.index') }}" style="color:#1a1c1b">@lang('shop::order-mail.store')</a></p>
    <p style="margin-top:32px;font-size:13px"><a href="{{ route('shop.subscription.destroy', $subscribersList->token) }}" style="color:#45464c">@lang('shop::app.emails.customers.subscribed.unsubscribe')</a></p>
@endcomponent
