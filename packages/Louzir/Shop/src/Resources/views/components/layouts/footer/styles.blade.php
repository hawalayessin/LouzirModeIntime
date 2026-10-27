    <style>
        .louzir-footer { background: #f4f3f1; color: #1a1c1b; font-size: 13px; line-height: 1.7; }
        .louzir-footer * { box-sizing: border-box; letter-spacing: 0; }
        .louzir-footer-inner { max-width: 1440px; margin: auto; padding: 36px 64px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); align-items: start; gap: 32px; }
        .louzir-footer-inner > * { min-width: 0; }
        .louzir-footer-brand { display: flex; flex-direction: column; align-items: flex-start; }
        .louzir-footer-links { align-content: start; }
        .louzir-footer-logo { display: inline-flex; align-items: center; width: 160px; max-width: 100%; height: 64px; margin-bottom: 4px; }
        .louzir-footer-logo img { display: block; width: 100%; height: 100%; object-fit: contain; }
        .louzir-footer h2 { font-family: "DM Serif Display", Georgia, serif; font-size: 26px; line-height: 1.2; }
        .louzir-footer h3 { font-size: 14px; font-weight: 600; margin-bottom: 14px; }
        .louzir-footer p { margin: 6px 0; color: #555753; }
        .louzir-footer a { overflow-wrap: anywhere; }
        .louzir-footer a:hover { text-decoration: underline; }
        .louzir-footer a:focus-visible { outline: 2px solid #79573d; outline-offset: 4px; }
        .louzir-footer nav { display: grid; gap: 9px; }
        .louzir-footer-contact { display: grid; gap: 6px; margin-top: 14px; }
        .louzir-footer .louzir-footer-social { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
        .louzir-footer-social a { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 44px; width: 44px; height: 44px; border: 1px solid var(--store-border, #e3e2e0); border-radius: 50%; background: var(--store-surface, #fff); color: inherit; text-decoration: none; transition: transform .18s ease, box-shadow .18s ease; }
        .louzir-footer-social svg { display: block; flex: none; }
        @media (hover: hover) and (pointer: fine) {
            .louzir-footer-social a:hover { transform: translateY(-2px); box-shadow: 0 4px 12px #13151512; text-decoration: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .louzir-footer-social a { transition: none; }
            .louzir-footer-social a:hover { transform: none; }
        }
        .louzir-footer-store { display: grid; gap: 4px; border-inline-start: 2px solid currentColor; padding-inline-start: 12px; }
        .louzir-footer-store + .louzir-footer-store { margin-top: 20px; }
        .louzir-footer-store strong { font-weight: 600; }
        .louzir-footer-store p { margin: 0; }
        .louzir-footer-store a { color: #79573d; }
        .louzir-footer-newsletter form { margin-top: 16px; }
        .louzir-footer-newsletter label { display: block; margin-bottom: 6px; }
        .louzir-footer-newsletter input { width: 100%; min-width: 0; min-height: 44px; border: 1px solid #b9b9b2; border-radius: 4px; background: #fff; padding: 10px 12px; font-size: 16px; }
        .louzir-newsletter-submit { min-height: 44px; width: 100%; padding: 10px 16px; border-radius: 4px; background: #1a1c1b; color: #fff; font-weight: 600; cursor: pointer; }
        .louzir-newsletter-submit:hover { background: #333733; }
        .louzir-footer-newsletter input:focus-visible,
        .louzir-newsletter-submit:focus-visible { outline: 2px solid #79573d; outline-offset: 3px; }
        .louzir-footer-bottom { border-top: 1px solid #deded9; padding: 14px 24px; text-align: center; font-size: 12px; }
        @media (max-width: 1100px) { .louzir-footer-inner { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 30px 32px; } }
        @media (max-width: 580px) { .louzir-footer-inner { grid-template-columns: 1fr; gap: 24px; padding: 28px 20px; } }
    </style>
