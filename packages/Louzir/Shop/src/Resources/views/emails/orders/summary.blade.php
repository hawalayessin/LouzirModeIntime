@php
    $emailChannel = $order->channel ?? core()->getCurrentChannel();
    $kind = $confirmed ? 'confirmed' : 'received';
    $emailTitle = trans('shop::order-mail.'.$kind.'-title');
    $intro = core()->getConfigData('emails.configure.customer_messages.order_'.$kind.'_message', $emailChannel->code, app()->getLocale()) ?: trans('shop::order-mail.'.$kind.'-message');
    $intro = strtr($intro, [':customer' => $order->customer_full_name, ':order' => $order->increment_id, ':store' => core()->getSenderEmailDetails()['name']]);
    $address = $order->shipping_address ?? $order->billing_address;
    $currency = $order->order_currency_code;
    $priceMode = core()->getConfigData('sales.taxes.sales.display_prices');
    $rows = [];
    foreach (['subtotal' => ['sub_total', 'subtotal'], 'shipping_amount' => ['shipping_amount', 'shipping-handling']] as $setting => [$field, $label]) {
        $mode = core()->getConfigData('sales.taxes.sales.display_'.$setting);
        if ($mode !== 'including_tax') $rows[] = [$label.($mode === 'both' ? '-excl-tax' : ''), $order->{$field}];
        if (in_array($mode, ['including_tax', 'both'])) $rows[] = [$label.'-incl-tax', $order->{$field.'_incl_tax'}];
    }
    $paymentTitle = core()->getConfigData('sales.payment_methods.'.$order->payment?->method.'.title', $emailChannel->code, app()->getLocale()) ?: $order->payment?->method;
@endphp
@component('shop::emails.message-layout', compact('emailChannel', 'emailTitle'))
    <p style="white-space:pre-line">{{ $intro }}</p>
    <div style="padding:16px;background:#faf9f7;border:1px solid #e3e2e0;border-radius:8px;margin:24px 0">
        <strong>@lang('shop::order-mail.number') #{{ $order->increment_id }}</strong><br>
        @lang('shop::order-mail.date') : {{ $order->created_at->format('d/m/Y H:i') }}<br>
        @lang('shop::order-mail.status') : @lang('shop::order-mail.'.$kind.'-status')
    </div>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;table-layout:fixed;overflow-wrap:anywhere">
        <thead><tr>
            <th style="text-align:start;padding:12px 4px;border-bottom:1px solid #e3e2e0;width:54%">@lang('shop::app.emails.orders.name')</th>
            <th style="padding:12px 4px;border-bottom:1px solid #e3e2e0;width:16%">@lang('shop::order-mail.quantity')</th>
            <th style="text-align:end;padding:12px 4px;border-bottom:1px solid #e3e2e0;width:30%">@lang('shop::app.emails.orders.grand-total')</th>
        </tr></thead>
        <tbody>
        @foreach ($order->items->whereNull('parent_id') as $item)
            <tr>
                <td style="padding:12px 4px;border-bottom:1px solid #e3e2e0">
                    <strong>{{ $item->name }}</strong><br><small>{{ $item->sku }}</small>
                    @foreach ($item->additional['attributes'] ?? [] as $attribute)
                        @if (($attribute['attribute_type'] ?? '') !== 'file')
                            <br><small>{{ $attribute['attribute_name'] ?? '' }} : {{ $attribute['option_label'] ?? '' }}</small>
                        @endif
                    @endforeach
                </td>
                <td align="center" style="padding:12px 4px;border-bottom:1px solid #e3e2e0">{{ $item->qty_ordered }}</td>
                <td style="text-align:end;padding:12px 4px;border-bottom:1px solid #e3e2e0">
                    {{ core()->formatPrice(in_array($priceMode, ['including_tax', 'both']) ? $item->total_incl_tax : $item->total, $currency) }}
                    @if ($priceMode === 'both')<br><small>@lang('shop::app.emails.orders.excl-tax') {{ core()->formatPrice($item->total, $currency) }}</small>@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <table width="100%" cellpadding="5" cellspacing="0" style="margin:16px 0;font-size:14px">
        @foreach ($rows as [$label, $amount])
            <tr><td>@lang('shop::app.emails.orders.'.$label)</td><td style="text-align:end">{{ core()->formatPrice($amount, $currency) }}</td></tr>
        @endforeach
        <tr><td>@lang('shop::app.emails.orders.tax')</td><td style="text-align:end">{{ core()->formatPrice($order->tax_amount, $currency) }}</td></tr>
        @if ($order->discount_amount > 0)
            <tr><td>@lang('shop::app.emails.orders.discount')</td><td style="text-align:end">−{{ core()->formatPrice($order->discount_amount, $currency) }}</td></tr>
        @endif
        <tr><td><strong>@lang('shop::app.emails.orders.grand-total')</strong></td><td style="text-align:end"><strong>{{ core()->formatPrice($order->grand_total, $currency) }}</strong></td></tr>
    </table>
    @if ($address)
        <h2 style="font-size:17px">@lang('shop::app.emails.orders.'.($order->shipping_address ? 'shipping-address' : 'billing-address'))</h2>
        <p>{{ $address->name }}<br>{{ $address->address }}<br>{{ $address->postcode }} {{ $address->city }} {{ $address->state }}<br>{{ $address->phone }}</p>
    @endif
    @if ($order->shipping_title)<p><strong>@lang('shop::app.emails.orders.shipping')</strong><br>{{ $order->shipping_title }}</p>@endif
    <p><strong>@lang('shop::app.emails.orders.payment')</strong><br>{{ $paymentTitle }}</p>
    @if (! $order->is_guest && $order->customer_id)
        <p><a href="{{ route('shop.customers.account.orders.view', $order->id) }}" style="color:#1a1c1b">@lang('shop::order-mail.account')</a></p>
    @endif
@endcomponent
