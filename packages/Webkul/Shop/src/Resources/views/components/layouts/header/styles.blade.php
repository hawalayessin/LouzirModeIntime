<style>
    .louzir-desktop-bar {
        gap: 24px;
        padding-inline: 28px;
    }

    .louzir-desktop-brand {
        flex: 0 1 auto;
        min-width: 0;
        gap: 16px;
    }

    .louzir-desktop-tools {
        display: flex;
        align-items: center;
        gap: 20px;
        min-width: 0;
        margin-inline-start: auto;
    }

    .louzir-desktop-search {
        width: 320px;
        max-width: 100%;
        min-width: 180px;
    }

    .louzir-desktop-actions,
    .louzir-mobile-actions {
        display: flex;
        align-items: center;
        flex: 0 0 auto;
        gap: 8px;
        margin: 0;
        padding: 0;
    }

    .louzir-desktop-actions > *,
    .louzir-mobile-actions > * {
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
    }

    .louzir-desktop-actions > a,
    .louzir-mobile-actions > a,
    .louzir-desktop-actions .icon-users,
    .louzir-desktop-actions .icon-cart,
    .louzir-mobile-actions .icon-users,
    .louzir-mobile-actions .icon-cart,
    .louzir-mobile-brand .icon-hamburger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        line-height: 1;
    }

    .louzir-mobile-row,
    .louzir-mobile-brand {
        display: flex;
        align-items: center;
        min-width: 0;
        gap: 8px;
    }

    .louzir-mobile-row {
        width: 100%;
        justify-content: space-between;
    }

    .louzir-mobile-brand {
        gap: 4px;
    }

    .louzir-mobile-logo {
        display: flex;
        flex-shrink: 1;
        min-width: 0;
        align-items: center;
    }

    .louzir-mobile-logo img {
        width: 110px;
        height: 56px;
        object-fit: contain;
    }

    .louzir-mobile-actions > .louzir-locale {
        flex-basis: 64px;
        width: 64px;
    }

    .louzir-locale button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 40px;
        gap: 4px;
    }

    .louzir-header-search .icon-search {
        top: 50%;
        transform: translateY(-50%);
        line-height: 1;
    }

    @media (min-width: 1024px) and (max-width: 1279px) {
        .louzir-desktop-bar { gap: 16px; padding-inline: 20px; }
        .louzir-desktop-brand { gap: 8px; }
        .louzir-desktop-tools { gap: 12px; }
        .louzir-desktop-search { width: 240px; }
        .louzir-desktop-actions { gap: 4px; }
    }

    @media (max-width: 480px) {
        .louzir-mobile-bar { padding-inline: 12px; }
        .louzir-mobile-actions { gap: 0; }
        .louzir-mobile-logo img { width: 86px; }
        .louzir-mobile-actions > .louzir-locale { flex-basis: 40px; width: 40px; }
        .louzir-locale button img { display: none; }
    }

    @media (max-width: 359px) {
        .louzir-mobile-logo img { width: 72px; }
        .louzir-mobile-row { gap: 4px; }
        .louzir-mobile-actions > * { flex-basis: 36px; width: 36px; }
        .louzir-mobile-actions .icon-users,
        .louzir-mobile-actions .icon-cart,
        .louzir-mobile-actions > a,
        .louzir-mobile-brand .icon-hamburger { width: 36px; }
    }
</style>
