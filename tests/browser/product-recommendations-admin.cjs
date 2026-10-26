const { chromium, expect } = require('@playwright/test');
const fs = require('node:fs');
const assert = require('node:assert/strict');
assert.equal(process.env.RECOMMENDATIONS_ALLOW_WRITES, '1', 'Use a disposable database and explicitly enable test writes.');
const base = process.env.RECOMMENDATIONS_ADMIN_URL || 'http://127.0.0.1:8002';
const fixture = JSON.parse(fs.readFileSync('storage/framework/testing/recommendation-fixtures.json'));
(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    const admin = await browser.newPage();
    const shop = await browser.newPage({ viewport: { width: 390, height: 1000 } });
    admin.setDefaultTimeout(60000);
    shop.setDefaultTimeout(60000);
    let original, checks = 0;
    const errors = [];
    for (const page of [admin, shop]) page.on('pageerror', e => errors.push(e.message));
    const check = (condition, message) => { assert.ok(condition, message); checks++; };
    const field = key => admin.locator('[name="catalog[products][product_view_page][' + key + ']"]');
    const saleable = () => admin.locator('input[type="checkbox"][name="catalog[products][product_view_page][related_only_saleable]"]');
    const config = base + '/admin/configuration/catalog/products?locale=fr';
    const save = async () => {
        const response = admin.waitForResponse(r => r.request().method() === 'POST' && r.url().includes('/configuration/catalog/products'));
        await admin.getByRole('button', { name: 'Enregistrer la configuration', exact: true }).click();
        check((await response).status() === 302, 'Native configuration form saves');
        await admin.waitForLoadState('networkidle');
        await admin.goto(config, { waitUntil: 'networkidle' });
        check((await admin.locator('.text-red-500,.text-red-600').allTextContents()).every(t => !t.trim()), 'No validation errors');
    };
    const related = async () => {
        const response = await shop.request.get(base + '/api/products/' + fixture.source + '/related?locale=fr');
        check(response.status() === 200, 'Related API succeeds after admin change');
        return (await response.json()).data.map(p => p.id);
    };
    try {
        await admin.goto(base + '/admin/login', { waitUntil: 'networkidle' });
        await admin.locator('[name=email]').fill(process.env.DELIVERY_TEST_ADMIN_EMAIL || 'admin@example.com');
        await admin.locator('[name=password]').fill(process.env.DELIVERY_TEST_ADMIN_PASSWORD || 'admin123');
        await admin.getByRole('button', { name: 'Se connecter', exact: true }).click();
        await admin.waitForURL('**/admin/dashboard');
        await admin.goto(base + '/admin/catalog/products/edit/' + fixture.source + '?locale=fr', { waitUntil: 'networkidle' });
        check((await admin.locator('input[name="related_products[]"]').evaluateAll(inputs => inputs.map(input => input.value))).includes(String(fixture.manual)), 'Existing admin product associations remain available');
        await admin.goto(config, { waitUntil: 'networkidle' });
        original = {};
        for (const key of ['no_of_related_products', 'no_of_up_sells_products', 'related_mode', 'related_cache_seconds']) original[key] = await field(key).inputValue();
        original.saleable = await saleable().isChecked();
        await field('no_of_related_products').fill('2');
        await field('no_of_up_sells_products').fill('0');
        await field('related_mode').selectOption('category');
        await field('related_cache_seconds').fill('60');
        await saleable().check({ force: true });
        await save();
        check(JSON.stringify(await related()) === JSON.stringify([fixture.near, fixture.far]), 'Category mode follows category and price ranking');
        await shop.goto(base + '/test-related-source?locale=fr', { waitUntil: 'networkidle' });
        const reject = shop.getByRole('button', { name: 'Rejeter', exact: true });
        if (await reject.isVisible()) await reject.click();
        const anchor = shop.locator('[data-product-recommendations]');
        await anchor.scrollIntoViewIfNeeded();
        await expect(anchor.locator('.louzir-product-card')).toHaveCount(2);
        check(true, 'Mobile product page follows the admin count');
        await field('related_mode').selectOption('manual');
        await save();
        check(JSON.stringify(await related()) === JSON.stringify([fixture.manual]), 'Manual-only mode respects product associations');
        await field('related_mode').selectOption('hybrid');
        await save();
        check(JSON.stringify(await related()) === JSON.stringify([fixture.manual, fixture.near]), 'Hybrid mode preserves manual priority and fills by category');
        await field('no_of_related_products').fill('0');
        await save();
        check((await related()).length === 0, 'Zero disables API recommendations');
        await shop.goto(base + '/test-related-source?locale=fr', { waitUntil: 'networkidle' });
        check(await shop.locator('[data-product-recommendations]').count() === 0, 'Zero counts remove recommendation mounting and requests');
        await field('related_mode').scrollIntoViewIfNeeded();
        await admin.screenshot({ path: 'storage/framework/testing/recommendations-admin.png' });
        check(errors.length === 0, 'No browser errors: ' + errors.join(', '));
    } finally {
        try {
            if (original) {
                await admin.goto(config, { waitUntil: 'networkidle' });
                for (const key of ['no_of_related_products', 'no_of_up_sells_products', 'related_cache_seconds']) await field(key).fill(original[key]);
                await field('related_mode').selectOption(original.related_mode);
                await saleable().setChecked(original.saleable, { force: true });
                await save();
            }
        } finally { await browser.close(); }
    }
    console.log('Recommendations admin: ' + checks + ' browser checks passed.');
})().catch(e => { console.error(e); process.exitCode = 1; });
