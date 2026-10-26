const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
const base = process.env.STOREFRONT_TEST_URL || 'http://127.0.0.1:8000';
(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    let checks = 0;
    const errors = [];
    const check = (value, message) => { assert.ok(value, message); checks++; };
    try {
        for (const width of (process.env.STOREFRONT_TEST_WIDTHS || '1440,768,390,320').split(',').map(Number)) {
            const context = await browser.newContext({ viewport: { width, height: 1000 }, isMobile: width < 768, hasTouch: width < 768, deviceScaleFactor: 2 });
            context.setDefaultNavigationTimeout(60000);
            context.setDefaultTimeout(45000);
            const page = await context.newPage();
            page.on('pageerror', e => errors.push(e.message));
            for (const path of ['/test-lingerie', '/test-louzir-demo-008', '/search?query=test', '/checkout/cart', '/customer/login', '/customer/register', '/customer/forgot-password', '/compare', '/']) {
                const response = await page.goto(base + path, { waitUntil: 'networkidle' });
                check(response.status() === 200, 'Page opens: ' + width + ' ' + path);
                const reject = page.getByRole('button', { name: 'Rejeter', exact: true });
                if (await reject.isVisible()) await reject.click();
                await page.evaluate(() => document.fonts.ready);
                check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), 'No overflow: ' + width + ' ' + path);
                const fonts = await page.evaluate(() => ({ body: getComputedStyle(document.body).fontFamily, heading: document.querySelector('h1,h2') && getComputedStyle(document.querySelector('h1,h2')).fontFamily }));
                check(fonts.body.includes('Manrope'), 'Shared body font: ' + path);
                if (fonts.heading) check(fonts.heading.includes('Playfair Display'), 'Shared heading font: ' + path);
                if (path === '/test-lingerie' || path.startsWith('/search')) {
                    await page.locator('.louzir-product-card').first().waitFor();
                    const card = page.locator('.louzir-product-card').first();
                    check(await card.locator('.louzir-product-card-title').getAttribute('href') !== null, 'Product title links to real product');
                    const action = card.locator('.louzir-product-card-info button').first();
                    await expect(action).toBeVisible();
                    check(true, 'Add to cart remains available at ' + width);
                    if (path === '/test-lingerie') {
                        await expect(page.locator('#collection-title')).toHaveText('Lingerie (test)');
                        check(await page.locator('.louzir-category-pills a').count() > 0, 'Subcategories use catalog data');
                        if (width < 768) {
                            await page.locator('[data-catalog-filter-trigger]').click();
                            await expect(page.locator('.louzir-filter-panel').last()).toBeVisible();
                            check(true, 'Mobile filter drawer opens');
                            await page.goto(base + path, { waitUntil: 'networkidle' });
                        } else check(await page.locator('.louzir-filter-sidebar').isVisible(), 'Desktop filters remain available');
                    }
                }
                if (path === '/test-louzir-demo-008') {
                    await expect(page.locator('.louzir-product-purchase')).toBeVisible();
                    check(await page.locator('[data-product-description]').count() === 1, 'One merchant description at every breakpoint');
                    await expect(page.locator('[data-product-description]')).toContainText('Parure avec collier');
                    const reviews = page.locator('#product-reviews');
                    await reviews.locator('summary').click();
                    check(await reviews.evaluate(el => el.open), 'Reviews expand with native control');
                    await reviews.locator('summary').press('Enter');
                    check(!await reviews.evaluate(el => el.open), 'Reviews collapse with keyboard');
                    const specs = page.locator('[data-product-specifications]');
                    if (await specs.count()) {
                        await specs.locator('summary').click();
                        check(!await specs.evaluate(el => el.open), 'Admin attributes panel can collapse');
                    }
                }
                if ([1440, 390].includes(width) && ['/test-lingerie', '/test-louzir-demo-008', '/checkout/cart', '/customer/login'].includes(path)) {
                    if (path === '/test-louzir-demo-008') await page.locator('.louzir-product-story').scrollIntoViewIfNeeded();
                    await page.screenshot({ path: 'storage/framework/testing/stitch-theme-' + width + '-' + path.split('/').pop() + '.png' });
                }
            }
            await context.close();
        }
        check(errors.length === 0, 'No JavaScript errors: ' + errors.join(', '));
        console.log('Storefront theme: ' + checks + ' browser checks passed.');
    } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
