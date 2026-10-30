const assert = require('node:assert/strict');
const { chromium } = require('@playwright/test');

const baseUrl = process.env.STOREFRONT_BASE_URL || 'http://127.0.0.1:8000';
const paths = ['/page/about-us', '/contact-us', '/page/customer-service', '/page/whats-new', '/page/terms-of-use', '/page/terms-conditions'];

(async () => {
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    let checks = 0;

    try {
        for (const locale of ['fr', 'en', 'ar']) {
            const page = await browser.newPage({ viewport: { width: locale === 'en' ? 1440 : 390, height: 900 } });
            try {
                await page.goto(`${baseUrl}/?locale=${locale}`);
                const links = await page.locator('.louzir-footer-links a').evaluateAll(elements => elements.map(element => ({ href: element.href, label: element.textContent.trim() })));

                for (const path of paths) {
                    const link = links.find(item => new URL(item.href).pathname === path);
                    assert.ok(link?.label, `Missing footer link ${locale}: ${path}`);
                    assert.equal(new URL(link.href).origin, new URL(baseUrl).origin, `Wrong destination ${locale}: ${link.href}`);
                    const response = await page.request.get(link.href);
                    assert.equal(response.status(), 200, `Broken page ${locale}: ${link.href}`);
                    assert.match(await response.text(), /<\/html>/i);
                    checks += 4;
                }

                // Follow a real anchor in each locale; no form is submitted.
                await page.locator('.louzir-footer-links a').filter({ hasText: links.find(item => new URL(item.href).pathname === paths[0]).label }).click();
                await page.waitForURL(url => url.pathname === paths[0]);
                assert.equal(new URL(page.url()).origin, new URL(baseUrl).origin);
                checks++;
            } finally {
                await page.close();
            }
        }
        console.log(`PASS: ${checks} footer checks (six pages, FR/EN/AR, mobile and desktop).`);
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error.message);
    process.exitCode = 1;
});
