const { chromium, expect } = require('@playwright/test');
const fs = require('node:fs');
const assert = require('node:assert/strict');
assert.equal(process.env.DELIVERY_TEST_ALLOW_WRITES, '1', 'Run only on a disposable test database.');
const base = process.env.DELIVERY_TEST_URL || 'http://127.0.0.1:8002';
const fixture = Number(process.env.DELIVERY_TEST_PRODUCT_ID || JSON.parse(fs.readFileSync('storage/framework/testing/product-fixture.json')).id);

(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 1100 } });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);
    const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    let checks = 0, originalShipping, originalProduct;
    const check = (value, message) => { assert.ok(value, message); checks++; console.log('OK: ' + message); };
    const configUrl = base + '/admin/configuration/sales/carriers?locale=fr';
    const editUrl = base + '/admin/catalog/products/edit/' + fixture + '?locale=fr';
    const rate = () => page.locator('[name="sales[carriers][express][default_rate]"]');
    const enabled = () => page.locator('input[type="checkbox"][name="sales[carriers][express][active]"]');
    const saveConfig = async () => {
        const response = page.waitForResponse(r => r.request().method() === 'POST' && r.url().includes('/configuration/sales/carriers'), { timeout: 15000 }).catch(e => e);
        await page.getByRole('button', { name: 'Enregistrer la configuration', exact: true }).click();
        const result = await response;
        check(!(result instanceof Error) && result.status() === 302, 'Shipping form submits');
        await page.waitForLoadState('networkidle');
        await page.goto(configUrl, { waitUntil: 'networkidle' });
        check(!(await page.locator('.text-red-500,.text-red-600').allTextContents()).some(v => v.trim()), 'Shipping form has no validation error');
    };
    const saveProduct = async () => {
        const response = page.waitForResponse(r => r.request().method() === 'POST' && r.url().includes('/catalog/products/edit/'), { timeout: 15000 }).catch(e => e);
        await page.getByRole('button', { name: 'Enregistrer le produit', exact: true }).click();
        const result = await response;
        check(!(result instanceof Error) && result.status() === 302, 'Product form submits');
        const location = await result.headerValue('location');
        check(location && /\/admin\/catalog\/products$/.test(location), 'Product save succeeds');
        await page.waitForURL('**/admin/catalog/products');
        await page.goto(editUrl, { waitUntil: 'networkidle' });
    };
    const guestCheckout = async () => {
        const guest = await browser.newContext({ viewport: { width: 1440, height: 1100 } });
        const shop = await guest.newPage();
        shop.on('pageerror', e => errors.push(e.message));
        await shop.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
        await shop.evaluate(async id => (await app.config.globalProperties.$axios.post('/api/checkout/cart', { product_id: id, quantity: 1, is_buy_now: 0 })).data, fixture);
        await shop.goto(base + '/checkout/onepage', { waitUntil: 'networkidle' });
        for (const [name, value] of [['billing.first_name', 'Test'], ['billing.last_name', 'Livraison'], ['billing.email', 'shipping-test@example.com'], ['billing.address.[0]', '10 Rue de test'], ['billing.phone', '20123456']]) await shop.locator('[name="' + name + '"]').fill(value);
        for (let i = 0; i < 2; i++) {
            await shop.getByRole('combobox').nth(i).click();
            await shop.getByRole('option').first().click();
        }
        const same = shop.locator('[name="billing.use_for_shipping"]');
        if (!await same.isChecked()) await same.check({ force: true });
        const response = shop.waitForResponse(r => r.url().includes('/api/checkout/onepage/addresses') && r.request().method() === 'POST');
        await shop.getByRole('button', { name: 'Continuer', exact: true }).click();
        const result = await response;
        check(result.status() === 200, 'Tunisian guest address accepted');
        const methods = (await result.json()).data.shippingMethods;
        return { guest, shop, methods };
    };
    try {
        await page.goto(base + '/admin/login', { waitUntil: 'networkidle' });
        await page.locator('[name=email]').fill(process.env.DELIVERY_TEST_ADMIN_EMAIL || 'admin@example.com');
        await page.locator('[name=password]').fill(process.env.DELIVERY_TEST_ADMIN_PASSWORD || 'admin123');
        await page.getByRole('button', { name: 'Se connecter', exact: true }).click();
        await page.waitForURL('**/admin/dashboard');
        check((await page.goto(editUrl, { waitUntil: 'networkidle' })).status() === 200, 'Product editor opens');
        const settings = page.locator('[data-attribute-group="settings"]');
        check((await settings.locator('p').first().textContent()).trim() === 'Paramètres', 'Settings heading localized');
        for (const code of ['new', 'featured', 'visible_individually', 'status', 'guest_checkout']) {
            const label = settings.locator('label[for="' + code + '"]').first();
            check((await label.textContent()).trim().length > 1, 'Visible product setting label: ' + code);
        }
        originalProduct = { name: await page.locator('input[name=name]').inputValue(), price: await page.locator('input[name=price]').inputValue(), featured: await page.locator('input#featured').isChecked() };
        await page.locator('input[name=name]').fill('Produit vérifié depuis admin');
        await page.locator('input[name=price]').fill('101.750');
        await page.locator('input#featured').setChecked(!originalProduct.featured, { force: true });
        await saveProduct();
        check(await page.locator('input[name=name]').inputValue() === 'Produit vérifié depuis admin', 'Product name persists');
        check(Number(await page.locator('input[name=price]').inputValue()) === 101.75, 'Product price persists');
        check(await page.locator('input#featured').isChecked() !== originalProduct.featured, 'Product switch persists');
        await settings.scrollIntoViewIfNeeded();
        await page.screenshot({ path: 'storage/framework/testing/product-settings-verified.png' });
        await page.goto(configUrl, { waitUntil: 'networkidle' });
        originalShipping = { rate: await rate().inputValue(), enabled: await enabled().isChecked() };
        await rate().fill('9.500');
        await enabled().check({ force: true });
        await saveConfig();
        check(Number(await rate().inputValue()) === 9.5, 'Admin delivery price persists with three decimals');
        const customer = await guestCheckout();
        try {
            check(Object.keys(customer.methods).join(',') === 'flatrate,express', 'Customer sees normal and express only');
            check(Number(customer.methods.flatrate.rates[0].base_price) === 8, 'Normal delivery is eight dinars');
            check(Number(customer.methods.express.rates[0].base_price) === 9.5, 'Admin rate change reaches checkout');
            for (const method of ['express_express', 'flatrate_flatrate']) {
                const saved = customer.shop.waitForResponse(r => r.url().includes('/api/checkout/onepage/shipping-methods') && r.request().method() === 'POST');
                await customer.shop.locator('label[for="' + method + '"]').last().click();
                check((await saved).status() === 200, 'Customer can select ' + method);
                const summary = await customer.shop.evaluate(async () => (await app.config.globalProperties.$axios.get('/api/checkout/onepage/summary')).data.data);
                check(summary.shipping_method === method && Number(summary.shipping_amount) === (method === 'express_express' ? 9.5 : 8), 'Selected rate updates cart totals');
                await expect(customer.shop.getByText('Frais de livraison', { exact: true }).locator('..').locator('p').last()).toHaveText(summary.formatted_shipping_amount);
                check(true, 'Visible cart summary reflects the selected delivery rate');
            }
            await customer.shop.screenshot({ path: 'storage/framework/testing/checkout-delivery-verified.png' });
        } finally { await customer.guest.close(); }
        await enabled().uncheck({ force: true });
        await saveConfig();
        const disabled = await guestCheckout();
        try { check(!disabled.methods.express && Boolean(disabled.methods.flatrate), 'Admin can disable express without removing normal delivery'); }
        finally { await disabled.guest.close(); }
        check(errors.length === 0, 'No browser errors: ' + errors.join(', '));
        console.log('Product and delivery: ' + checks + ' browser checks passed.');
    } catch (error) {
        console.error(error);
        console.error('At URL: ' + page.url());
        console.error('Validation: ' + (await page.locator('.text-red-500,.text-red-600').allTextContents()).join(' | '));
        await page.screenshot({ path: 'storage/framework/testing/admin-delivery-failure.png' });
        throw error;
    } finally {
        try {
            if (originalShipping) {
                await page.goto(configUrl, { waitUntil: 'networkidle' });
                await rate().fill(originalShipping.rate);
                await enabled().setChecked(originalShipping.enabled, { force: true });
                await saveConfig();
            }
            if (originalProduct) {
                await page.goto(editUrl, { waitUntil: 'networkidle' });
                await page.locator('input[name=name]').fill(originalProduct.name);
                await page.locator('input[name=price]').fill(originalProduct.price);
                await page.locator('input#featured').setChecked(originalProduct.featured, { force: true });
                await saveProduct();
            }
        } finally { await browser.close(); }
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
