const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
const base = process.env.STOREFRONT_TEST_URL || 'http://127.0.0.1:8000';
(async () => {
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    let checks = 0;
    const errors = [];
    const check = (value, message) => { assert.ok(value, message); checks++; };
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    context.setDefaultNavigationTimeout(60000);
    context.setDefaultTimeout(30000);
    const page = await context.newPage();
    page.on('pageerror', error => errors.push(error.message));
    const dismissCookies = async () => {
        const reject = page.getByRole('button', { name: 'Rejeter', exact: true });
        if (await reject.isVisible()) await reject.click();
    };
    try {
        for (const path of ['/test-lingerie', '/soutiens-gorges', '/search?query=test']) {
            await page.setViewportSize({ width: 1440, height: 1000 });
            await page.goto(base + path, { waitUntil: 'networkidle' });
            await dismissCookies();
            const sidebar = page.locator('.louzir-filter-sidebar');
            await expect(sidebar.locator('.louzir-filter-panel')).toBeVisible();
            check(await sidebar.locator('input[type=range]').count() > 0, 'Admin price filter available: ' + path);
            for (const width of [390, 768, 320, 1440]) {
                await page.setViewportSize({ width, height: 1000 });
                if (width < 768) {
                    await expect(sidebar).toHaveCount(0);
                    await page.locator('[data-catalog-filter-trigger]').click();
                    await expect(page.locator('.louzir-filter-panel').last()).toBeVisible();
                } else {
                    await expect(sidebar).toBeVisible();
                    await expect(page.locator('[data-catalog-filter-trigger]')).toHaveCount(0);
                }
                check(true, 'Filters follow viewport without reload: ' + width + path);
                if (width < 768) {
                    // Returning to desktop must also release the drawer scroll lock.
                    await page.setViewportSize({ width: 1440, height: 1000 });
                    await expect(sidebar).toBeVisible();
                    check(await page.locator('body').evaluate(el => getComputedStyle(el).overflow !== 'hidden'), 'Drawer releases scroll lock');
                }
                check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), 'No viewport overflow');
            }
            const cards = page.locator('.louzir-catalog-results .louzir-product-card');
            if (await cards.count()) {
                const last = cards.last();
                await last.scrollIntoViewIfNeeded();
                const gap = await last.evaluate(el => document.querySelector('.louzir-footer').getBoundingClientRect().top - el.getBoundingClientRect().bottom);
                check(gap >= 64, 'Cards have breathing room before footer: ' + gap);
                await cards.first().hover();
                await page.waitForTimeout(450);
                check(await cards.first().evaluate(el => getComputedStyle(el).transform.startsWith('matrix3d')), 'Product cards lift in 3D');
            }
        }
        await page.goto(base + '/', { waitUntil: 'networkidle' });
        const homeCard = page.locator('.louzir-card').first();
        await homeCard.scrollIntoViewIfNeeded();
        await page.waitForTimeout(1000);
        await homeCard.hover();
        await page.waitForTimeout(450);
        check(await homeCard.evaluate(el => getComputedStyle(el).transform.startsWith('matrix3d')), 'Home editorial card shares 3D hover');
        check(await homeCard.evaluate(el => getComputedStyle(el).borderRadius === '16px'), 'Shared card radius');
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.waitForTimeout(100);
        check(await homeCard.evaluate(el => getComputedStyle(el).transform === 'none'), 'Reduced motion stops 3D');
        await page.emulateMedia({ reducedMotion: 'no-preference' });
        let fail = true;
        await page.route('**/api/categories/attributes**', route => fail ? route.fulfill({ status: 503, contentType: 'application/json', body: '{}' }) : route.continue());
        await page.goto(base + '/test-lingerie', { waitUntil: 'networkidle' });
        await expect(page.locator('.louzir-filter-sidebar [role=alert]')).toContainText('Les filtres ne sont pas disponibles');
        fail = false;
        await page.getByRole('button', { name: 'Réessayer', exact: true }).click();
        await expect(page.locator('.louzir-filter-sidebar input[type=range]').first()).toBeVisible();
        check(true, 'Filter API failure can be retried');
        await page.unroute('**/api/categories/attributes**');
        for (const width of [1440, 390]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.goto(base + '/test-lingerie', { waitUntil: 'networkidle' });
            await dismissCookies();
            await page.locator('.louzir-catalog-results').scrollIntoViewIfNeeded();
            await page.screenshot({ path: 'storage/framework/testing/pearl-catalog-' + width + '.png', fullPage: true });
            await page.evaluate(() => window.scrollTo(0, document.querySelector('.louzir-footer').offsetTop - 420));
            await page.screenshot({ path: 'storage/framework/testing/pearl-footer-' + width + '.png' });
            await page.goto(base + '/test-louzir-demo-001', { waitUntil: 'networkidle' });
            await page.locator('[data-product-recommendations]').scrollIntoViewIfNeeded();
            const related = page.locator('.louzir-products-carousel .louzir-product-card');
            await expect(related.last()).toBeVisible({ timeout: 30000 });
            const gap = await related.last().evaluate(el => document.querySelector('.louzir-footer').getBoundingClientRect().top - el.getBoundingClientRect().bottom);
            check(gap >= 48, 'Related product cards remain clear of footer: ' + width + ' gap=' + gap);
        }
        check(errors.length === 0, errors.join('\n'));
    } finally { await browser.close(); }
    console.log('Cards, footer spacing, responsive filters: ' + checks + ' browser checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
