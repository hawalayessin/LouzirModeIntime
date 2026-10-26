const { chromium, expect } = require('@playwright/test');
const assert = require('node:assert/strict');
const base = process.env.HERO_TEST_URL || 'http://127.0.0.1:8000';
(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    let checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    const errors = [];
    try {
        for (const mode of ['desktop', 'mobile', 'reduced', 'disabled']) {
            const mobile = mode === 'mobile';
            const context = await browser.newContext({ viewport: { width: mobile ? 390 : 1440, height: 1000 }, isMobile: mobile, hasTouch: mobile, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
            await context.addInitScript(() => {
                window.__entrances = [];
                const animate = Element.prototype.animate;
                Element.prototype.animate = function (frames, timing) {
                    if (this.closest('.louzir-home')) window.__entrances.push({ frames, timing, target: this.className });
                    return animate.call(this, frames, timing);
                };
            });
            if (mode === 'disabled') await context.route(base + '/?locale=fr', async route => {
                const response = await route.fetch();
                await route.fulfill({ response, body: (await response.text()).replace('data-motion-enabled="1"', 'data-motion-enabled="0"') });
            });
            const page = await context.newPage();
            page.on('pageerror', e => errors.push(e.message));
            await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
            const reject = page.getByRole('button', { name: 'Rejeter', exact: true });
            if (await reject.isVisible()) await reject.click();
            const pause = page.locator('[data-carousel-toggle]');
            if (await pause.getAttribute('aria-pressed') === 'false') await pause.click();
            await page.evaluate(() => { window.__entrances = []; });
            await page.locator('[data-carousel-next]').click();
            const heroAnimations = await page.evaluate(() => window.__entrances);
            if (['reduced', 'disabled'].includes(mode)) {
                check(heroAnimations.length === 0, 'Hero motion respects ' + mode);
                check(await page.locator('.is-active .louzir-hero-photo').evaluate(el => getComputedStyle(el).animationName === 'none'), 'Photo animation disabled: ' + mode);
            } else {
                check(heroAnimations.length >= 5, 'Slide copy appears in sequence: ' + mode);
                check(heroAnimations.every((a, i) => !i || a.timing.delay >= heroAnimations[i - 1].timing.delay), 'Hero delays progress in reading order: ' + mode);
                check(heroAnimations.every(a => a.frames.every(f => !f.filter)), 'No blur or reflection effect: ' + mode);
                if (mobile) check(heroAnimations.every(a => a.frames.every(f => !f.transform)), 'Mobile copy remains stationary');
            }
            await page.waitForTimeout(1100);
            check(await page.locator('.louzir-slide').evaluateAll(slides => slides.filter(s => getComputedStyle(s).visibility === 'visible').length === 1), 'No overlapping slides: ' + mode);
            if (mode === 'desktop') {
                const button = page.locator('.is-active .louzir-button.primary');
                await button.hover();
                await page.waitForTimeout(300);
                check(await button.evaluate(el => getComputedStyle(el).boxShadow !== 'none'), 'Button gains a soft shadow on hover');
                await button.evaluate(el => el.addEventListener('click', e => e.preventDefault(), { once: true }));
                const before = await button.boundingBox();
                await page.mouse.down();
                await page.waitForTimeout(200);
                const pressed = await button.boundingBox();
                check(pressed.width < before.width && pressed.width > before.width * .94, 'Button press feedback stays subtle');
                await page.mouse.up();
            }
            await page.evaluate(() => { window.__entrances = []; });
            const height = await page.evaluate(() => document.documentElement.scrollHeight);
            for (let y = 0; y < height; y += 550) {
                await page.evaluate(y => scrollTo(0, y), y);
                await page.waitForTimeout(130);
            }
            await page.waitForTimeout(1100);
            const entrances = await page.evaluate(() => window.__entrances);
            if (['reduced', 'disabled'].includes(mode)) check(entrances.length === 0, 'No scroll entrances: ' + mode);
            else {
                check(entrances.length > 0, 'Sections enter on scroll: ' + mode);
                if (mobile) check(entrances.every(a => a.frames.every(f => !f.transform) && a.timing.duration <= 350), 'Mobile scroll effects stay light');
                else check(entrances.every(a => a.frames[0].transform === 'translateY(8px)' && a.timing.duration === 700), 'Desktop uses a bounded eight-pixel rise');
                const count = entrances.length;
                await page.evaluate(() => scrollTo(0, 0));
                await page.waitForTimeout(100);
                await page.evaluate(() => scrollTo(0, document.documentElement.scrollHeight));
                await page.waitForTimeout(300);
                check(await page.evaluate(() => window.__entrances.length) === count, 'Entrances do not repeat on return scroll: ' + mode);
            }
            check(await page.locator('.louzir-home').evaluate(el => el.getAnimations({ subtree: true }).filter(a => a.constructor.name !== 'CSSAnimation').length === 0), 'Finite animations settle: ' + mode);
            if (mode === 'desktop' || mobile) {
                const card = page.locator('.louzir-card').first();
                await card.scrollIntoViewIfNeeded();
                await page.waitForTimeout(1100);
                if (mode === 'desktop') {
                    await card.hover();
                    await page.waitForTimeout(800);
                    check(await card.evaluate(el => getComputedStyle(el).boxShadow !== 'none'), 'Collection card gets layered elevation');
                }
                await page.screenshot({ path: 'storage/framework/testing/landing-motion-' + mode + '.png' });
            }
            await context.close();
        }
        check(errors.length === 0, 'No JavaScript errors: ' + errors.join(', '));
        console.log('Landing motion: ' + checks + ' browser checks passed.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
