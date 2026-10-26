const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
assert.equal(process.env.STOREFRONT_TEST_ALLOW_WRITES, '1', 'Use a disposable database and enable writes explicitly.');
const base = process.env.STOREFRONT_ADMIN_TEST_URL || 'http://127.0.0.1:8002';
(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    const page = await browser.newPage();
    const shop = await browser.newPage();
    let original, checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    const field = key => page.locator('[name="general[louzir_home][storefront][' + key + ']"]');
    const config = base + '/admin/configuration/general/louzir_home?locale=fr';
    const palette = { 'design-background': '#f6f1e9', 'design-surface': '#fff8ee', 'design-panel': '#ebdbc3', 'design-button': '#443322', 'design-button-text': '#ffffff', 'design-muted': '#665544', 'design-accent-strong': '#886644' };
    const save = async () => {
        const saved = page.waitForResponse(r => r.request().method() === 'POST' && r.url().includes('/configuration/general/louzir_home'));
        await page.getByRole('button', { name: 'Enregistrer la configuration', exact: true }).click();
        check((await saved).status() === 302, 'Native design form saves');
        await page.waitForLoadState('networkidle');
        await page.goto(config, { waitUntil: 'networkidle' });
        check((await page.locator('.text-red-500,.text-red-600').allTextContents()).every(t => !t.trim()), 'No native validation errors');
    };
    try {
        await page.goto(base + '/admin/login', { waitUntil: 'networkidle' });
        await page.locator('[name=email]').fill(process.env.DELIVERY_TEST_ADMIN_EMAIL || 'admin@example.com');
        await page.locator('[name=password]').fill(process.env.DELIVERY_TEST_ADMIN_PASSWORD || 'admin123');
        await page.getByRole('button', { name: 'Se connecter', exact: true }).click();
        await page.waitForURL('**/admin/dashboard');
        await page.goto(config, { waitUntil: 'networkidle' });
        original = {};
        for (const key of [...Object.keys(palette), 'design-body-font', 'design-heading-font', 'design-width']) original[key] = await field(key).inputValue();
        for (const [key, value] of Object.entries(palette)) {
            check(await field(key).getAttribute('type') === 'color', 'Native color picker: ' + key);
            await field(key).fill(value);
        }
        await field('design-body-font').selectOption('inter');
        await field('design-heading-font').selectOption('georgia');
        await field('design-width').fill('1320');
        await save();
        check(await field('design-body-font').inputValue() === 'inter', 'Font selection persists');
        check(await field('design-background').inputValue() === '#f6f1e9', 'Background selection persists');
        for (const [key, value] of Object.entries(palette)) check(await field(key).inputValue() === value, 'Saved color persists: ' + key);
        for (const path of ['/', '/search?query=test', '/customer/login', '/checkout/cart']) {
            await shop.goto(base + path, { waitUntil: 'networkidle' });
            const style = await shop.locator('body').evaluate(el => ({ font: getComputedStyle(el).fontFamily, bg: getComputedStyle(el).backgroundColor, width: getComputedStyle(el).getPropertyValue('--store-width').trim() }));
            check(style.font.includes('Inter') && style.bg === 'rgb(246, 241, 233)' && style.width === '1320px', 'Saved theme reaches ' + path);
            for (const [key, value] of Object.entries(palette)) {
                const variable = '--store-' + key.replace('design-', '');
                check(await shop.locator('body').evaluate((el, name) => getComputedStyle(el).getPropertyValue(name).trim(), variable) === value, 'Saved palette reaches ' + path + ': ' + key);
            }
        }
        await field('design-body-font').scrollIntoViewIfNeeded();
        await page.screenshot({ path: 'storage/framework/testing/stitch-theme-admin.png' });
    } finally {
        try {
            if (original) {
                await page.goto(config, { waitUntil: 'networkidle' });
                for (const key of Object.keys(palette)) await field(key).fill(original[key]);
                await field('design-body-font').selectOption(original['design-body-font']);
                await field('design-heading-font').selectOption(original['design-heading-font']);
                await field('design-width').fill(original['design-width']);
                await save();
            }
        } finally { await browser.close(); }
    }
    console.log('Storefront design admin: ' + checks + ' browser checks passed.');
})().catch(e => { console.error(e); process.exitCode = 1; });
