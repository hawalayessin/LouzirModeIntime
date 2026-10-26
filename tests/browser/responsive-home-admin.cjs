const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
const base = process.env.HERO_TEST_URL || 'http://127.0.0.1:8000';
const output = 'storage/framework/testing';
(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    let checks = 0;
    const errors = [];
    const check = (condition, message) => { assert.ok(condition, message); checks++; };
    try {
        for (const width of [320, 360, 390, 430, 640, 768, 1024, 1440]) {
            const context = await browser.newContext({ viewport: { width, height: 900 }, isMobile: width <= 640, hasTouch: width <= 640, deviceScaleFactor: 2 });
            context.setDefaultNavigationTimeout(60000);
            const page = await context.newPage();
            page.on('pageerror', e => errors.push(e.message));
            check((await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' })).status() === 200, 'Home opens at ' + width);
            await page.evaluate(() => document.fonts.ready);
            const reject = page.getByRole('button', { name: 'Rejeter', exact: true });
            if (await reject.isVisible()) await reject.click();
            const toggle = page.locator('[data-carousel-toggle]');
            if (await toggle.getAttribute('aria-pressed') === 'false') await toggle.click();
            let firstHeight;
            for (let i = 0; i < 3; i++) {
                await page.locator('.louzir-carousel-dot').nth(i).click();
                await page.waitForTimeout(400);
                const layout = await page.locator('.louzir-carousel').evaluate(el => {
                    const slide = el.querySelector('.is-active');
                    const image = slide.querySelector('img');
                    const rect = node => node.getBoundingClientRect().toJSON();
                    return { hero: rect(el), image: rect(image), copy: rect(slide.querySelector('.louzir-hero-copy')), arrows: [...el.querySelectorAll('[data-carousel-prev], [data-carousel-next]')].map(rect), buttons: [...slide.querySelectorAll('.louzir-actions a')].map(rect), focalPoint: getComputedStyle(image).objectPosition, configured: getComputedStyle(slide).getPropertyValue('--hero-mobile-x').trim(), visibleSlides: [...el.querySelectorAll('.louzir-slide')].filter(s => getComputedStyle(s).visibility === 'visible').length };
                });
                firstHeight ??= layout.hero.height;
                check(Math.abs(layout.hero.height - firstHeight) < 1, 'Slide height stable: ' + width + '/' + i);
                check(layout.visibleSlides === 1, 'One slide visible, no ghost text: ' + width + '/' + i);
                check(layout.arrows.every(r => Math.abs(r.y + r.height / 2 - (layout.image.y + layout.image.height / 2)) < 2), 'Arrows centered on photo: ' + width + '/' + i);
                check(layout.buttons.every(r => r.x >= 0 && r.right <= width + 1 && r.bottom < layout.hero.bottom - 45), 'Actions fit and remain above pagination: ' + width + '/' + i);
                if (width <= 640) {
                    check(Math.abs(layout.image.height - width * 5 / 8) < 1, 'Photo keeps its landscape framing: ' + width + '/' + i);
                    check(layout.copy.y >= layout.image.bottom, 'Mobile text sits below photo: ' + width + '/' + i);
                    check(layout.hero.height < layout.image.height + 500, 'No oversized mobile minimum height: ' + width + '/' + i);
                    check(layout.focalPoint.split(' ')[0] === layout.configured, 'Admin mobile focal point preserved: ' + width + '/' + i);
                }
            }
            check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No horizontal overflow at ' + width);
            if ([390, 768, 1440].includes(width)) {
                await page.evaluate(() => scrollTo(0, 0));
                await page.screenshot({ path: output + '/responsive-home-' + width + '.png' });
            }
            await context.close();
        }

        for (const reducedMotion of ['no-preference', 'reduce']) {
            const context = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion });
            await context.addInitScript(() => {
                window.__entrances = [];
                const animate = Element.prototype.animate;
                Element.prototype.animate = function (frames, options) {
                    if (this.closest('.louzir-home')) window.__entrances.push({ frames, options, hero: Boolean(this.closest('.louzir-hero-copy')), top: this.getBoundingClientRect().top });
                    return animate.call(this, frames, options);
                };
            });
            const page = await context.newPage();
            page.on('pageerror', e => errors.push(e.message));
            await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
            const reject = page.getByRole('button', { name: 'Rejeter', exact: true });
            if (await reject.isVisible()) await reject.click();
            const pause = page.locator('[data-carousel-toggle]');
            if (await pause.getAttribute('aria-pressed') === 'false') await pause.click();
            const initial = await page.evaluate(() => window.__entrances.filter(a => !a.hero).length);
            check(initial === 0, 'Already painted content does not flash on load: ' + reducedMotion);
            const height = await page.evaluate(() => document.documentElement.scrollHeight);
            for (let y = 0; y < height; y += 500) {
                await page.evaluate(y => scrollTo(0, y), y);
                await page.waitForTimeout(150);
            }
            await page.waitForTimeout(500);
            const animations = await page.evaluate(() => window.__entrances.filter(a => !a.hero));
            if (reducedMotion === 'reduce') check(animations.length === 0, 'Reduced motion disables entrance effects');
            else {
                check(animations.length > 0, 'Below-fold sections retain a subtle entrance');
                check(animations.every(a => a.frames.every(f => !f.transform && !f.filter) && a.options.duration <= 350), 'Entrances do not move or blur content');
            }
            check(await page.locator('.louzir-home').evaluate(el => el.getAnimations({ subtree: true }).filter(a => a.constructor.name !== 'CSSAnimation').length === 0), 'Entrance effects finish without running indefinitely');
            await context.close();
        }

        const adminContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const page = await adminContext.newPage();
        page.on('pageerror', e => errors.push(e.message));
        await page.goto(base + '/admin/login', { waitUntil: 'networkidle' });
        await page.locator('[name=email]').fill(process.env.DELIVERY_TEST_ADMIN_EMAIL || 'admin@example.com');
        await page.locator('[name=password]').fill(process.env.DELIVERY_TEST_ADMIN_PASSWORD || 'admin123');
        await page.getByRole('button', { name: 'Se connecter', exact: true }).click();
        await page.waitForURL('**/admin/dashboard');
        await page.waitForLoadState('networkidle');
        for (const width of [320, 390, 524, 525, 768, 1440]) {
            await page.setViewportSize({ width, height: 900 });
            const logo = await page.locator('#logo-image').boundingBox();
            const header = await page.locator('header').boundingBox();
            check(logo.width <= 104 && logo.height <= 32, 'Admin logo constrained at ' + width);
            check(header.height <= 70, 'Admin header compact at ' + width);
            check(logo.x + logo.width <= width, 'Admin logo stays inside screen at ' + width);
            if (width === 390) {
                await page.screenshot({ path: output + '/responsive-admin-mobile.png' });
                await page.locator('header .icon-menu').click();
                await expect(page.locator('#logo-image-drawer')).toBeVisible();
                const drawerLogo = await page.locator('#logo-image-drawer').boundingBox();
                check(drawerLogo.width <= 104 && drawerLogo.height <= 32, 'Mobile drawer logo constrained');
                await page.screenshot({ path: output + '/responsive-admin-drawer.png' });
                await page.locator('.fixed.z-\\[10002\\] .icon-cross').click();
            }
        }
        await page.setViewportSize({ width: 390, height: 844 });
        const lightSource = await page.locator('#logo-image').getAttribute('src');
        await page.locator('header .icon-dark').click();
        await expect(page.locator('html')).toHaveClass(/dark/);
        check(await page.locator('#logo-image').evaluate(el => el.complete && el.naturalWidth > 0), 'Admin logo loads in dark theme');
        const darkLogo = await page.locator('#logo-image').boundingBox();
        check(darkLogo.width <= 104 && darkLogo.height <= 32, 'Dark mode logo stays constrained');
        await page.locator('header .icon-light').click();
        check(await page.locator('#logo-image').getAttribute('src') === lightSource, 'Light theme logo restored');
        await adminContext.close();
        check(errors.length === 0, 'No browser errors: ' + errors.join(', '));
        console.log('Responsive home and admin: ' + checks + ' browser checks passed.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
