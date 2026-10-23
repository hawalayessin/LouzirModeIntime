@php($offerColors = app(\Webkul\Shop\Helpers\HeaderOffer::class)->colors())

<style>
    .louzir-topbar {
        background: {{ $offerColors['background'] }};
        color: {{ $offerColors['text'] }};
    }
    .louzir-topbar > div { min-height: 36px; padding-inline: 32px; border-color: rgb(128 128 128 / 20%); }
    .louzir-topbar [role="button"], .louzir-topbar p { padding-block: 7px; font-size: 12px; line-height: 20px; }
    .louzir-topbar [class*="icon-arrow"] { font-size: 16px; }
    .louzir-topbar .journal-scroll { background: #fff; color: #181818; }
    .louzir-topbar :is(a, button, [tabindex]):focus-visible { outline: 2px solid currentColor; outline-offset: 3px; }
</style>
