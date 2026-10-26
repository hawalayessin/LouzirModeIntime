const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
const fixture = JSON.parse(require('node:fs').readFileSync('storage/framework/testing/product-fixture.json')).id;
const base = process.env.CHECKOUT_TEST_URL || 'http://127.0.0.1:8002';
assert.equal(process.env.CHECKOUT_TEST_ALLOW_WRITES, '1', 'Use the disposable checkout database.');
(async () => {
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    let checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    try {
        for (const width of [1440, 768, 390, 320]) {
            const context = await browser.newContext({ viewport: { width, height: 1000 } });
            context.setDefaultNavigationTimeout(60000);
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', e => errors.push(e.message));
            await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
            await page.evaluate(async id => app.config.globalProperties.$axios.post('/api/checkout/cart', { product_id: id, quantity: 1, is_buy_now: 0 }), fixture);
            await page.goto(base + '/checkout/onepage', { waitUntil: 'networkidle' });
            for (const [name, value] of [['billing.first_name', 'Test'], ['billing.last_name', 'Checkout'], ['billing.email', 'checkout@example.test'], ['billing.address.[0]', '10 rue de test'], ['billing.phone', '20123456']]) await page.locator('[name="' + name + '"]').fill(value);
            for (let i = 0; i < 2; i++) {
                await page.getByRole('combobox').nth(i).click();
                await page.getByRole('option').first().click();
            }
            await page.locator('[name="billing.use_for_shipping"]').check({ force: true });
            const addressSaved = page.waitForResponse(r => r.url().includes('/api/checkout/onepage/addresses') && r.request().method() === 'POST');
            await page.getByRole('button', { name: 'Continuer', exact: true }).click();
            const methods = (await (await addressSaved).json()).data.shippingMethods;
            check(Number(methods.flatrate.rates[0].base_price) === 8, 'Standard remains 8 DT');
            check(Number(methods.express.rates[0].base_price) === 15, 'Express is 15 DT');
            const shippingSaved = page.waitForResponse(r => r.url().includes('/shipping-methods') && r.request().method() === 'POST');
            await page.locator('label[for="express_express"]').last().click();
            check((await shippingSaved).status() === 200, 'Express can be selected');
            const paymentSaved = page.waitForResponse(r => r.url().includes('/payment-methods') && r.request().method() === 'POST');
            await page.locator('label[for="cashondelivery"]').last().click();
            check((await paymentSaved).status() === 200, 'Payment can be selected');
            const button = page.getByRole('button', { name: 'Passer la commande', exact: true });
            await expect(button).toBeVisible();
            await button.scrollIntoViewIfNeeded();
            const geometry = await button.evaluate(el => {
                const b = el.getBoundingClientRect();
                const a = el.closest('.louzir-checkout-actions').getBoundingClientRect();
                const s = document.querySelector('.louzir-checkout-review .louzir-checkout-summary').getBoundingClientRect();
                return { width: b.width, action: a.width, height: b.height, gap: a.top - s.bottom };
            });
            check(Math.abs(geometry.width - geometry.action) <= 1, 'Order button fills its column');
            check(geometry.height >= 52, 'Comfortable button height');
            if (width >= 768) check(geometry.gap >= 20, 'Button has space below summary');
            check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), 'No horizontal overflow');
            check(errors.length === 0, errors.join('\n'));
            await page.screenshot({ path: 'storage/framework/testing/checkout-submit-' + width + '.png', fullPage: true });
            // Never click Place order: no orders or customer notifications are created.
            await context.close();
        }
    } finally { await browser.close(); }
    console.log('Express delivery and order action: ' + checks + ' browser checks passed.');
})().catch(e => { console.error(e); process.exitCode = 1; });
