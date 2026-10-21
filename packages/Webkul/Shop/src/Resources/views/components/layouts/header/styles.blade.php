<style>
    .louzir-header { border-bottom: 1px solid #e9e9e9; box-shadow: 0 2px 8px rgba(0, 0, 0, .025); }
    .louzir-header :is(a, button, input, [tabindex]):focus-visible { outline: 2px solid #181818; outline-offset: 3px; }
    .louzir-header :is(a, button) { touch-action: manipulation; }
    .louzir-topbar > div { min-height: 36px; padding-inline: 32px; border-color: #ededed; }
    .louzir-topbar [role="button"], .louzir-topbar p { padding-block: 7px; font-size: 12px; line-height: 20px; }
    .louzir-topbar [class*="icon-arrow"] { font-size: 16px; }

    .louzir-desktop-bar { align-items: center; gap: 28px; min-height: 80px; padding: 0 32px; border: 0; }
    .louzir-desktop-brand { display: flex; align-items: center; flex: 1 1 auto; min-width: 0; gap: 28px; }
    .louzir-desktop-logo { display: flex; align-items: center; justify-content: center; flex: 0 0 148px; height: 72px; }
    .louzir-desktop-logo img { width: 148px; height: 60px; object-fit: contain; }
    .louzir-desktop-brand > :last-child { min-width: 0; }
    .louzir-category-nav { flex-wrap: wrap; gap: 0 4px; }
    .louzir-category-item { height: 80px; max-width: 220px; border-bottom-width: 2px; font-size: 13px; line-height: 1.5; }
    .louzir-category-item:hover { border-color: #181818; border-bottom-width: 2px; }
    .louzir-category-link, .louzir-all-categories > span { padding-inline: 12px; text-transform: none; overflow-wrap: anywhere; }
    .louzir-all-categories > span { gap: 8px; }
    .louzir-category-panel { top: 100%; max-width: min(850px, calc(100vw - 80px)); border-color: #ededed; border-radius: 0 0 4px 4px; box-shadow: 0 8px 20px rgba(0, 0, 0, .08); }
    .louzir-category-item:focus-within > .louzir-category-panel { opacity: 1; pointer-events: auto; transform: translateY(0); }
    .louzir-desktop-tools { display: flex; align-items: center; flex: 0 0 auto; gap: 20px; margin-inline-start: auto; }
    .louzir-desktop-search { width: 300px; min-width: 0; }
    .louzir-header-search input { height: 44px; padding-inline: 42px; border: 1px solid #e5e5e5; border-radius: 4px; background: #f8f8f8; font-size: 13px; font-weight: 400; text-overflow: ellipsis; transition: border-color .15s ease, background-color .15s ease; }
    .louzir-header-search input:hover { border-color: #bcbcbc; }
    .louzir-header-search input:focus { background: #fff; border-color: #181818; }
    .louzir-header-search .icon-search { top: 50%; transform: translateY(-50%); font-size: 20px; line-height: 1; }
    .louzir-desktop-actions, .louzir-mobile-actions { display: flex; align-items: center; flex: 0 0 auto; gap: 4px; margin: 0; padding: 0; }
    .louzir-desktop-actions > *, .louzir-mobile-actions > * { flex: 0 0 40px; width: 40px; height: 40px; }
    .louzir-desktop-actions > a, .louzir-mobile-actions > a,
    .louzir-desktop-actions .icon-users, .louzir-desktop-actions .icon-cart,
    .louzir-mobile-actions .icon-users, .louzir-mobile-actions .icon-cart,
    .louzir-mobile-brand .icon-hamburger, .louzir-mobile-compare {
        display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; line-height: 1; border-radius: 4px;
    }
    .louzir-desktop-actions > a:hover, .louzir-mobile-compare:hover,
    .louzir-desktop-actions .icon-users:hover, .louzir-desktop-actions .icon-cart:hover,
    .louzir-mobile-actions .icon-users:hover, .louzir-mobile-actions .icon-cart:hover,
    .louzir-mobile-brand .icon-hamburger:hover { background: #f4f4f4; }
    .louzir-mobile-bar { padding: 6px 20px 12px; gap: 6px; box-shadow: none; }
    .louzir-mobile-row { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); align-items: center; gap: 8px; width: 100%; min-height: 64px; }
    .louzir-mobile-brand { display: flex; align-items: center; min-width: 0; gap: 0; }
    .louzir-mobile-logo { display: flex; align-items: center; justify-content: center; width: 112px; height: 60px; }
    .louzir-mobile-logo img { width: 112px; height: 56px; object-fit: contain; }
    .louzir-mobile-row > :last-child { justify-self: end; }
    .louzir-mobile-actions > .louzir-locale { flex-basis: 40px; width: 40px; }
    .louzir-locale button { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; }
    .louzir-locale button img { display: none; }

    @media (min-width: 1024px) and (max-width: 1279px) {
        .louzir-desktop-bar { gap: 16px; padding-inline: 24px; }
        .louzir-desktop-brand { gap: 16px; }
        .louzir-desktop-logo { flex-basis: 136px; }
        .louzir-desktop-logo img { width: 136px; }
        .louzir-desktop-tools { gap: 12px; }
        .louzir-desktop-search { width: 220px; }
        .louzir-category-link, .louzir-all-categories > span { padding-inline: 8px; }
    }
    @media (max-width: 480px) {
        .louzir-mobile-bar { padding-inline: 12px; }
        .louzir-mobile-actions { gap: 0; }
        .louzir-mobile-actions > *, .louzir-mobile-actions > .louzir-locale { flex-basis: 36px; width: 36px; }
        .louzir-mobile-actions .icon-users, .louzir-mobile-actions .icon-cart, .louzir-mobile-actions > a,
        .louzir-mobile-brand .icon-hamburger, .louzir-mobile-compare, .louzir-locale button { width: 36px; }
        .louzir-header-search input { font-size: 16px; }
    }
    @media (max-width: 359px) {
        .louzir-mobile-row { gap: 4px; }
        .louzir-mobile-logo { width: 88px; }
        .louzir-mobile-logo img { width: 88px; }
        .louzir-mobile-actions > *, .louzir-mobile-actions > .louzir-locale { flex-basis: 32px; width: 32px; }
        .louzir-mobile-actions .icon-users, .louzir-mobile-actions .icon-cart, .louzir-mobile-actions > a,
        .louzir-mobile-brand .icon-hamburger, .louzir-mobile-compare, .louzir-locale button { width: 32px; }
    }
    @media (prefers-reduced-motion: reduce) { .louzir-header-search input { transition: none; } }
</style>
