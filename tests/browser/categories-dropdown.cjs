const { chromium } = require('@playwright/test');
const assert = require('node:assert/strict');
const base = process.env.CATALOG_TEST_URL || 'http://127.0.0.1:8000';
const expected = ['Soutiens-gorge', 'Bas', 'Hauts', 'Mariage', 'Maternité', 'Homme', 'Accessoires', 'Pyjamas', 'Robes de chambre'];

(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    let checks = 0;
    const errors = [];
    const check = (value, message) => { assert.ok(value, message); checks++; };
    try {
        for (const width of [1440, 390, 320]) {
            const page = await browser.newPage({ viewport: { width, height: 1000 } });
            page.on('pageerror', error => errors.push(error.message));
            check((await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' })).status() === 200, 'Homepage available');
            const cookie = page.getByRole('button', { name: 'Rejeter', exact: true });
            if (await cookie.isVisible()) await cookie.click();
            if (width > 1000) await page.locator('.louzir-all-categories').click();
            else await page.locator('.louzir-mobile-brand button.icon-hamburger').click();
            const menu = page.locator('[data-category-menu="' + (width > 1000 ? 'desktop' : 'mobile') + '"]');
            await menu.waitFor({ state: 'visible' });
            const names = await menu.locator(':scope > li > .louzir-category-row > a').allTextContents();
            check(expected.every(name => names.includes(name)), 'Nine reference categories available at ' + width);
            const toggle = menu.getByRole('button', { name: 'Soutiens-gorge', exact: true });
            const child = menu.getByRole('link', { name: 'Classiques', exact: true });
            check(await toggle.getAttribute('aria-expanded') === 'false' && !await child.isVisible(), 'Children collapsed initially');
            await toggle.click();
            check(await toggle.getAttribute('aria-expanded') === 'true' && await child.isVisible(), 'Children expand on click');
            const childUrl = await child.getAttribute('href');
            check(childUrl.includes('soutiens-gorges-classiques'), 'Subcategory uses its catalog URL');
            check((await page.request.get(childUrl)).status() === 200, 'Subcategory page opens');
            await toggle.focus();
            await page.keyboard.press('Enter');
            check(!await child.isVisible(), 'Enter collapses the branch');
            await page.keyboard.press('Space');
            check(await child.isVisible(), 'Space expands the branch');
            const browserPanel = menu.locator('xpath=..');
            const search = browserPanel.getByRole('searchbox', { name: 'Rechercher une catégorie', exact: true });
            check(await search.isVisible(), 'Category search visible');
            await search.click();
            const focusStyle = await search.evaluate(el => ({
                outline: getComputedStyle(el).outlineStyle,
                shadow: getComputedStyle(el).boxShadow,
                parentOutline: getComputedStyle(el.closest('.louzir-category-search-field')).outlineStyle,
            }));
            check(focusStyle.outline === 'none' && focusStyle.shadow === 'none' && focusStyle.parentOutline === 'solid', 'Search uses one rounded focus outline at ' + width);
            const placement = await menu.evaluate(el => {
                const panel = el.closest('.fixed').getBoundingClientRect();
                return { top: panel.top, navbarBottom: document.querySelector('.louzir-header').getBoundingClientRect().bottom };
            });
            check(Math.abs(placement.top - placement.navbarBottom) < 2, 'Sidebar starts below the navbar at ' + width);
            const font = await menu.locator(':scope > li > .louzir-category-row > a').first().evaluate(el => parseFloat(getComputedStyle(el).fontSize));
            check(font === 14, 'Compact category text');
            await search.fill('classiques');
            check(await menu.locator(':scope > li').count() === 1 && await child.isVisible(), 'Subcategory search preserves and opens its parent');
            check(!await menu.getByRole('link', { name: 'Push-up', exact: true }).count(), 'Unmatched children are filtered out');
            await search.fill('MATERNITE');
            check(await menu.locator(':scope > li').count() === 1 && await menu.getByRole('link', { name: 'Maternité', exact: true }).isVisible(), 'Search ignores case and accents');
            await search.fill('chaussettes');
            check(await menu.locator(':scope > li').count() === 2, 'Search finds children in multiple branches');
            await search.fill('zzzz-no-category');
            check(await browserPanel.getByRole('status').isVisible() && await menu.locator(':scope > li').count() === 0, 'Empty search state');
            await browserPanel.getByRole('button', { name: 'Effacer la recherche', exact: true }).click();
            check(await menu.locator(':scope > li').count() === names.length, 'Clear restores all categories');
            await search.fill('classiques');
            await search.press('Escape');
            check(await search.inputValue() === '' && await menu.isVisible(), 'Escape clears the search without closing the panel');
            await search.fill('   ');
            check(await menu.locator(':scope > li').count() === names.length && !await child.isVisible(), 'Whitespace behaves as an empty search');
            await search.fill('');
            await toggle.click();
            await page.waitForTimeout(300);
            await page.screenshot({ path: 'storage/framework/testing/category-dropdown-' + width + '.png' });
            check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No horizontal overflow at ' + width);
            const ids = await page.locator('[id^="category-children-"]').evaluateAll(elements => elements.map(el => el.id));
            check(new Set(ids).size === ids.length, 'Unique subcategory control IDs');
            await page.locator('.louzir-category-drawer .icon-cancel').first().click();
            await menu.waitFor({ state: 'hidden' });
            await page.evaluate(() => scrollTo(0, 400));
            await page.waitForTimeout(100);
            if (width > 1000) await page.locator('.louzir-all-categories').click();
            else await page.locator('.louzir-mobile-brand button.icon-hamburger').click();
            await menu.waitFor({ state: 'visible' });
            const scrolled = await menu.evaluate(el => ({ top: el.closest('.fixed').getBoundingClientRect().top, navbar: document.querySelector('.louzir-header').getBoundingClientRect().bottom }));
            check(Math.abs(scrolled.top - scrolled.navbar) < 2, 'Sidebar follows the sticky navbar after scrolling');
            await page.close();
        }

        const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/api/categories/tree*', route => route.fulfill({ json: { data: [{
            id: 90001, name: 'Parent fixture', url: '/', children: [{
                id: 90002, name: 'Child fixture', url: '/', children: [{ id: 90003, name: 'Grandchild fixture', url: '/search', children: [] }],
            }],
        }] } }));
        await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
        const cookie = page.getByRole('button', { name: 'Rejeter', exact: true });
        if (await cookie.isVisible()) await cookie.click();
        await page.locator('.louzir-all-categories').click();
        const menu = page.locator('[data-category-menu="desktop"]');
        await menu.getByRole('button', { name: 'Parent fixture', exact: true }).click();
        check(!await menu.getByRole('link', { name: 'Grandchild fixture' }).isVisible(), 'Grandchildren stay collapsed');
        await menu.getByRole('button', { name: 'Child fixture', exact: true }).click();
        check(await menu.getByRole('link', { name: 'Grandchild fixture' }).isVisible(), 'Nested branch expands');
        check(errors.length === 0, 'No browser errors: ' + errors.join(', '));
        console.log('Category dropdown: ' + checks + ' browser checks passed.');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
