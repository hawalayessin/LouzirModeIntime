const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = process.env.PALETTE_TEST_URL || 'http://127.0.0.1:8002';
assert.equal(process.env.PALETTE_TEST_ALLOW_WRITES, '1', 'Use the disposable database for cart sessions.');
(async () => {
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    let checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    try {
        const product = JSON.parse(fs.readFileSync('storage/framework/testing/product-fixture.json')).id;
        for (const locale of (process.env.PALETTE_TEST_LOCALES || 'fr,en,ar').split(',')) {
            for (const width of (process.env.PALETTE_TEST_WIDTHS || '1440,390,320').split(',').map(Number)) {
                const context = await browser.newContext({ viewport: { width, height: 1000 } });
                context.setDefaultNavigationTimeout(60000);
                const page = await context.newPage();
                const errors = [];
                page.on('pageerror', error => errors.push(error.message));
                await page.goto(base + '/?locale=' + locale, { waitUntil: 'networkidle' });
                await page.evaluate(async id => {
                    const result = await app.config.globalProperties.$axios.post('/api/checkout/cart', { product_id: id, quantity: 1, is_buy_now: 0 });
                    if (!result.data.data) throw new Error('Cart fixture could not be added');
                }, product);
                for (const path of ['/checkout/cart', '/checkout/onepage']) {
                    check((await page.goto(base + path, { waitUntil: 'networkidle' })).status() === 200, 'Page opens: ' + locale + width + path);
                    await expect(page.locator('.louzir-page-intro h1')).toBeVisible();
                    check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), 'No overflow: ' + locale + width + path);
                    const panel = page.locator(path.endsWith('/cart') ? '.louzir-cart-summary' : '.louzir-checkout-summary:visible');
                    await expect(panel).toBeVisible();
                    check(await panel.evaluate(el => getComputedStyle(el).backgroundColor !== 'rgb(255, 255, 255)'), 'Colored summary: ' + locale + width + path);
                    check(!(await page.locator('body').innerText()).includes('shop::storefront.'), 'No untranslated theme keys');
                    if (locale === 'ar') check(await page.locator('html').getAttribute('dir') === 'rtl', 'Arabic RTL');
                    if (locale === 'fr' && width !== 320) await page.screenshot({ path: 'storage/framework/testing/palette-' + width + '-' + path.split('/').pop() + '.png', fullPage: true });
                }
                check(errors.length === 0, errors.join('\n'));
                await context.close();
            }
        }
    } finally { await browser.close(); }
    console.log('Palette, cart, checkout, FR/EN/AR: ' + checks + ' browser checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
