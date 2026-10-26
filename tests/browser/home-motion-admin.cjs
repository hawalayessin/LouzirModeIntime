const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
assert.equal(process.env.MOTION_TEST_ALLOW_WRITES, '1', 'Use a disposable database and explicitly enable test writes.');
const base = process.env.MOTION_TEST_URL || 'http://127.0.0.1:8002';
(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    const admin = await browser.newPage();
    const shop = await browser.newPage();
    let original, checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    const config = base + '/admin/configuration/general/louzir_home?locale=fr';
    const field = key => admin.locator('[name="general[louzir_home][motion][' + key + ']"]');
    const toggle = () => admin.getByRole('switch', { name: 'Activer les animations des sections et boutons', exact: true });
    const save = async () => {
        const saved = admin.waitForResponse(r => r.request().method() === 'POST' && r.url().includes('/configuration/general/louzir_home'));
        await admin.getByRole('button', { name: 'Enregistrer la configuration', exact: true }).click();
        check((await saved).status() === 302, 'Native admin form saves');
        await admin.waitForLoadState('networkidle');
        await admin.goto(config, { waitUntil: 'networkidle' });
        check((await admin.locator('.text-red-500,.text-red-600').allTextContents()).every(t => !t.trim()), 'No validation errors');
    };
    try {
        await admin.goto(base + '/admin/login', { waitUntil: 'networkidle' });
        await admin.locator('[name=email]').fill(process.env.DELIVERY_TEST_ADMIN_EMAIL || 'admin@example.com');
        await admin.locator('[name=password]').fill(process.env.DELIVERY_TEST_ADMIN_PASSWORD || 'admin123');
        await admin.getByRole('button', { name: 'Se connecter', exact: true }).click();
        await admin.waitForURL('**/admin/dashboard');
        await admin.goto(config, { waitUntil: 'networkidle' });
        original = { enabled: await toggle().isChecked(), duration: await field('motion-duration').inputValue(), stagger: await field('motion-stagger').inputValue() };
        await toggle().uncheck({ force: true });
        await field('motion-duration').fill('500');
        await field('motion-stagger').fill('80');
        await save();
        check(!await toggle().isChecked(), 'Disabled state persists');
        check(await field('motion-duration').inputValue() === '500' && await field('motion-stagger').inputValue() === '80', 'Timing values persist');
        await shop.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
        await expect(shop.locator('.louzir-home')).toHaveAttribute('data-motion-enabled', '0');
        await expect(shop.locator('.louzir-home')).toHaveAttribute('data-motion-duration', '500');
        await expect(shop.locator('.louzir-home')).toHaveAttribute('data-motion-stagger', '80');
        check(await shop.locator('.is-active .louzir-hero-photo').evaluate(el => getComputedStyle(el).animationName === 'none'), 'Admin disable reaches storefront');
        await toggle().check({ force: true });
        await save();
        await shop.reload({ waitUntil: 'networkidle' });
        await expect(shop.locator('.louzir-home')).toHaveAttribute('data-motion-enabled', '1');
        check(true, 'Admin enable reaches storefront');
        await toggle().scrollIntoViewIfNeeded();
        await admin.screenshot({ path: 'storage/framework/testing/home-motion-admin.png' });
    } finally {
        try {
            if (original) {
                await admin.goto(config, { waitUntil: 'networkidle' });
                await toggle().setChecked(original.enabled, { force: true });
                await field('motion-duration').fill(original.duration);
                await field('motion-stagger').fill(original.stagger);
                await save();
            }
        } finally { await browser.close(); }
    }
    console.log('Home motion admin: ' + checks + ' browser checks passed.');
})().catch(e => { console.error(e); process.exitCode = 1; });
