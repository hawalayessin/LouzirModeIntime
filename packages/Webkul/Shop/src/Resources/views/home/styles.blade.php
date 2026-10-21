<style>
    .louzir-home { --louzir-ink: #181818; --louzir-muted: #606060; --louzir-line: #e4e4e4; background: #fff; color: var(--louzir-ink); font-family: "Poppins", "Segoe UI", sans-serif; font-size: 16px; line-height: 1.6; }
    .louzir-home * { letter-spacing: 0; }
    .louzir-shell { width: min(100%, 1440px); margin-inline: auto; padding-inline: 48px; }
    .louzir-title { font-family: "DM Serif Display", Georgia, serif; font-weight: 400; line-height: 1.2; text-wrap: balance; }
    .louzir-kicker { display: block; font-size: 12px; font-weight: 600; }
    .louzir-hero { position: relative; isolation: isolate; overflow: clip; background: #242424; color: #fff; }
    .louzir-hero-photo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center 25%; z-index: -2; }
    .louzir-hero::before { content: ""; position: absolute; inset: 0; background: rgba(0, 0, 0, .32); z-index: -1; }
    .louzir-hero-grid { display: flex; align-items: center; min-height: min(620px, calc(100svh - 220px)); padding-block: 48px; }
    .louzir-hero-copy { max-width: 580px; text-shadow: 0 1px 8px rgba(0, 0, 0, .18); }
    .louzir-hero h1 { font-size: 48px; margin-block: 18px; overflow-wrap: anywhere; }
    .louzir-hero-tagline { font-size: 24px; line-height: 1.4; margin-bottom: 12px; text-wrap: balance; }
    .louzir-hero-description { max-width: 500px; font-size: 16px; line-height: 1.7; }
    .louzir-actions, .louzir-socials { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
    .louzir-actions { margin-top: 24px; }
    .louzir-socials { margin-top: 24px; font-size: 14px; }
    .louzir-button { display: inline-flex; justify-content: center; align-items: center; min-height: 48px; padding: 12px 22px; border: 1px solid transparent; border-radius: 4px; font-size: 16px; font-weight: 500; text-align: center; text-shadow: none; transition: transform .2s ease, box-shadow .2s ease; }
    .louzir-button.primary { background: #181818; color: #fff; }
    .louzir-button.secondary { border-color: var(--louzir-line); background: #fff; color: #181818; }
    .louzir-hero .primary { background: #fff; color: #181818; }
    .louzir-hero .secondary { border-color: #fff; color: #fff; background: transparent; }
    .louzir-button:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0, 0, 0, .16); }
    .louzir-button:active { transform: translateY(0); box-shadow: none; }
    .louzir-hero .secondary:hover { background: #fff; color: #181818; border-color: #fff; }
    .louzir-chip { min-height: 32px; padding: 4px 0; text-decoration: underline; text-underline-offset: 4px; }
    .louzir-socials > span { font-size: 12px; opacity: .9; }
    .louzir-edition { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-block: 16px; font-size: 14px; border-bottom: 1px solid var(--louzir-line); }
    .louzir-edition div { display: flex; gap: 16px; flex-wrap: wrap; }
    .louzir-edition span { color: #181818; }
    .louzir-strip { padding-block: 32px; border-bottom: 1px solid var(--louzir-line); background: #fafafa; }
    .louzir-strip-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 32px; }
    .louzir-feature { display: flex; align-items: flex-start; gap: 14px; }
    .louzir-feature-icon { display: grid; place-items: center; flex: 0 0 36px; width: 36px; height: 36px; color: #181818; font-size: 24px; }
    .louzir-feature h3 { font-size: 16px; }
    .louzir-products, .louzir-boutiques { padding-block: 80px; scroll-margin-top: 110px; }
    .louzir-section-head { display: flex; justify-content: space-between; align-items: end; gap: 32px; margin-bottom: 28px; }
    .louzir-section-head h2, .louzir-community .louzir-title { font-size: 32px; }
    .louzir-section-head h2 { margin-top: 8px; }
    .louzir-section-head p { max-width: 460px; color: var(--louzir-muted); font-size: 16px; }
    .louzir-bento { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 40px 28px; }
    .louzir-card { position: relative; min-width: 0; overflow: hidden; background: #fff; }
    .louzir-card img { display: block; width: 100%; height: 360px; object-fit: cover; background: #f2f2f2; transition: transform .35s ease; }
    .louzir-card:hover img { transform: scale(1.025); }
    .louzir-card:focus-within { outline: 2px solid #181818; outline-offset: 6px; }
    .louzir-card-content { position: relative; padding: 24px 0 0; background: #fff; color: #181818; }
    .louzir-card-content h3 { font-family: "DM Serif Display", Georgia, serif; font-size: 24px; margin-top: 4px; line-height: 1.3; }
    .louzir-card-content p { max-width: 480px; font-size: 14px; margin-top: 8px; line-height: 1.6; color: #606060; }
    .louzir-card-content > span { color: #606060; }
    .louzir-section-head .text-xs { color: #606060; }
    .louzir-boutiques { border-top: 1px solid var(--louzir-line); }
    .louzir-boutique-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
    .louzir-boutique-card { min-width: 0; border: 1px solid var(--louzir-line); border-radius: 4px; overflow: hidden; }
    .louzir-boutique-info { padding: 24px; min-height: 132px; }
    .louzir-boutique-info h3 { font-size: 22px; font-family: "DM Serif Display", Georgia, serif; }
    .louzir-boutique-info p { color: var(--louzir-muted); margin-top: 8px; }
    .louzir-map-preview { position: relative; }
    .louzir-map-preview iframe { display: block; border: 0; width: 100%; height: 224px; pointer-events: none; }
    .louzir-map-preview a { position: absolute; inset: 0; z-index: 1; }
    .louzir-boutique-actions { display: flex; flex-wrap: wrap; gap: 12px; padding: 20px 24px; }
    .louzir-community { padding-block: 64px; background: #181818; color: #fff; }
    .louzir-community-card p { max-width: 660px; margin-top: 12px; font-size: 16px; }
    .louzir-community-card > span { color: #dedede; }
    .louzir-home :is(a, button):focus-visible { outline: 3px solid #181818; outline-offset: 4px; }
    .louzir-hero a:focus-visible, .louzir-community a:focus-visible { outline-color: #fff; }
    .louzir-home a { touch-action: manipulation; }
    @media (min-width: 1600px) { .louzir-hero-grid { min-height: min(680px, calc(100svh - 220px)); } }
    @media (max-width: 1000px) {
        .louzir-shell { padding-inline: 28px; }
        .louzir-hero-grid { min-height: min(560px, calc(100svh - 180px)); }
        .louzir-hero-copy { max-width: 480px; }
        .louzir-strip-grid { gap: 20px; }
        .louzir-section-head { align-items: start; flex-direction: column; gap: 12px; }
    }
    @media (max-width: 640px) {
        .louzir-shell { padding-inline: 20px; }
        .louzir-hero-grid { min-height: min(540px, calc(100svh - 180px)); padding-block: 32px; align-items: end; }
        .louzir-hero::before { background: rgba(0, 0, 0, .48); }
        .louzir-hero-photo { object-position: 64% center; }
        .louzir-hero h1 { font-size: 32px; }
        .louzir-hero-tagline { font-size: 20px; }
        .louzir-hero-description { font-size: 16px; }
        .louzir-actions { gap: 10px; }
        .louzir-actions .louzir-button { flex: 1 1 160px; }
        .louzir-socials { gap: 14px; margin-top: 18px; }
        .louzir-strip-grid, .louzir-bento, .louzir-boutique-grid { grid-template-columns: minmax(0, 1fr); }
        .louzir-strip { padding-block: 24px; }
        .louzir-products, .louzir-boutiques { padding-block: 40px; }
        .louzir-section-head h2, .louzir-community .louzir-title { font-size: 28px; }
        .louzir-card img { height: 280px; }
        .louzir-card-content { padding-top: 18px; }
        .louzir-bento { gap: 32px; }
        .louzir-boutique-info { min-height: 0; }
        .louzir-edition { gap: 8px; }
    }
    @supports (animation-timeline: view()) {
        @media (prefers-reduced-motion: no-preference) {
            .louzir-hero { view-timeline-name: --louzir-hero; view-timeline-axis: block; }
            .louzir-hero-photo {
                animation: louzir-hero-depth linear both;
                animation-timeline: --louzir-hero;
                animation-range: exit 0% exit 100%;
            }
        }
    }
    @keyframes louzir-hero-depth {
        from { transform: scale(1); }
        to { transform: scale(1.06); }
    }
    @media print {
        .louzir-home * { animation: none !important; transform: none !important; opacity: 1 !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        .louzir-home *, .louzir-home .louzir-card img { transition: none; scroll-behavior: auto; }
        .louzir-card:hover img, .louzir-button:hover { transform: none; }
    }
</style>
