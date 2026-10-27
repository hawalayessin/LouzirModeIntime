@inject('storefrontContent', 'Webkul\Shop\Helpers\HomeContent')
@php
    $bodyFont = ['montserrat' => 'Montserrat', 'manrope' => 'Manrope', 'inter' => 'Inter'][$storefrontContent->value('design-body-font')];
    $headingChoice = $storefrontContent->value('design-heading-font');
    $headingFont = match ($headingChoice) {
        'georgia' => 'Georgia',
        'playfair' => 'Playfair Display',
        default => $bodyFont,
    };
    $remoteFonts = [];
    if ($bodyFont !== 'Manrope') {
        $remoteFonts[] = 'family='.urlencode($bodyFont).':wght@400;500;600;700';
    }
    if ($headingFont === 'Playfair Display') {
        $remoteFonts[] = 'family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500';
    }
@endphp
@include('core::components.ui-typography')
@if ($remoteFonts)
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ implode('&', $remoteFonts) }}&amp;display=swap">
@endif
<style>
    :root {
        --store-background: {{ $storefrontContent->value('design-background') }};
        --store-surface: {{ $storefrontContent->value('design-surface') }};
        --store-panel: {{ $storefrontContent->value('design-panel') }};
        --store-button: {{ $storefrontContent->value('design-button') }};
        --store-button-text: {{ $storefrontContent->value('design-button-text') }};
        --store-accent-strong: {{ $storefrontContent->value('design-accent-strong') }};
        --store-ink: {{ $storefrontContent->value('design-text') }};
        --store-accent: {{ $storefrontContent->value('design-accent') }};
        --store-border: {{ $storefrontContent->value('design-border') }};
        --store-width: {{ $storefrontContent->value('design-width') }}px;
        --store-body-font: '{{ $bodyFont }}', 'Noto Sans Arabic', system-ui, sans-serif;
        --store-heading-font: '{{ $headingFont }}', 'Noto Sans Arabic', system-ui, sans-serif;
        --store-muted: {{ $storefrontContent->value('design-muted') }};
        --store-gradient: linear-gradient(135deg, var(--store-surface), var(--store-background) 48%, var(--store-panel));
        --store-card-shadow: 0 12px 36px -4px rgba(26, 28, 27, .08), 0 4px 12px -2px rgba(26, 28, 27, .03);
    }
    .louzir-storefront { --ui-font: var(--store-body-font); background: var(--store-background); color: var(--store-ink); font: 14px/1.65 var(--store-body-font); }
    .louzir-storefront :is(button, input, select, textarea) { font-family: inherit; }
    .louzir-storefront :is(#main, #app) { background-color: var(--store-background); }
    .louzir-storefront :is(.louzir-home, .louzir-auth-page, .louzir-footer, .louzir-header) { font-family: var(--store-body-font); }
    .louzir-storefront :is(h1, h2, .font-dmserif, .louzir-title, .louzir-feature h3, .louzir-footer h2) { font-family: var(--store-heading-font); }
    .louzir-storefront :is(.louzir-hero-tagline, .louzir-signature-product > div:nth-child(2) > p) { font-family: var(--store-heading-font); }
    .louzir-storefront :is(.louzir-card-content h3, .louzir-boutique-info h3) { font-family: var(--store-heading-font); }
    .louzir-storefront :is(h1, h2, h3) { color: inherit; overflow-wrap: anywhere; }
    .louzir-storefront :is(.primary-button, .secondary-button, .louzir-button, .louzir-newsletter-submit) { border-radius: var(--ui-button-radius); font-family: var(--store-body-font); font-size: 14px; font-weight: 600; line-height: 1.4; min-height: 48px; text-transform: none; }
    .louzir-storefront .primary-button { background: var(--store-button); border-color: var(--store-button); color: var(--store-button-text); }
    .louzir-storefront .secondary-button { border-color: var(--store-border); color: var(--store-ink); background: var(--store-surface); }
    .louzir-storefront #main :is(input[type="text"], input[type="email"], input[type="password"], input[type="tel"], input[type="number"], select, textarea) { max-width: 100%; border-color: var(--store-border); border-radius: 6px; }
    .louzir-storefront :is(.container, .louzir-shell, .louzir-catalog-shell, .louzir-footer-inner) { max-width: var(--store-width); }
    .louzir-storefront .text-navyBlue { color: var(--store-ink); }
    .louzir-storefront :is(a, button, summary, [role="button"]):focus-visible { outline: 2px solid var(--store-ink); outline-offset: 3px; }
    .louzir-storefront .louzir-home { --louzir-ink: var(--store-ink); --louzir-line: var(--store-border); background: var(--store-background); }
    .louzir-storefront .louzir-auth-page { --auth-ink: var(--store-ink); --auth-border: var(--store-border); }
    .louzir-storefront .louzir-catalog-shell { padding-inline: 32px; }
    .louzir-storefront .louzir-catalog-results { padding-bottom: 80px; }
    .louzir-collection-heading { padding-block: 36px; }
    .louzir-collection-heading h1 { max-width: 850px; font-size: clamp(30px, 4vw, 52px); line-height: 1.15; margin-bottom: 16px; }
    .louzir-collection-heading .louzir-richtext { max-width: 760px; }
    .louzir-category-hero { position: relative; display: grid; align-items: center; min-height: 360px; margin-top: 28px; border-radius: 16px; overflow: hidden; background: var(--store-panel); }
    .louzir-category-hero .louzir-category-banner { position: absolute; inset: 0; height: 100%; aspect-ratio: auto; border-radius: 0; }
    .louzir-category-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #000b, #0002); }
    .louzir-category-hero .louzir-collection-heading { position: relative; z-index: 1; width: 100%; padding: 44px; color: #fff; }
    .louzir-category-hero .louzir-richtext { color: #fff; }
    .louzir-collection-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
    .louzir-storefront .louzir-collection-actions .primary-button { background: var(--store-surface); color: var(--store-ink); border-color: #fff; }
    .louzir-storefront .louzir-collection-actions .secondary-button { background: transparent; color: #fff; border-color: #ffffff80; }
    #catalogue { scroll-margin-top: 150px; }
    .louzir-category-pills { display: flex; gap: 8px; overflow-x: auto; padding-block: 20px; scrollbar-width: thin; }
    .louzir-category-pills a { flex: 0 0 auto; padding: 7px 16px; border: 1px solid var(--store-border); border-radius: 999px; background: var(--store-surface); font-size: 12px; }
    .louzir-category-pills a:hover { border-color: var(--store-ink); }
    .louzir-category-pills a[aria-current="page"] { border-color: var(--store-ink); background: var(--store-ink); color: #fff; }
    .louzir-filter-panel { padding: 20px; border: 1px solid var(--store-border); border-radius: 12px; background: var(--store-surface); }
    .louzir-filter-panel h3 { font-family: var(--store-body-font); font-size: 13px; text-transform: uppercase; }
    .louzir-filter-heading { gap: 8px; padding-bottom: 12px; }
    .louzir-storefront .louzir-filter-heading p { font-size: 13px; font-weight: 600; }
    .louzir-storefront .louzir-filter-heading button { font-size: 11px; color: var(--store-muted); text-decoration: underline; text-underline-offset: 3px; }
    .louzir-filter-group { padding-block: 16px; border-top: 1px solid var(--store-border); }
    .louzir-filter-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; cursor: pointer; list-style: none; min-height: 28px; }
    .louzir-filter-title::-webkit-details-marker { display: none; }
    .louzir-filter-title::after { content: '+'; color: var(--store-muted); font-size: 17px; }
    .louzir-filter-group[open] > summary::after { content: '−'; }
    .louzir-storefront .louzir-filter-title { font-size: 11px; line-height: 1.5; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
    .louzir-filter-content { padding-top: 10px; }
    .louzir-storefront .louzir-filter-search input { min-height: 36px; padding: 8px 12px; padding-inline-start: 32px; font-size: 12px; font-weight: 400; border-radius: 4px; background: var(--store-background); }
    .louzir-storefront .louzir-filter-search .icon-search { top: 10px; inset-inline-start: 10px; font-size: 15px; color: var(--store-muted); }
    .louzir-filter-options { display: grid; gap: 4px; margin-top: 8px; }
    .louzir-filter-option { position: relative; display: flex; align-items: center; gap: 8px; min-height: 30px; font-size: 12px; line-height: 1.5; color: var(--store-muted); cursor: pointer; overflow-wrap: anywhere; }
    .louzir-filter-option input { width: 16px; height: 16px; flex-shrink: 0; accent-color: var(--store-button); cursor: pointer; }
    .louzir-filter-option:has(input:checked) { color: var(--store-ink); font-weight: 600; }
    .louzir-filter-swatch { width: 12px; height: 12px; flex-shrink: 0; border-radius: 50%; border: 1px solid #1a1c1b20; }
    .louzir-size-options { display: flex; flex-wrap: wrap; gap: 8px; }
    .louzir-size-options .louzir-filter-option { justify-content: center; min-width: 42px; min-height: 36px; padding: 6px 10px; border: 1px solid var(--store-border); border-radius: 4px; background: var(--store-surface); }
    .louzir-size-options input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .louzir-size-options .louzir-filter-option:has(input:checked) { background: var(--store-button); border-color: var(--store-button); color: var(--store-button-text); }
    .louzir-size-options .louzir-filter-option:focus-within { outline: 2px solid var(--store-accent-strong); outline-offset: 2px; }
    .louzir-storefront .louzir-filter-apply { display: flex; align-items: center; justify-content: center; width: 100%; max-width: none; margin-top: 12px; padding: 12px; font-size: 11px; letter-spacing: .04em; text-transform: uppercase; border-radius: 4px; }
    .louzir-filter-panel .louzir-range-label { gap: 4px; }
    .louzir-filter-panel .louzir-range-label p { font-size: 11px; color: var(--store-muted); }
    .louzir-filter-panel .louzir-range-track .bg-navyBlue { background: var(--store-button); }
    .louzir-filter-panel .louzir-range-track input[type="range"]::-webkit-slider-thumb { border-color: var(--store-button); }
    .louzir-filter-panel .louzir-range-track input[type="range"]::-moz-range-thumb { border-color: var(--store-button); }
    .louzir-storefront .louzir-catalog-grid { gap: 24px; align-items: stretch; }
    .louzir-storefront .louzir-product-card { min-width: 0; display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--store-border); border-radius: 12px; background: var(--store-surface); box-shadow: 0 1px 3px #11182705; }
    .louzir-storefront .louzir-product-strip > .louzir-product-card { flex: 0 0 291px; width: 291px; }
    .louzir-storefront .louzir-product-card-media { max-width: none; max-height: none; width: 100%; aspect-ratio: 4 / 5; border-radius: 0; }
    .louzir-product-media-actions { position: absolute; top: 12px; inset-inline-end: 12px; display: flex; flex-direction: column; gap: 8px; }
    .louzir-storefront .louzir-product-media-action { display: grid; place-items: center; flex: 0 0 44px; width: 44px; height: 44px; min-height: 44px; padding: 0; border: 1px solid var(--store-border); border-radius: 50%; background: var(--store-surface); color: var(--store-ink); font-size: 22px; line-height: 1; box-shadow: 0 2px 8px #13151512; }
    .louzir-storefront .louzir-product-media-action:hover { background: var(--store-elevated, #f1f2f3); }
    .louzir-storefront .louzir-product-media-action.icon-heart-fill { color: #dc2626; }
    @media (min-width: 768px) { .louzir-product-media-actions { display: none; } }
    .louzir-product-card-media > a { display: block; width: 100%; height: 100%; }
    .louzir-storefront .louzir-product-card-media > a > :is(img, div) { width: 100%; height: 100%; object-fit: cover; }
    .louzir-product-card-media > a > div::after { display: none; }
    .louzir-storefront .louzir-product-card-info { flex: 1; min-width: 0; max-width: none; margin: 0; padding: 16px; transform: none; gap: 10px; background: var(--store-surface); }
    .louzir-storefront .louzir-product-card-title { font-family: var(--store-body-font); font-size: 13px; font-weight: 500; line-height: 1.55; overflow-wrap: anywhere; word-break: normal; max-width: none; }
    .louzir-storefront .louzir-product-card-price { padding-top: 8px; border-top: 1px solid var(--store-border); font-size: 15px; color: var(--store-ink); }
    .louzir-storefront .louzir-product-card-info > .action-items { opacity: 1; display: flex; gap: 4px; margin-top: auto; }
    .louzir-storefront .louzir-product-card-info .action-items button { min-width: 0; font-size: 13px; background: var(--store-button); color: var(--store-button-text); }
    .louzir-product-card--list { padding: 16px; background: var(--store-surface); border: 1px solid var(--store-border); border-radius: 12px; max-width: 100%; }
    .louzir-product-page { padding-block: 24px 64px; }
    .louzir-product-layout { align-items: flex-start; }
    .louzir-product-purchase { min-width: 0; flex: 1; padding: 28px; border: 1px solid var(--store-border); border-radius: 16px; background: var(--store-surface); }
    .louzir-product-purchase h1 { font-family: var(--store-heading-font); font-size: clamp(26px, 3vw, 34px); line-height: 1.2; }
    .louzir-storefront .louzir-product-purchase .secondary-button { background: var(--store-button); color: var(--store-button-text); border-color: var(--store-button); }
    .louzir-product-purchase .louzir-richtext { margin-top: 16px; }
    .louzir-product-editorial { width: 100%; max-width: var(--store-width); margin: 56px auto 0; padding-inline: 32px; }
    .louzir-product-story { display: grid; grid-template-columns: minmax(180px, .4fr) minmax(0, 1fr); gap: 48px; padding: 36px; border: 1px solid var(--store-border); border-radius: 16px; background: var(--store-surface); }
    .louzir-product-story h2 { font-size: 32px; line-height: 1.2; }
    .louzir-product-story h2::before { content: ''; display: block; width: 36px; height: 3px; margin-bottom: 20px; background: var(--store-accent); }
    .louzir-product-disclosure { margin-top: 16px; border: 1px solid var(--store-border); border-radius: 12px; background: var(--store-surface); }
    .louzir-product-disclosure > summary { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 20px 24px; cursor: pointer; list-style: none; font-size: 15px; font-weight: 600; }
    .louzir-product-disclosure > summary::-webkit-details-marker { display: none; }
    .louzir-product-disclosure > summary::after { content: '+'; font-size: 24px; font-weight: 400; color: var(--store-accent); }
    .louzir-product-disclosure[open] > summary::after { content: '−'; }
    .louzir-product-disclosure-content { padding: 0 24px 24px; min-width: 0; }
    .louzir-product-specs { display: grid; grid-template-columns: minmax(100px, .6fr) minmax(0, 1fr); gap: 0 24px; }
    .louzir-product-specs > :is(dt, dd) { min-width: 0; padding-block: 12px; border-top: 1px solid var(--store-border); overflow-wrap: anywhere; }
    .louzir-product-specs dt { color: var(--store-muted); }
    .louzir-product-specs img { width: 56px; height: 56px; object-fit: contain; }
    .louzir-richtext { font-family: var(--store-body-font); font-size: 14px; line-height: 1.85; color: var(--store-muted); overflow-wrap: anywhere; min-width: 0; }
    .louzir-richtext > * + * { margin-top: 16px; }
    .louzir-richtext :is(h2, h3, h4) { color: var(--store-ink); line-height: 1.35; margin-top: 24px; margin-bottom: 12px; }
    .louzir-richtext h2 { font-size: 26px; }
    .louzir-richtext h3 { font-size: 21px; font-family: var(--store-heading-font); }
    .louzir-richtext :is(ul, ol) { padding-inline-start: 24px; }
    .louzir-richtext ul { list-style: disc; }
    .louzir-richtext ol { list-style: decimal; }
    .louzir-richtext li + li { margin-top: 8px; }
    .louzir-richtext img { max-width: 100%; height: auto; border-radius: 10px; }
    .louzir-richtext table { display: block; max-width: 100%; overflow-x: auto; border-collapse: collapse; }
    .louzir-richtext :is(th, td) { padding: 10px 14px; border: 1px solid var(--store-border); }
    .louzir-richtext a { text-decoration: underline; text-underline-offset: 3px; }
    /* Pearl surfaces use merchant colors, shared by editorial and catalog cards. */
    .louzir-storefront #main { min-height: 65svh; background: var(--store-gradient); }
    .louzir-storefront #main:has(.louzir-product-page) { padding-bottom: 80px; }
    .louzir-storefront :is(.louzir-header, .louzir-desktop-bar, .louzir-mobile-bar) { background-color: var(--store-surface); }
    .louzir-storefront .louzir-footer { background: linear-gradient(120deg, var(--store-background), var(--store-panel)); border-top: 1px solid var(--store-border); }
    .louzir-storefront .louzir-footer :is(a, h3) { color: var(--store-ink); }
    .louzir-storefront .louzir-footer-store a { color: var(--store-accent-strong); }
    .louzir-storefront .louzir-filter-panel { background: var(--store-gradient); }
    .louzir-storefront :is(.louzir-product-purchase, .louzir-product-story) { border-top: 2px solid var(--store-border); box-shadow: 0 8px 28px #1a1c1b05; }
    .louzir-storefront .louzir-product-disclosure { background: var(--store-panel); }
    .louzir-storefront .louzir-product-disclosure > summary::after { color: var(--store-accent-strong); }
    .louzir-storefront .louzir-product-card-price { border-color: var(--store-border); color: var(--store-accent-strong); }
    .louzir-storefront :is(.louzir-button.primary, .louzir-newsletter-submit) { background: var(--store-button); color: var(--store-button-text); border-color: var(--store-button); }
    .louzir-storefront .louzir-hero .louzir-button.primary { background: var(--store-surface); color: var(--store-ink); }
    .louzir-storefront .louzir-home { --louzir-muted: var(--store-muted); background: var(--store-gradient); }
    .louzir-storefront .louzir-home :is(.louzir-strip, .louzir-signature) { background: var(--store-gradient); }
    .louzir-storefront :is(.louzir-card, .louzir-product-card, .louzir-product-card--list) { border: 1px solid var(--store-border); border-radius: 16px; background: var(--store-gradient); box-shadow: 0 1px 3px #1a1c1b05; transition: transform .35s cubic-bezier(.22, 1, .36, 1), box-shadow .35s ease; }
    .louzir-storefront .louzir-card-media { border-radius: 15px 15px 0 0; }
    .louzir-storefront :is(.louzir-card-content, .louzir-product-card-info) { background: transparent; }
    .louzir-storefront .louzir-card-content { border-radius: 0 0 15px 15px; }
    .louzir-storefront .louzir-product-strip { padding-top: 8px; padding-bottom: 28px; }
    @if ($storefrontContent->value('motion-enabled') === '1')
        @media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
            .louzir-storefront :is(.louzir-card, .louzir-product-card, .louzir-product-card--list):hover { transform: perspective(1000px) translateY(-4px) rotateX(1deg); box-shadow: var(--store-card-shadow); }
        }
    @endif
    @media (prefers-reduced-motion: reduce) {
        .louzir-storefront :is(.louzir-card, .louzir-product-card, .louzir-product-card--list), .louzir-storefront .louzir-product-card-media > a > :is(img, div) { transition: none; transform: none; }
    }
    .louzir-storefront .louzir-boutique-card { background: var(--store-surface); border-color: var(--store-border); }
    .louzir-storefront .louzir-community { background: var(--store-button); }
    .louzir-storefront .louzir-auth-page { background: var(--store-gradient); }
    .louzir-storefront :is(.louzir-auth-card, .louzir-form-panel) { background-color: var(--store-surface); }
    .louzir-storefront #main :is(input[type="text"], input[type="email"], input[type="password"], input[type="tel"], input[type="number"], select, textarea) { background-color: var(--store-surface); }
    .louzir-storefront :is(a, button, summary, input, select, textarea):focus-visible { outline-color: var(--store-accent-strong); }
    .louzir-page-intro { margin-block: 24px; padding: 28px 32px; border: 1px solid var(--store-border); border-inline-start: 3px solid var(--store-accent-strong); border-radius: 16px; background: var(--store-gradient); }
    .louzir-page-intro h1 { font: 500 clamp(28px, 3.5vw, 42px)/1.2 var(--store-heading-font); }
    .louzir-page-intro p { margin-top: 12px; max-width: 720px; color: var(--store-muted); font-size: 14px; }
    .louzir-cart-page, .louzir-checkout-page { padding-bottom: 48px; }
    :is(.louzir-cart-page, .louzir-checkout-page) .text-blue-700 { color: var(--store-accent-strong); }
    .louzir-cart-items { min-width: 0; padding: 24px; border: 1px solid var(--store-border); border-radius: 16px; background: var(--store-surface); }
    .louzir-cart-item-content, .louzir-cart-item-details { min-width: 0; }
    .louzir-cart-item-details { overflow-wrap: anywhere; }
    .louzir-cart-summary { padding: 24px; border: 1px solid var(--store-border); border-top: 3px solid var(--store-accent-strong); border-radius: 16px; background: var(--store-panel); }
    .louzir-cart-empty { min-height: 360px; margin-block: 24px; padding: 48px 24px; border: 1px solid var(--store-border); border-radius: 16px; background: var(--store-surface); }
    .louzir-checkout-header { border-bottom: 1px solid var(--store-border); background: var(--store-surface); }
    .louzir-checkout-header a { color: var(--store-ink); }
    .louzir-checkout-page #steps-container .bg-white { background-color: var(--store-surface); }
    .louzir-checkout-page #steps-container .bg-gray-100 { background-color: var(--store-panel); }
    .louzir-checkout-summary { padding: 24px; border: 1px solid var(--store-border); border-radius: 16px; background: var(--store-panel); }
    .louzir-checkout-actions { display: grid; gap: 12px; width: 100%; margin-top: 20px; }
    .louzir-storefront .louzir-place-order { width: 100%; max-width: none; min-height: 52px; padding: 16px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; }
    .louzir-checkout-page #steps-container { padding: 24px; border: 1px solid var(--store-border); border-radius: 16px; background: var(--store-surface); }
    .louzir-search-heading { margin-block: 24px; padding: 24px; background: var(--store-panel); border: 1px solid var(--store-border); border-radius: 16px; }
    @media (max-width: 1023px) { .louzir-product-story { gap: 24px; padding: 24px; } }
    @media (max-width: 767px) {
        .louzir-checkout-review { width: 100%; max-width: none; }
        .louzir-checkout-actions { margin-top: 8px; padding-bottom: env(safe-area-inset-bottom, 0px); }
        .louzir-filter-option { min-height: 44px; font-size: 14px; }
        .louzir-size-options .louzir-filter-option { min-width: 44px; min-height: 44px; }
        .louzir-page-intro { padding: 24px 20px; margin-block: 16px; }
        .louzir-cart-items, .louzir-cart-summary, .louzir-checkout-summary, .louzir-checkout-page #steps-container { padding: 16px; }
        .louzir-cart-empty { min-height: 320px; }
        .louzir-cart-items { width: 100%; }
        .louzir-cart-item-content { gap: 8px; }
        .louzir-cart-item-content > a { flex-shrink: 0; }
        .louzir-cart-item-details > .flex { flex-wrap: wrap; }
        .louzir-search-heading { padding: 20px; }
        .louzir-storefront :is(.louzir-catalog-shell, .louzir-product-editorial) { padding-inline: 16px; }
        .louzir-storefront .louzir-catalog-results { padding-bottom: 96px; }
        .louzir-storefront #main:has(.louzir-product-page) { padding-bottom: 48px; }
        .louzir-storefront .louzir-catalog-grid { gap: 12px; }
        .louzir-storefront .louzir-product-strip > .louzir-product-card { flex-basis: 192px; width: 192px; }
        .louzir-product-card-info { padding: 12px; }
        .louzir-storefront .louzir-product-card-title { font-size: 12px; }
        .louzir-storefront .louzir-product-card-info > .action-items [role="button"] { display: none; }
        .louzir-storefront #main :is(input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select, textarea) { font-size: 16px; }
        .louzir-collection-heading { padding-block: 24px; }
        .louzir-category-hero { min-height: 280px; margin-top: 16px; }
        .louzir-category-hero .louzir-collection-heading { padding: 24px; }
        .louzir-product-story { grid-template-columns: minmax(0, 1fr); gap: 20px; padding: 24px 20px; }
        .louzir-product-story h2 { font-size: 26px; }
        .louzir-product-purchase { border-inline: 0; border-radius: 0; padding: 24px 16px; }
        .louzir-product-editorial { margin-top: 32px; }
        .louzir-product-specs { grid-template-columns: minmax(80px, .6fr) minmax(0, 1fr); gap: 0 12px; font-size: 12px; }
        .louzir-product-disclosure > summary { padding: 16px 20px; }
        .louzir-product-disclosure-content { padding: 0 20px 20px; }
    }
    @media (max-width: 640px) {
        .louzir-storefront .louzir-carousel .louzir-slide { background: var(--store-surface); }
        .louzir-storefront .louzir-carousel .louzir-button.primary { background: var(--store-button); color: var(--store-button-text); }
    }
    @media (max-width: 359px) { .louzir-storefront .louzir-catalog-grid { grid-template-columns: minmax(0, 1fr); } }
    .louzir-storefront :is(h1, h2, h3, h4, .louzir-title) { font-weight: 600; }
    .louzir-storefront .louzir-hero-tagline { font-style: normal; font-weight: 400; font-size: clamp(20px, 2vw, 26px); }
    .louzir-storefront .louzir-carousel :is(h1, h2) { font-size: clamp(28px, 3.5vw, 48px); line-height: 1.15; }
    .louzir-storefront .louzir-place-order { border-radius: var(--ui-button-radius); }
    @media (max-width: 640px) {
        .louzir-storefront .louzir-carousel :is(h1, h2) { font-size: clamp(26px, 7vw, 32px); }
        .louzir-storefront .louzir-carousel .louzir-button { font-size: 12px; }
    }
</style>
