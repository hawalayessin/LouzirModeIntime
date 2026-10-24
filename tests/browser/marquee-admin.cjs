const { chromium } = require('@playwright/test');
const assert = require('node:assert/strict');

// Run only against a disposable local database: this exercises real admin saves.
assert.equal(process.env.MARQUEE_TEST_ALLOW_WRITES, '1', 'Set MARQUEE_TEST_ALLOW_WRITES=1 for an isolated test site.');
const base = process.env.HERO_TEST_URL || 'http://127.0.0.1:8000';
const adminUrl = base + '/admin/configuration/general/louzir_home?locale=fr';
const name = key => 'general[louzir_home][marquee][' + key + ']';
const colors = ['marquee-background', 'marquee-color', 'marquee-accent'];
const palette = ['#243b53', '#ffffff', '#ffcc88'];

(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);
    page.setDefaultNavigationTimeout(60000);
    const errors = [];
    let checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    page.on('pageerror', error => errors.push(error.message));
    const field = key => page.locator('input[name="' + name(key) + '"]');
    const toggle = () => page.getByRole('switch', { name: 'Afficher le bandeau défilant', exact: true });
    const save = async () => {
        const response = page.waitForResponse(response => response.request().method() === 'POST' && response.url().includes('/configuration/general/louzir_home'), { timeout: 60000 }).catch(error => error);
        await page.getByRole('button', { name: 'Enregistrer la configuration', exact: true }).click();
        let result;
        try { result = await response; if (result instanceof Error) throw result; }
        catch (error) { throw new Error('Admin save failed: ' + (await page.locator('.text-red-600,.text-red-500').allTextContents()).join('; ')); }
        check(result.status() === 302, 'Admin accepts the complete form');
        await page.waitForLoadState('networkidle');
        await page.reload({ waitUntil: 'networkidle' });
        check((await page.locator('.text-red-600,.text-red-500').allTextContents()).every(message => !message.trim()), 'No admin validation error');
    };
    let original;
    try {
        await page.goto(base + '/admin/login', { waitUntil: 'networkidle' });
        await page.locator('[name="email"]').fill(process.env.MARQUEE_TEST_ADMIN_EMAIL || 'admin@example.com');
        await page.locator('[name="password"]').fill(process.env.MARQUEE_TEST_ADMIN_PASSWORD || 'admin123');
        await page.getByRole('button', { name: 'Se connecter', exact: true }).click();
        await page.waitForURL('**/admin/dashboard');
        await page.goto(adminUrl, { waitUntil: 'networkidle' });
        original = { enabled: await toggle().isChecked(), colors: await Promise.all(colors.map(key => field(key).inputValue())) };
        check(await field('marquee-enabled').count() === 1, 'Switch submits one explicit scalar value');
        for (let i = 0; i < colors.length; i++) {
            check(await field(colors[i]).getAttribute('type') === 'color', 'Native admin color picker');
            await field(colors[i]).fill(palette[i]);
        }
        await toggle().uncheck({ force: true });
        await save();
        check(!await toggle().isChecked(), 'Disabled state persists after reload');
        const shop = await context.newPage();
        await shop.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
        check(await shop.locator('.louzir-marquee').count() === 0, 'Disabled marquee is absent from the storefront');
        await toggle().check({ force: true });
        await save();
        check(await toggle().isChecked(), 'Enabled state persists after reload');
        for (let i = 0; i < colors.length; i++) check(await field(colors[i]).inputValue() === palette[i], 'Color persists after reload');
        for (const locale of ['fr', 'en']) {
            await shop.goto(base + '/?locale=' + locale, { waitUntil: 'networkidle' });
            check(await shop.locator('.louzir-marquee').count() === 1, 'Enabled marquee visible in ' + locale);
            const styles = await shop.locator('.louzir-marquee').evaluate(el => ({
                background: getComputedStyle(el).backgroundColor,
                color: getComputedStyle(el).color,
                accent: getComputedStyle(el.querySelector('.icon-star')).color,
            }));
            check(styles.background === 'rgb(36, 59, 83)' && styles.color === 'rgb(255, 255, 255)' && styles.accent === 'rgb(255, 204, 136)', 'Saved colors applied in ' + locale);
        }
        await shop.screenshot({ path: 'storage/framework/testing/marquee-colors.png' });
        await shop.close();
        await toggle().scrollIntoViewIfNeeded();
        await page.screenshot({ path: 'storage/framework/testing/marquee-admin.png' });
        check(errors.length === 0, 'No JavaScript error: ' + errors.join(', '));
        console.log('Marquee admin: ' + checks + ' browser checks passed.');
    } finally {
        try {
            if (original) {
                await page.goto(adminUrl, { waitUntil: 'networkidle' });
                await toggle().setChecked(original.enabled, { force: true });
                for (let i = 0; i < colors.length; i++) await field(colors[i]).fill(original.colors[i]);
                await save();
            }
        } finally {
            await browser.close();
        }
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
