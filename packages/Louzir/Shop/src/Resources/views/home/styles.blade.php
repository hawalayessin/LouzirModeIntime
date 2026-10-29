<style>
    .louzir-home { --louzir-ink: #161616; --louzir-muted: #656565; --louzir-line: #e6e4e1; --louzir-motion-ease: cubic-bezier(.22, 1, .36, 1); --louzir-motion-duration: 700ms; --louzir-card-shadow: 0 16px 40px rgba(19,21,21,.12); background: #fff; color: var(--louzir-ink); font: 15px/1.65 "Manrope", "Poppins", sans-serif; }
    .louzir-home * { letter-spacing: 0; }
    .louzir-shell { width: min(100%, 1440px); margin-inline: auto; padding-inline: 48px; }
    .louzir-title, .louzir-card-content h3, .louzir-boutique-info h3 { font-family: "Playfair Display", Georgia, serif; font-weight: 600; line-height: 1.2; text-wrap: balance; }
    .louzir-kicker { display: block; font-size: 11px; line-height: 1.5; text-transform: uppercase; font-weight: 600; }

    .louzir-marquee { --marquee-height: 48px; display: flex; align-items: center; min-height: var(--marquee-height); background: var(--marquee-background, #151515); color: var(--marquee-color, #e8d5ce); }
    /* Anchor the oversized track at the left edge in both locales; Arabic text keeps its own RTL direction. */
    .louzir-marquee-window { flex: 1; min-width: 0; overflow: hidden; direction: ltr; }
    .louzir-marquee-track { display: flex; width: max-content; direction: ltr; animation: louzir-ticker var(--marquee-duration, 32s) linear infinite; }
    .louzir-marquee-group { display: flex; flex: 0 0 auto; align-items: center; justify-content: space-around; gap: 48px; min-width: calc(100vw - 44px); padding-inline: 32px; }
    .louzir-marquee-group > span { display: inline-flex; align-items: center; gap: 28px; font-size: 12px; font-weight: 500; white-space: nowrap; text-transform: uppercase; }
    .louzir-marquee-group .icon-star { color: var(--marquee-accent, #d5b18b); font-size: 14px; }
    .louzir-marquee-toggle { display: grid; place-items: center; flex: 0 0 44px; width: 44px; height: var(--marquee-height); border-inline-start: 1px solid currentColor; color: inherit; background: transparent; }
    @media (max-width: 640px) {
        .louzir-marquee { --marquee-height: 44px; }
        .louzir-marquee-group { padding-inline: 20px; gap: 32px; }
        .louzir-marquee-group > span { font-size: 11px; gap: 20px; }
    }
    .louzir-pause-symbol { display: block; width: 10px; height: 12px; border-inline: 3px solid currentColor; }
    .louzir-marquee.is-paused .louzir-pause-symbol { width: 0; height: 0; border: 6px solid transparent; border-inline-start: 9px solid currentColor; border-inline-end: 0; }
    .louzir-marquee:is(:hover, :focus-within, .is-paused) .louzir-marquee-track { animation-play-state: paused; }
    [dir="rtl"] .louzir-marquee-track { animation-direction: reverse; }
    [dir="rtl"] .louzir-marquee-group > span { direction: rtl; }
    @keyframes louzir-ticker { to { transform: translateX(-50%); } }

    .louzir-hero { position: relative; isolation: isolate; overflow: clip; background: #252525; color: #fff; }
    .louzir-hero-photo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center top; z-index: -2; }
    .louzir-hero::before { content: ""; position: absolute; inset: 0; background: rgba(0,0,0,.48); z-index: -1; }
    .louzir-hero-grid { display: flex; align-items: center; min-height: min(640px, calc(100svh - 190px)); padding-block: 56px; }
    .louzir-hero-copy { max-width: 590px; }
    .louzir-hero-kicker { display: inline-block; padding: 5px 10px; border: 1px solid #ffffff40; border-radius: 4px; background: #ffffff18; font-size: 11px; }
    .louzir-hero h1 { margin-block: 18px 4px; font-size: 56px; overflow-wrap: anywhere; }
    .louzir-hero-tagline { margin-bottom: 20px; font-family: "Playfair Display", Georgia, serif; font-style: italic; font-size: 30px; line-height: 1.35; color: #e8d5ce; }
    .louzir-hero-description { max-width: 500px; font-size: 16px; line-height: 1.7; color: #f1f1f1; }
    .louzir-actions, .louzir-socials { display: flex; flex-wrap: wrap; align-items: center; gap: 16px; margin-top: 28px; }
    .louzir-button { display: inline-flex; justify-content: center; align-items: center; gap: 10px; min-height: 46px; padding: 12px 22px; border: 1px solid transparent; border-radius: 999px; font-size: 12px; font-weight: 700; line-height: 1.5; text-align: center; text-transform: uppercase; transition: transform .15s var(--louzir-motion-ease), box-shadow .25s var(--louzir-motion-ease), background-color .25s ease; }
    .louzir-button.primary { background: #181818; color: #fff; }
    .louzir-button.secondary { border-color: #d9d7d3; background: #fff; color: #181818; }
    .louzir-hero .primary { background: #fff; color: #181818; }
    .louzir-hero .secondary { border-color: #ffffff80; background: transparent; color: #fff; }
    .louzir-button:active { transform: scale(.97); }
    .louzir-socials { gap: 18px; font-size: 12px; color: #ddd; }
    .louzir-socials > span { font-size: 11px; color: #ddd; }
    .louzir-chip { display: inline-flex; align-items: center; min-height: 32px; border-bottom: 1px solid #ffffff60; }
    .louzir-hero-edition { position: absolute; inset-inline-end: 32px; bottom: 24px; padding: 8px 12px; background: #111111b3; font-size: 10px; text-transform: uppercase; }
    .louzir-edition { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding-block: 14px; border-bottom: 1px solid var(--louzir-line); font-size: 11px; text-transform: uppercase; }
    .louzir-edition div { display: flex; align-items: center; flex-wrap: wrap; gap: 16px; }
    .louzir-edition span { color: #888; }
    .louzir-edition p { border-inline-start: 1px solid var(--louzir-line); padding-inline-start: 16px; font-weight: 600; }

    .louzir-strip { padding-block: 44px; border-bottom: 1px solid var(--louzir-line); background: #faf8f5; }
    .louzir-strip-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .louzir-feature { display: flex; align-items: flex-start; gap: 16px; padding-inline: 24px; }
    .louzir-feature:first-child { padding-inline-start: 0; }
    .louzir-feature + .louzir-feature { border-inline-start: 1px solid var(--louzir-line); }
    .louzir-feature-icon { display: grid; place-items: center; flex: 0 0 34px; width: 34px; height: 34px; border-radius: 8px; background: #181818; color: #fff; font-size: 17px; }
    .louzir-feature h3 { margin-bottom: 6px; font: 600 17px/1.3 "Playfair Display", Georgia, serif; }
    .louzir-feature p { font-size: 13px; color: var(--louzir-muted); }

    .louzir-products, .louzir-boutiques { padding-block: 80px; scroll-margin-top: 120px; }
    .louzir-section-head { display: flex; justify-content: space-between; align-items: end; gap: 40px; margin-bottom: 40px; padding-bottom: 24px; border-bottom: 1px solid #f0efed; }
    .louzir-section-head .louzir-kicker { margin-bottom: 10px; color: #737373; }
    .louzir-section-head h2, .louzir-signature-head h2 { font-size: 36px; }
    .louzir-section-head p { max-width: 390px; font-size: 14px; color: var(--louzir-muted); }
    .louzir-bento { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 48px; }
    .louzir-card { position: relative; min-width: 0; border-radius: 20px; transition: box-shadow .35s var(--louzir-motion-ease); }
    .louzir-card-media { position: relative; aspect-ratio: 1.6; overflow: hidden; border-radius: 16px; background: #f4f3f1; }
    .louzir-card-media img { width: 100%; height: 100%; object-fit: cover; transition: transform var(--louzir-motion-duration) var(--louzir-motion-ease); }
    .louzir-media-badge { position: absolute; inset-inline-start: 16px; bottom: 16px; max-width: calc(100% - 32px); padding: 5px 10px; background: #ffffffed; color: #181818; font-size: 10px; text-transform: uppercase; font-weight: 700; }
    .louzir-card-content { padding: 18px 16px 20px; }
    .louzir-card-content .louzir-kicker { color: #777; font-size: 10px; }
    .louzir-card-content h3 { margin-block: 5px; font-size: 23px; }
    .louzir-card-content p { font-size: 13px; color: var(--louzir-muted); }
    .louzir-card-link { position: absolute; inset: 0; z-index: 1; }
    .louzir-card:focus-within { outline: 2px solid #181818; outline-offset: 5px; }

    .louzir-signature { padding-block: 64px; background: #f4f1ea; scroll-margin-top: 120px; }
    .louzir-signature-head { margin-bottom: 40px; text-align: center; }
    .louzir-signature-head .louzir-kicker { color: #777; margin-bottom: 10px; }
    .louzir-signature-head p { margin-top: 12px; font-size: 14px; color: #666; }
    .louzir-signature-grid, .louzir-signature-loading { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 24px; }
    .louzir-signature-grid[data-single] { grid-template-columns: minmax(0, 300px); justify-content: center; }
    .louzir-signature-loading > span { height: 360px; }
    .louzir-signature-empty { padding-block: 44px; text-align: center; color: #666; }
    .louzir-signature-empty .louzir-button { margin-top: 16px; }
    .louzir-signature-product { min-width: 0; border: 1px solid #e5e2dc; border-radius: 2px; background: #fff; }
    .louzir-signature-product > div:first-child { width: 100%; max-width: none; max-height: none; border-radius: 0; }
    .louzir-signature-product > div:nth-child(2) { min-width: 0; max-width: none; margin-top: 0; transform: none; gap: 10px; padding: 16px; }
    .louzir-signature-product > div:nth-child(2) > p { font: 600 16px/1.4 "Playfair Display", Georgia, serif; overflow-wrap: anywhere; word-break: normal; max-width: none; }
    .louzir-signature-product .action-items { display: flex; opacity: 1; flex-wrap: wrap; gap: 8px; }
    .louzir-signature-product .action-items button { flex: 1 1 100%; min-height: 48px; padding: 10px 12px; border: 1px solid #181818; border-radius: var(--ui-button-radius); background: #181818; color: #fff; font-size: 13px; }
    .louzir-signature-product .action-items button:disabled { opacity: .5; cursor: not-allowed; }
    .louzir-signature-product .action-items [role="button"] { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; padding: 6px; }

    .louzir-boutique-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 32px; }
    .louzir-boutique-card { min-width: 0; padding: 28px; border: 1px solid #e9e6e2; border-radius: 20px; background: #faf8f5; transition: box-shadow .35s var(--louzir-motion-ease); }
    .louzir-boutique-info h3 { font-size: 24px; }
    .louzir-boutique-info p { margin-block: 10px 20px; color: #666; font-size: 14px; }
    .louzir-map-preview { display: flex; flex-direction: column; width: 100%; min-width: 0; background: #fff; }
    .louzir-map-preview img { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: contain; background: #f0eeeb; }
    .louzir-map-preview > span { display: inline-flex; justify-content: center; align-items: center; gap: 8px; min-height: 46px; padding: 10px 14px; color: #181818; font-size: 12px; }
    .louzir-boutique-actions { display: flex; gap: 12px; margin-top: 24px; }
    .louzir-boutique-actions .primary { flex: 1; }
    .louzir-community { padding-block: 56px; background: #111; color: #fff; }
    .louzir-community-card { display: flex; justify-content: space-between; align-items: center; gap: 48px; }
    .louzir-community-card > div:first-child { max-width: 690px; }
    .louzir-community-card .louzir-kicker { color: #c6c6c6; }
    .louzir-community h2 { margin-block: 12px; font-size: 34px; }
    .louzir-community p { max-width: 560px; font-size: 14px; color: #c6c6c6; }
    .louzir-community .louzir-actions { margin-top: 0; flex-wrap: nowrap; }
    .louzir-home :is(a, button, summary):focus-visible { outline: 2px solid #181818; outline-offset: -3px; }
    .louzir-marquee button:focus-visible { outline-color: currentColor; }
    .louzir-hero a:focus-visible, .louzir-community a:focus-visible { outline-color: #fff; }
    .louzir-home :is(a,button) { touch-action: manipulation; }
    @media (hover: hover) and (pointer: fine) {
        .louzir-button:hover { box-shadow: 0 8px 24px rgba(19,21,21,.14); }
        .louzir-card:hover, .louzir-boutique-card:hover { box-shadow: var(--louzir-card-shadow); }
        .louzir-card:hover img { transform: scale(1.025); }
    }
    .louzir-home[data-motion-enabled="0"] :is(.louzir-button, .louzir-card, .louzir-card-media img, .louzir-boutique-card, .louzir-hero-photo, .louzir-slide, .louzir-hero-copy) { animation: none !important; transition: none !important; }
    .louzir-home[data-motion-enabled="0"] .louzir-card:hover img, .louzir-home[data-motion-enabled="0"] .louzir-button:active { transform: none; }

    @media (max-width: 900px) {
        .louzir-hero-photo { position: relative; display: block; height: auto; aspect-ratio: 1408 / 768; object-fit: contain; z-index: auto; }
        .louzir-hero::before { display: none; }
        .louzir-hero-grid { min-height: 0; padding-block: 32px 64px; }
        .louzir-hero-copy { max-width: 650px; }
    }
    @media (max-width: 1000px) {
        .louzir-shell { padding-inline: 28px; }
        .louzir-section-head { align-items: start; gap: 18px; }
        .louzir-section-head h2 { font-size: 30px; }
        .louzir-bento { gap: 32px; }
        .louzir-feature { padding-inline: 16px; gap: 12px; }
        .louzir-feature h3 { font-size: 16px; }
        .louzir-signature-grid, .louzir-signature-loading { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .louzir-boutique-card { padding: 20px; }
        .louzir-boutique-actions { flex-wrap: wrap; }
        .louzir-boutique-actions .louzir-button { flex: 1 1 auto; }
        .louzir-community-card { flex-direction: column; align-items: start; gap: 28px; }
    }
    @media (max-width: 640px) {
        .louzir-shell { padding-inline: 20px; }
        .louzir-hero-grid { min-height: 0; padding-block: 28px 64px; }
        .louzir-hero h1 { font-size: 36px; }
        .louzir-hero-tagline { font-size: 25px; }
        .louzir-hero-description { font-size: 15px; }
        .louzir-hero-edition { inset-inline-end: 20px; bottom: 16px; }
        .louzir-actions { gap: 12px; }
        .louzir-hero .louzir-button { flex: 1 1 150px; }
        .louzir-socials { gap: 14px; }
        .louzir-strip-grid, .louzir-bento, .louzir-boutique-grid { grid-template-columns: minmax(0, 1fr); }
        .louzir-strip { padding-block: 16px; }
        .louzir-feature, .louzir-feature:first-child { padding: 20px 0; }
        .louzir-feature + .louzir-feature { border-inline-start: 0; border-top: 1px solid var(--louzir-line); }
        .louzir-products, .louzir-boutiques { padding-block: 48px; }
        .louzir-section-head { flex-direction: column; margin-bottom: 28px; }
        .louzir-section-head h2, .louzir-signature-head h2, .louzir-community h2 { font-size: 28px; }
        .louzir-section-head p { max-width: none; }
        .louzir-card-content h3 { font-size: 22px; }
        .louzir-signature-grid, .louzir-signature-loading { gap: 12px; }
        .louzir-signature-product > div:nth-child(2) { padding: 12px; }
        .louzir-signature-product > div:nth-child(2) > p { font-size: 15px; }
        .louzir-signature-product .action-items { display: flex !important; }
        .louzir-signature-product .action-items [role="button"] { display: none; }
        .louzir-edition { gap: 8px; }
        .louzir-edition div { gap: 10px; }
        .louzir-edition p { padding-inline-start: 10px; }
    }
    @media (max-width: 359px) { .louzir-hero h1 { font-size: 32px; } }
    @media (prefers-reduced-motion: reduce) {
        .louzir-home *, .louzir-home .louzir-card img { animation: none !important; transition: none !important; scroll-behavior: auto; }
        .louzir-card:hover img, .louzir-button:hover { transform: none; }
        .louzir-marquee-track { width: 100%; }
        .louzir-marquee-group { flex-wrap: wrap; min-width: 0; gap: 8px 24px; padding-block: 10px; }
        .louzir-marquee-group > span { white-space: normal; }
        .louzir-marquee-group[aria-hidden="true"], .louzir-marquee-toggle { display: none; }
    }
    @media print {
        .louzir-home * { animation: none !important; transform: none !important; opacity: 1 !important; }
        .louzir-marquee { display: none; }
    }

    /* A shared grid row keeps every slide the same height, including long translations. */
    .louzir-carousel::before { display: none; }
    .louzir-carousel { padding-block: 20px; background: var(--store-gradient, linear-gradient(135deg, #f7f8fa, #eceff2)); --carousel-flow: 1; }
    [dir="rtl"] .louzir-carousel { --carousel-flow: -1; }
    .louzir-slides { display: grid; margin-inline: clamp(16px, 6vw, 96px); }
    .louzir-slide { grid-area: 1 / 1; position: relative; isolation: isolate; min-width: 0; z-index: 0; border-radius: 24px; overflow: hidden; box-shadow: 0 12px 32px #13151514; opacity: 0; visibility: hidden; pointer-events: none; transform: scale(.92); transition: transform .8s cubic-bezier(.22, 1, .36, 1), opacity .6s ease, visibility 0s linear .8s; }
    .louzir-slide:is(.is-previous, .is-next) { opacity: .65; visibility: visible; transition-delay: 0s; }
    .louzir-slide.is-previous { transform: translateX(calc(-98% * var(--carousel-flow))) scale(.92); }
    .louzir-slide.is-next { transform: translateX(calc(98% * var(--carousel-flow))) scale(.92); }
    .louzir-slide.is-active { z-index: 1; opacity: 1; visibility: visible; pointer-events: auto; transform: translateX(0) scale(1); transition-delay: 0s; }
    .louzir-slide .louzir-hero-copy { opacity: 0; transform: translateY(6px); transition: opacity .18s ease; }
    .louzir-slide.is-active .louzir-hero-copy { opacity: 1; transform: translateY(0); transition: opacity .5s ease .12s, transform .65s cubic-bezier(.22, 1, .36, 1) .08s; }
    .louzir-slide::before { content: ''; position: absolute; inset: 0; z-index: -1; background: linear-gradient(90deg, rgba(0,0,0,var(--hero-overlay)) 0%, rgba(0,0,0,.32) 52%, rgba(0,0,0,.08) 100%); }
    .louzir-carousel .louzir-hero-photo { position: absolute; inset: 0; height: 100%; width: 100%; aspect-ratio: auto; object-fit: cover; object-position: var(--hero-x) var(--hero-y); z-index: -2; }
    .louzir-carousel .louzir-hero-grid { min-height: 640px; padding-block: 64px 104px; }
    .louzir-carousel :is(h1, h2) { margin-block: 18px 4px; font-size: clamp(36px, 4vw, 60px); line-height: 1.12; overflow-wrap: anywhere; }
    .louzir-carousel .louzir-hero-kicker { border-radius: 999px; letter-spacing: .1em; }
    .louzir-carousel-controls { position: absolute; z-index: 2; inset: 0; display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; padding: 0 max(calc(6vw + 24px), calc((100% - 1344px) / 2)) 40px; pointer-events: none; }
    .louzir-carousel-controls button { pointer-events: auto; }
    .louzir-carousel-controls .louzir-carousel-navigation { display: contents; }
    .louzir-carousel [data-carousel-prev], .louzir-carousel [data-carousel-next] { position: absolute; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; display: grid; place-items: center; padding: 0; line-height: 0; background: #00000040; border-color: #ffffff80; backdrop-filter: blur(6px); }
    .louzir-carousel [data-carousel-prev] { inset-inline-start: max(24px, calc(6vw - 24px)); }
    .louzir-carousel [data-carousel-next] { inset-inline-end: max(24px, calc(6vw - 24px)); }
    .louzir-carousel [data-carousel-prev] svg, .louzir-carousel [data-carousel-next] svg { display: block; width: 20px; height: 20px; margin: 0; }
    [dir="rtl"] .louzir-carousel :is([data-carousel-prev], [data-carousel-next]) svg { transform: scaleX(-1); }
    @media (min-width: 641px) { .louzir-carousel .louzir-hero-grid { padding-inline: 96px; } }
    .louzir-carousel-controls[hidden] { display: none; }
    .louzir-carousel-pagination, .louzir-carousel-navigation { display: flex; align-items: center; gap: 8px; }
    .louzir-carousel-counter { font-size: 12px; letter-spacing: .15em; margin-inline-end: 14px; font-variant-numeric: tabular-nums; }
    .louzir-carousel button { color: white; min-width: 44px; height: 44px; border: 1px solid #ffffff55; border-radius: 50%; background: #ffffff0d; cursor: pointer; }
    .louzir-carousel button:hover { background: #ffffff26; }
    .louzir-carousel .louzir-carousel-dot { display: grid; place-items: center; width: 44px; border: 0; border-radius: 0; background: transparent; }
    .louzir-carousel-dot span { display: block; height: 4px; width: 14px; border-radius: 4px; background: #ffffff66; transition: width .3s, background .3s; }
    .louzir-carousel-dot[aria-current="true"] span { width: 36px; background: #e8d5ce; }
    .louzir-carousel button:focus-visible { outline: 2px solid white; outline-offset: 3px; }
    .louzir-carousel:focus-visible { outline: 2px solid #e8d5ce; outline-offset: -3px; }
    @media (max-width: 900px) {
        .louzir-carousel .louzir-hero-photo { object-position: var(--hero-mobile-x) var(--hero-y); }
        .louzir-carousel .louzir-hero-grid { min-height: 560px; }
        .louzir-slide::before { background: linear-gradient(90deg, rgba(0,0,0,var(--hero-overlay)), rgba(0,0,0,.38)); }
    }
    @media (max-width: 640px) {
        .louzir-carousel { padding-block: 12px; }
        .louzir-slides { margin-inline: 16px; }
        .louzir-slide { border-radius: 16px; }
        .louzir-slide { background: #f7f4f0; color: var(--louzir-ink); }
        .louzir-slide::before { display: none; }
        .louzir-carousel .louzir-hero-photo { position: relative; inset: auto; display: block; width: 100%; height: auto; aspect-ratio: 8 / 5; z-index: auto; }
        .louzir-carousel .louzir-hero-grid { min-height: 0; padding: 22px 20px 76px; align-items: flex-start; }
        .louzir-carousel .louzir-hero-copy { width: 100%; max-width: 100%; padding: 0; }
        .louzir-carousel .louzir-hero-kicker { padding: 4px 8px; border-color: #d6cec4; background: transparent; font-size: 9px; letter-spacing: .06em; }
        .louzir-carousel :is(h1, h2) { margin-block: 12px 6px; font-size: clamp(26px, 7vw, 34px); }
        .louzir-carousel .louzir-hero-tagline { margin-bottom: 12px; font-size: 20px; color: #69564a; }
        .louzir-carousel .louzir-hero-description { font-size: 13px; line-height: 1.65; color: #555; }
        .louzir-carousel .louzir-actions { gap: 8px; margin-top: 18px; }
        .louzir-carousel .louzir-button { flex: 1 1 0; min-width: 0; padding: 10px 8px; font-size: 10px; }
        .louzir-carousel .primary { background: #181818; color: #fff; }
        .louzir-carousel .secondary { border-color: #c9c1b8; background: transparent; color: #181818; }
        .louzir-carousel .louzir-socials { gap: 12px; margin-top: 16px; font-size: 11px; color: #555; }
        .louzir-carousel .louzir-socials > span { color: #555; }
        .louzir-carousel .louzir-chip { min-height: 28px; border-color: #bfb5a9; }
        .louzir-carousel-controls { padding: 0 32px 28px; gap: 4px; color: #181818; }
        .louzir-carousel button { color: #181818; border-color: #bfb5a9; background: transparent; }
        .louzir-carousel button:focus-visible, .louzir-carousel a:focus-visible { outline-color: #181818; }
        .louzir-carousel .louzir-carousel-dot span { background: #c7beb4; }
        .louzir-carousel .louzir-carousel-dot[aria-current="true"] span { background: #69564a; }
        .louzir-carousel [data-carousel-prev], .louzir-carousel [data-carousel-next] { top: calc(12px + (100vw - 32px) * .3125); width: 44px; height: 44px; color: #fff; background: #00000070; border-color: #ffffff80; backdrop-filter: none; }
        .louzir-carousel [data-carousel-prev] { inset-inline-start: 12px; }
        .louzir-carousel [data-carousel-next] { inset-inline-end: 12px; }
        .louzir-carousel-counter { margin-inline-end: 0; font-size: 10px; }
        .louzir-carousel-pagination, .louzir-carousel-navigation { gap: 0; }
        .louzir-carousel .louzir-carousel-dot { min-width: 36px; width: 36px; }
        .louzir-carousel-dot[aria-current="true"] span { width: 28px; }
        .louzir-carousel-navigation { gap: 4px; }
    }
    @media (prefers-reduced-motion: reduce) { .louzir-slide { transition: none; } .louzir-slide:not(.is-active) { visibility: hidden; } }
</style>
