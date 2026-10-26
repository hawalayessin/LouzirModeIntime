const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
const base = process.env.STOREFRONT_TEST_URL || 'http://127.0.0.1:8000';
(async () => {
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    let checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    const labels = {
        fr: { color: 'Couleur', size: 'Taille', brand: 'Marque', red: 'Rouge', apply: 'Appliquer les filtres', clear: 'Tout effacer' },
        en: { color: 'Color', size: 'Size', brand: 'Brand', red: 'Red', apply: 'Apply filters', clear: 'Clear All' },
        ar: { color: 'اللون', size: 'المقاس', brand: 'العلامة التجارية', red: 'أحمر', apply: 'تطبيق الفلاتر', clear: null },
    };
    try {
        for (const locale of (process.env.FILTER_TEST_LOCALES || 'fr,en,ar').split(',')) {
            for (const width of (process.env.FILTER_TEST_WIDTHS || '1440,390,320').split(',').map(Number)) {
                const context = await browser.newContext({ viewport: { width, height: 1000 } });
                context.setDefaultNavigationTimeout(60000);
                const page = await context.newPage();
                const errors = [];
                page.on('pageerror', error => errors.push(error.message));
                await page.goto(base + '/test-lingerie?locale=' + locale, { waitUntil: 'networkidle' });
                const cookie = page.locator('.cookie-reject');
                if (await cookie.isVisible()) await cookie.click();
                if (width < 768) await page.locator('[data-catalog-filter-trigger]').click();
                const panel = page.locator('.louzir-filter-panel').last();
                await expect(panel).toBeVisible();
                for (const code of ['color', 'size', 'brand']) {
                    await expect(panel.locator('[data-filter-code=' + code + '] summary')).toHaveText(labels[locale][code]);
                    check(true, 'Localized header ' + locale + code);
                }
                const color = panel.locator('[data-filter-code=color]');
                await expect(color.getByRole('checkbox', { name: labels[locale].red, exact: true })).toBeVisible();
                check(!/Affichage de|Showing \d|currentCount|totalCount/.test(await panel.innerText()), 'Option count is removed');
                check(await panel.locator('.louzir-filter-swatch').count() > 0, 'Color swatches display');
                const search = color.getByRole('textbox');
                const optionsResponse = page.waitForResponse(r => r.url().includes('/options') && r.url().includes('search='));
                await search.fill(labels[locale].red);
                await optionsResponse;
                await expect(color.getByRole('checkbox')).toHaveCount(1);
                check(true, 'Search finds translated colors');
                await color.getByRole('checkbox').check();
                const size = panel.locator('[data-filter-code=size]');
                await size.locator('label').filter({ hasText: /^M$/ }).click();
                check(!new URL(page.url()).searchParams.has('color'), 'Selections remain draft before Apply');
                const result = page.waitForResponse(r => /\/api\/products\?/.test(r.url()) && r.url().includes('color='));
                await panel.getByRole('button', { name: labels[locale].apply, exact: true }).click();
                check((await result).status() === 200, 'Apply requests real filtered products');
                await expect(page).toHaveURL(/color=/);
                check(new URL(page.url()).searchParams.has('size'), 'Size is applied with color');
                if (width < 768) {
                    await expect(panel).not.toBeVisible();
                    await page.locator('[data-catalog-filter-trigger]').click();
                }
                await expect(panel.locator('[data-filter-code=color] input[type=checkbox]').first()).toBeChecked();
                if (labels[locale].clear) {
                    await panel.getByRole('button', { name: labels[locale].clear, exact: true }).click();
                    await expect(page).not.toHaveURL(/color=/);
                    if (width < 768) await expect(panel).not.toBeVisible();
                    check(true, 'Clear restores results');
                }
                if (width < 768 && !await panel.isVisible()) await page.locator('[data-catalog-filter-trigger]').click();
                const summary = panel.locator('[data-filter-code=size] summary');
                await summary.focus();
                await summary.press('Enter');
                check(!await size.evaluate(el => el.open), 'Accordion works with keyboard');
                await summary.press('Enter');
                check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), 'No mobile/desktop overflow');
                if (locale === 'ar') check(await page.locator('html').getAttribute('dir') === 'rtl', 'Arabic layout uses RTL');
                if (locale === 'fr' && width !== 320) {
                    await search.fill('');
                    await expect(color.getByRole('checkbox')).toHaveCount(5);
                    await expect(panel.locator('[data-filter-code=price] input[type=range]').first()).toBeVisible();
                    await panel.locator('.louzir-filter-heading p').click();
                    if (width === 1440) await panel.screenshot({ path: 'storage/framework/testing/filter-sidebar-' + width + '.png' });
                    else await page.screenshot({ path: 'storage/framework/testing/filter-sidebar-' + width + '.png' });
                }
                check(errors.length === 0, errors.join('\n'));
                await context.close();
            }
        }
    } finally { await browser.close(); }
    console.log('Filter sidebar FR/EN/AR: ' + checks + ' browser checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
