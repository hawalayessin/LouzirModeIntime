const assert = require('node:assert/strict');
const fs = require('node:fs');
const { execFileSync } = require('node:child_process');
const { chromium } = require('@playwright/test');

const base = process.env.RESPONSIVE_TEST_URL || 'http://127.0.0.1:8000';
assert(['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Local test server required');
const fixtureScript = 'tests/browser/responsive-fixture.php';
const output = 'storage/framework/testing/responsive-storefront';
fs.mkdirSync(output, { recursive: true });
const results = [];
function check(condition, name, detail) {
    results.push({ name, passed: Boolean(condition), detail });
    fs.writeFileSync(output + '/results.json', JSON.stringify(results, null, 2));
}

(async () => {
    const fixture = JSON.parse(execFileSync('php', [fixtureScript], { encoding: 'utf8' }));
    let browser;
    try {
        browser = await chromium.launch({ channel: 'msedge', headless: true });
        const context = await browser.newContext();
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));

        for (const width of process.env.RESPONSIVE_SKIP_HEADER ? [] : [1024, 1180, 1280, 1366, 1440, 1920, 2560, 3840]) {
            await page.setViewportSize({ width, height: 1080 });
            await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
            const layout = await page.locator('.louzir-desktop-bar').evaluate(bar => {
                const rect = element => { const r = element.getBoundingClientRect(); return { x: r.x, y: r.y, width: r.width, height: r.height, right: r.right }; };
                return { bar: rect(bar), items: [...bar.querySelectorAll('.louzir-category-item')].map(rect), search: rect(bar.querySelector('.louzir-desktop-search')), overflow: document.documentElement.scrollWidth > innerWidth + 1 };
            });
            check(layout.bar.height <= 84, 'Navbar remains one row at ' + width, layout);
            check(new Set(layout.items.map(item => Math.round(item.y))).size === 1, 'Categories share one baseline at ' + width);
            check(!layout.overflow, 'No page overflow at ' + width);
            if ([1366, 3840].includes(width)) await page.locator('.louzir-header').screenshot({ path: output + '/navbar-' + width + '.png' });
            if (width === 1366) {
                const item = page.locator('.louzir-category-item').filter({ has: page.locator('.louzir-category-panel') }).first();
                await item.hover();
                const panel = item.locator('.louzir-category-panel');
                check(await panel.isVisible(), 'Category submenu opens on hover');
                const bounds = await panel.boundingBox();
                check(bounds.x >= 0 && bounds.x + bounds.width <= width, 'Category submenu remains inside the viewport');
                await page.locator('.louzir-desktop-search input[name=query]').focus();
                await page.keyboard.press('Escape');
                await page.mouse.move(0, 0);
            }
        }

        await page.setViewportSize({ width: 390, height: 900 });
        await page.goto(base + '/customer/login', { waitUntil: 'networkidle' });
        const cookie = page.locator('.cookie-reject');
        if (await cookie.isVisible()) await cookie.click();
        await page.locator('input[name=email]').fill(fixture.email);
        await page.locator('input[name=password]').fill(fixture.password);
        const agreement = page.locator('.louzir-agreement input[type=checkbox]');
        if (await agreement.isVisible()) await agreement.check();
        await Promise.all([page.waitForURL(url => !url.pathname.endsWith('/customer/login')), page.locator('form button[type=submit]').click()]);

        for (const width of [320, 390, 480, 768, 1440]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.goto(base + '/customer/account/profile/edit', { waitUntil: 'networkidle' });
            const avatar = await page.locator('form img[src*="responsive-regression/avatar"]').evaluate(image => {
                const r = image.getBoundingClientRect(), frame = image.parentElement.getBoundingClientRect();
                return { width: r.width, height: r.height, x: r.x, right: r.right, frameWidth: frame.width, frameHeight: frame.height, objectFit: getComputedStyle(image).objectFit, pageWidth: innerWidth };
            });
            check(Math.abs(avatar.width - avatar.frameWidth) < 1 && Math.abs(avatar.height - avatar.frameHeight) < 1, 'Avatar fills the frame at ' + width, avatar);
            check(avatar.objectFit === 'cover', 'Avatar preserves its proportions at ' + width);
            if (width < 768) check(Math.abs((avatar.x + avatar.right) / 2 - width / 2) < 3, 'Avatar centered on mobile at ' + width, avatar);
            if (width === 390) await page.screenshot({ path: output + '/profile-390.png', fullPage: true });
        }

        await page.setViewportSize({ width: 390, height: 900 });
        await page.locator('input[type=file][name="image[]"]').setInputFiles({
            name: 'landscape.svg', mimeType: 'image/svg+xml',
            buffer: Buffer.from('<svg xmlns="http://www.w3.org/2000/svg" width="240" height="80"><rect width="240" height="80" fill="#d7e5de"/></svg>'),
        });
        await page.waitForFunction(() => document.querySelector('form img[src^="data:"]')?.complete);
        const preview = await page.locator('form img[src^="data:"]').evaluate(image => {
            const bounds = image.getBoundingClientRect(), frame = image.parentElement.getBoundingClientRect();
            return Math.abs(bounds.width - frame.width) < 1 && Math.abs(bounds.height - frame.height) < 1;
        });
        check(preview, 'New landscape upload also fills the avatar frame');
        await page.goto(base + '/customer/account/orders', { waitUntil: 'networkidle' });
        for (const width of [320, 390, 480, 768]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.locator('.icon-filter').filter({ visible: true }).click();
            await page.waitForFunction(() => {
                const bounds = [...document.querySelectorAll('input[name$="[from]"], input[name$="[to]"]')]
                    .map(input => input.getBoundingClientRect()).filter(bounds => bounds.width > 0);
                return bounds.length === 2 && bounds.every(bounds => bounds.x >= 0 && bounds.right <= innerWidth);
            });
            const dates = await page.locator('input[name$="[from]"], input[name$="[to]"]').evaluateAll(inputs => inputs.filter(input => input.getBoundingClientRect().width).map(input => {
                const style = getComputedStyle(input), icon = input.parentElement.querySelector('.icon-calendar'), bounds = input.getBoundingClientRect();
                return { padding: parseFloat(style.paddingInlineEnd), iconWidth: icon?.getBoundingClientRect().width || 24, x: bounds.x, right: bounds.right };
            }));
            check(dates.length === 2 && dates.every(date => date.padding >= date.iconWidth + 12 && date.x >= 0 && date.right <= width + 1), 'Dates reserve calendar space and fit at ' + width, dates);
            if (width === 390) await page.screenshot({ path: output + '/order-filters-390.png' });
            await page.locator('.icon-cancel').filter({ visible: true }).click();
        }

        // Exercise the real checkout with the temporary customer, stopping before order creation.
        await page.goto(base + '/customer/account/profile/edit', { waitUntil: 'networkidle' });
        const csrf = await page.locator('input[name=_token]').first().inputValue();
        const products = await (await page.request.get(base + '/api/products')).json();
        const product = products.data.find(product => product.is_saleable);
        assert(product, 'A saleable local product is required');
        const added = await page.request.post(base + '/api/checkout/cart', { headers: { 'X-CSRF-TOKEN': csrf }, data: { product_id: product.id, quantity: 1, is_buy_now: 0 } });
        assert.equal(added.status(), 200, 'Local test product added to fixture cart');
        await page.goto(base + '/checkout/onepage', { waitUntil: 'networkidle' });
        if (await cookie.isVisible()) await cookie.click();
        for (const [name, value] of [['billing.first_name', 'Responsive'], ['billing.last_name', 'Regression'], ['billing.email', fixture.email], ['billing.address.[0]', '10 rue de test'], ['billing.phone', '22123456']]) {
            await page.locator('[name="' + name + '"]').fill(value);
        }
        for (let index = 0; index < 2; index++) {
            await page.getByRole('combobox').nth(index).click();
            await page.getByRole('option').first().click();
        }
        await page.getByRole('button', { name: 'Enregistrer', exact: true }).click();
        await page.locator('[name="billing.use_for_shipping"]').check({ force: true });
        const addressSaved = page.waitForResponse(response => response.url().includes('/api/checkout/onepage/addresses') && response.request().method() === 'POST');
        await page.getByRole('button', { name: 'Continuer', exact: true }).click();
        assert.equal((await addressSaved).status(), 200, 'Fixture billing address accepted');
        const shippingSaved = page.waitForResponse(response => response.url().includes('/shipping-methods') && response.request().method() === 'POST');
        await page.locator('label[for="express_express"]').last().click();
        assert.equal((await shippingSaved).status(), 200, 'Admin-configured shipping method selected');
        const paymentSaved = page.waitForResponse(response => response.url().includes('/payment-methods') && response.request().method() === 'POST');
        await page.locator('label[for="cashondelivery"]').last().click();
        assert.equal((await paymentSaved).status(), 200, 'Admin-configured payment method selected');
        for (const width of [320, 390, 480, 768, 1440]) {
            await page.setViewportSize({ width, height: 1000 });
            for (const direction of ['ltr', 'rtl']) {
                await page.evaluate(direction => document.documentElement.dir = direction, direction);
                const choices = await page.locator('input[name="shipping_method"], input[name="payment[method]"]').evaluateAll(inputs => inputs.map(input => {
                    const labels = [...input.parentElement.querySelectorAll('label')];
                    const marker = labels.find(label => label.className.includes('icon-radio'));
                    const content = labels.find(label => label !== marker);
                    const m = marker.getBoundingClientRect();
                    const overlaps = [...content.querySelectorAll('p')].some(text => {
                        const range = document.createRange(); range.selectNodeContents(text);
                        return [...range.getClientRects()].some(r => r.left < m.right && r.right > m.left && r.top < m.bottom && r.bottom > m.top);
                    });
                    return { overlaps, name: input.name, keyboardAccessible: getComputedStyle(input).display !== 'none' };
                }));
                check(choices.length === 3 && choices.every(choice => !choice.overlaps), 'Checkout radio markers do not overlap text at ' + width + '/' + direction, choices);
                check(choices.every(choice => choice.keyboardAccessible), 'Checkout radio inputs remain keyboard accessible at ' + width + '/' + direction);
                if (direction === 'ltr') await page.screenshot({ path: output + '/checkout-' + width + '.png', fullPage: true });
            }
        }
        await page.evaluate(() => document.documentElement.dir = 'ltr');
        await page.locator('input[name="shipping_method"]').first().focus();
        const keyboardSelection = page.waitForResponse(response => response.url().includes('/shipping-methods') && response.request().method() === 'POST');
        await page.keyboard.press('Space');
        check((await keyboardSelection).status() === 200, 'Shipping method can be changed using the keyboard');
        for (const locale of ['en', 'ar']) {
            for (const width of [390, 1366, 1920]) {
                await page.setViewportSize({ width, height: 1000 });
                await page.goto(base + '/?locale=' + locale, { waitUntil: 'networkidle' });
                const layout = await page.evaluate(() => ({ language: document.documentElement.lang, direction: document.documentElement.dir, overflow: document.documentElement.scrollWidth > innerWidth + 1, height: document.querySelector('.louzir-desktop-bar')?.getBoundingClientRect().height }));
                check(layout.language === locale && layout.direction === (locale === 'ar' ? 'rtl' : 'ltr'), 'Correct navbar locale at ' + width + '/' + locale);
                check(!layout.overflow && (width < 1024 || layout.height <= 84), 'Navbar fits at ' + width + '/' + locale, layout);
            }
        }
        check(errors.length === 0, 'No JavaScript errors', errors);
        await context.close();
    } finally {
        await browser?.close();
        execFileSync('php', [fixtureScript, 'cleanup'], { stdio: 'pipe' });
        fs.writeFileSync(output + '/results.json', JSON.stringify(results, null, 2));
    }
    const failed = results.filter(result => !result.passed);
    console.log(JSON.stringify({ checks: results.length, failed }, null, 2));
    assert.equal(failed.length, 0, failed.length + ' responsive checks failed');
})().catch(error => { console.error(error.message); process.exitCode = 1; });
