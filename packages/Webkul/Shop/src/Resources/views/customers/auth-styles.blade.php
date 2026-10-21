<style>
    html { overflow-y: scroll; scrollbar-gutter: stable; }
    .louzir-auth-page {
        --auth-ink: #202222;
        --auth-muted: #595959;
        --auth-border: #dedede;
        --auth-accent: #181818;
        min-height: 100svh;
        padding: 0 32px 48px;
        background: #fff;
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
        min-height: 96px;
        max-width: 1200px;
        margin-inline: auto;
    }
    .louzir-brand { display: inline-flex; }
    .louzir-brand-logo { width: 154px; height: auto; max-height: 64px; object-fit: contain; }
    .louzir-auth-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        max-width: 1200px;
        min-height: 620px;
        margin-inline: auto;
        background: #fff;
    }
    .louzir-editorial-panel {
        position: relative;
        min-width: 0;
        overflow: hidden;
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
    .louzir-form-panel { display: flex; flex-direction: column; justify-content: center; align-items: center; min-width: 0; padding: 40px 48px; }
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
        border-radius: 4px;
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
        border-radius: 4px;
        background: #fff;
        color: var(--auth-ink);
        font-size: 16px;
        box-shadow: none;
    }
    .louzir-auth-form [class*="text-red"], .louzir-auth-form .error { font-size: 14px; line-height: 1.5; }
    .louzir-name-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .louzir-form-options { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
    .louzir-auth-form .louzir-checkbox-line { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; padding-block: 8px; min-height: 40px; font-size: 14px; cursor: pointer; }
    .louzir-checkbox-line input[type="checkbox"] { appearance: auto; position: static; width: 20px; height: 20px; margin: 1px 0 0; flex: 0 0 20px; accent-color: #181818; cursor: pointer; }
    .louzir-checkbox-line span { min-width: 0; overflow-wrap: anywhere; }
    .louzir-terms-link { display: inline-block; margin-bottom: 12px; font-size: 14px; text-decoration: underline; text-underline-offset: 3px; }
    @media (prefers-reduced-motion: reduce) {
        .louzir-login-button { transition: none; }
        .louzir-login-button:hover { transform: none; }
    }
    .louzir-forgot-link, .louzir-auth-link { color: var(--auth-accent); text-decoration: underline; text-underline-offset: 3px; font-size: 14px; }
    .louzir-login-button { width: 100%; min-height: 48px; padding: 12px 20px; border-radius: 4px; background: #202222; color: #fff; font-size: 16px; font-weight: 500; }
    .louzir-login-button { transition: transform .18s ease, box-shadow .18s ease; }
    .louzir-login-button:hover { background: #202222; transform: translateY(-2px); box-shadow: 0 5px 12px rgba(0, 0, 0, .14); }
    .louzir-login-button:active { transform: translateY(0); box-shadow: none; }
    .louzir-login-return:hover, .louzir-social-login a:hover { background: #f5f5f5; border-color: var(--auth-accent); }
    .louzir-auth-switch { margin-top: 24px; text-align: center; color: var(--auth-muted); font-size: 14px; }
    .louzir-login-return { margin-top: 10px; }
    .louzir-auth-alert { margin-block: 16px; padding: 12px 16px; border: 1px solid #ecb7b7; border-radius: 4px; background: #fff3f3; color: #9e2020; font-size: 14px; overflow-wrap: anywhere; }
    .louzir-auth-page :is(a, button, input):focus-visible { outline: 2px solid #181818; outline-offset: 3px; }
    .louzir-auth-page :is(a, button) { touch-action: manipulation; }
    @media (max-width: 1000px) {
        .louzir-auth-page { padding-inline: 24px; }
        .louzir-auth-layout { grid-template-columns: minmax(0, .8fr) minmax(0, 1fr); }
        .louzir-form-panel { padding: 32px 24px; }
    }
    @media (max-width: 760px) {
        .louzir-auth-page { padding: 0 20px 40px; }
        .louzir-brand-header { min-height: 88px; }
        .louzir-auth-layout { display: block; min-height: 0; max-width: 480px; }
        .louzir-editorial-panel { display: none; }
        .louzir-form-panel { padding: 16px 0 24px; }
        .louzir-form-title { font-size: 28px; }
        .louzir-form-intro { font-size: 16px; }
    }
    @media (max-width: 420px) {
        .louzir-name-grid { grid-template-columns: minmax(0, 1fr); gap: 0; }
    }
</style>
