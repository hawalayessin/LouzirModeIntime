<style>
    html { overflow-y: scroll; scrollbar-gutter: stable; }
    .louzir-auth-page {
        --auth-ink: #292621;
        --auth-muted: #655f57;
        --auth-border: #d8cec2;
        --auth-accent: #55412f;
        min-height: 100svh;
        padding: 0 32px 48px;
        background: linear-gradient(135deg, #faf8f5, #f0eae2);
        color: var(--auth-ink);
        font-size: 16px;
        font-family: "Poppins", "Segoe UI", sans-serif;
        line-height: 1.6;
    }
    .louzir-auth-page * { letter-spacing: 0; }
    .louzir-brand-header {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 112px;
        max-width: 1200px;
        margin-inline: auto;
    }
    .louzir-brand { display: inline-flex; }
    .louzir-brand-logo { width: 154px; height: auto; max-height: 64px; object-fit: contain; }
    .louzir-auth-layout {
        position: relative;
        isolation: isolate;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 0;
        max-width: 1200px;
        min-height: 620px;
        margin-inline: auto;
        padding: 7px;
        border: 1px solid transparent;
        border-radius: 24px;
        background:
            linear-gradient(145deg, #fff, #fffcf8) padding-box,
            linear-gradient(145deg, #fff 10%, #e8dbcc 45%, #bda78c) border-box;
        box-shadow:
            inset 0 2px 1px rgb(255 255 255 / 95%),
            inset 0 -2px 2px rgb(160 130 96 / 9%),
            0 6px 0 -1px #e0d5c8,
            0 7px 0 0 #bda991,
            0 20px 28px -15px rgb(68 46 25 / 24%),
            0 36px 60px -35px rgb(68 46 25 / 24%);
    }
    .louzir-auth-layout::before {
        content: "";
        position: absolute;
        inset: 7px;
        z-index: 1;
        border: 1px solid rgb(177 150 119 / 18%);
        border-radius: 17px;
        pointer-events: none;
    }
    .louzir-editorial-panel {
        position: relative;
        min-width: 0;
        overflow: hidden;
        border-start-start-radius: 17px;
        border-end-start-radius: 17px;
        background: #eef0ed;
    }
    .louzir-editorial-panel img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .louzir-editorial-label {
        position: absolute;
        top: 32px;
        inset-inline-start: 32px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        color: #303c35;
    }
    .louzir-editorial-line { width: 24px; height: 1px; background: currentColor; }
    .louzir-editorial-copy {
        position: absolute;
        inset-inline: 0;
        bottom: 0;
        padding: 32px;
        background: rgba(255, 255, 255, .94);
    }
    .louzir-editorial-eyebrow { color: var(--auth-accent); font-size: 12px; }
    .louzir-editorial-copy h2 { margin-block: 12px; font-family: "DM Serif Display", Georgia, serif; font-size: 32px; line-height: 1.2; }
    .louzir-editorial-copy p { font-size: 14px; color: var(--auth-muted); }
    .louzir-editorial-bottom, .louzir-header-caption, .louzir-welcome-icon,
    .louzir-form-bottom, .louzir-security-note, .louzir-auth-footer { display: none; }
    .louzir-form-panel {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-width: 0;
        padding: 40px;
    }
    .louzir-register-panel { align-items: flex-start; padding-block: 32px; }
    .louzir-form-content { width: 100%; max-width: 480px; margin-inline: auto; }
    .louzir-form-eyebrow { font-size: 12px; color: var(--auth-accent); margin-bottom: 8px; }
    .louzir-form-title { font-family: "DM Serif Display", Georgia, serif; font-size: 36px; font-weight: 400; line-height: 1.2; text-wrap: balance; overflow-wrap: anywhere; }
    .louzir-form-intro { margin: 12px 0 24px; font-size: 16px; color: var(--auth-muted); }
    .louzir-social-login { display: grid; gap: 12px; }
    .louzir-social-login a, .louzir-login-return {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 48px;
        padding: 10px 16px;
        border: 1px solid var(--auth-border);
        border-radius: 10px;
        background: #fff;
        color: var(--auth-ink);
        font-size: 14px;
        font-weight: 500;
        text-align: center;
    }
    .louzir-social-login svg { width: 20px; height: 20px; flex-shrink: 0; }
    .louzir-form-divider { display: flex; align-items: center; gap: 16px; margin-block: 22px; font-size: 12px; color: var(--auth-muted); }
    .louzir-form-divider span { flex: 1; height: 1px; background: var(--auth-border); }
    .louzir-auth-form label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; line-height: 1.5; }
    .louzir-auth-form .mb-4 { margin-bottom: 20px; }
    .louzir-auth-form input:not([type="checkbox"]):not([type="hidden"]) {
        width: 100%;
        height: 48px;
        padding: 12px 14px;
        border: 1px solid var(--auth-border);
        border-radius: 10px;
        background: #fffdfa;
        color: var(--auth-ink);
        font-size: 16px;
        box-shadow: inset 0 2px 4px rgb(69 48 26 / 5%), 0 1px 0 #fff;
        transition: border-color .18s ease, background-color .18s ease;
    }
    .louzir-auth-form input::placeholder { color: #77716a; opacity: 1; }
    .louzir-auth-form input:not([type="checkbox"]):not([type="hidden"]):hover { border-color: #ac9780; }
    .louzir-auth-form input:not([type="checkbox"]):not([type="hidden"]):focus {
        border-color: #8b6c4c;
        background: #fff;
        box-shadow: 0 0 0 3px rgb(139 108 76 / 12%), inset 0 1px 2px rgb(69 48 26 / 4%);
    }
    .louzir-auth-form [class*="text-red"], .louzir-auth-form .error { font-size: 14px; line-height: 1.5; }
    .louzir-name-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .louzir-form-options { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
    .louzir-password-label { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px; }
    .louzir-auth-form .louzir-password-label > label { margin-bottom: 0; }
    .louzir-password-eye { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 36px; flex: 0 0 40px; padding: 8px; border: 0; border-radius: 6px; background: transparent; color: #655346; cursor: pointer; }
    .louzir-password-eye svg { width: 20px; height: 20px; }
    .louzir-password-eye:hover { background: #f0e9e2; color: #24211d; }
    .louzir-password-eye:focus-visible { outline: 2px solid #8b6c4c; outline-offset: 2px; }
    .louzir-auth-form .louzir-checkbox-line { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; padding-block: 8px; min-height: 40px; font-size: 14px; cursor: pointer; }
    .louzir-checkbox-line input[type="checkbox"] { appearance: auto; position: static; width: 20px; height: 20px; margin: 1px 0 0; flex: 0 0 20px; accent-color: #181818; cursor: pointer; }
    .louzir-checkbox-line span { min-width: 0; overflow-wrap: anywhere; }
    .louzir-terms-link { display: inline-block; margin-bottom: 12px; font-size: 14px; text-decoration: underline; text-underline-offset: 3px; }
    .louzir-forgot-link, .louzir-auth-link { color: var(--auth-accent); text-decoration: underline; text-underline-offset: 3px; font-size: 14px; }
    .louzir-login-button {
        width: 100%;
        min-height: 50px;
        padding: 12px 20px;
        border: 1px solid #39332b;
        border-radius: 10px;
        background: linear-gradient(#35312b, #24211d);
        color: #fff;
        font-size: 16px;
        font-weight: 500;
        box-shadow: inset 0 1px 0 rgb(255 255 255 / 16%), 0 3px 0 #100f0d, 0 7px 12px -5px rgb(41 31 21 / 28%);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    @media (hover: hover) {
        .louzir-login-button:hover { transform: translateY(-2px); box-shadow: inset 0 1px 0 rgb(255 255 255 / 16%), 0 5px 0 #100f0d, 0 10px 16px -6px rgb(41 31 21 / 32%); }
    }
    .louzir-login-button:active { transform: translateY(2px); box-shadow: inset 0 1px 3px rgb(0 0 0 / 18%), 0 1px 0 #100f0d; }
    .louzir-login-return:hover, .louzir-social-login a:hover { background: #faf6f0; border-color: var(--auth-accent); }
    .louzir-auth-switch { margin-top: 24px; text-align: center; color: var(--auth-muted); font-size: 14px; }
    .louzir-login-return { margin-top: 10px; }
    .louzir-auth-alert { margin-block: 16px; padding: 12px 16px; border: 1px solid #ecb7b7; border-radius: 4px; background: #fff3f3; color: #9e2020; font-size: 14px; overflow-wrap: anywhere; }
    .louzir-auth-page :is(a, button, input):focus-visible { outline: 2px solid #795b3d; outline-offset: 3px; }
    .louzir-auth-page :is(a, button) { touch-action: manipulation; }
    @media (max-width: 1000px) {
        .louzir-auth-page { padding-inline: 24px; }
        .louzir-auth-layout { grid-template-columns: minmax(0, .8fr) minmax(0, 1fr); }
        .louzir-form-panel { padding: 32px 24px; }
    }
    @media (max-width: 760px) {
        .louzir-auth-page { padding: 0 20px 40px; }
        .louzir-brand-header { min-height: 88px; }
        .louzir-auth-layout { display: block; min-height: 0; max-width: 480px; border-radius: 20px; }
        .louzir-auth-layout::before { border-radius: 13px; }
        .louzir-editorial-panel { min-height: 180px; border-radius: 13px 13px 0 0; }
        .louzir-editorial-panel img { object-position: center 42%; }
        .louzir-editorial-label { top: 20px; inset-inline-start: 20px; }
        .louzir-editorial-copy { display: none; }
        .louzir-form-panel { padding: 28px 24px; }
        .louzir-form-title { font-size: 28px; }
        .louzir-form-intro { font-size: 16px; }
    }
    @media (max-width: 420px) {
        .louzir-auth-page { padding-inline: 16px; }
        .louzir-form-panel { padding: 28px 20px; }
        .louzir-name-grid { grid-template-columns: minmax(0, 1fr); gap: 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        .louzir-login-button, .louzir-auth-form input { transition: none !important; }
        .louzir-login-button:hover, .louzir-login-button:active { transform: none; }
    }
</style>
