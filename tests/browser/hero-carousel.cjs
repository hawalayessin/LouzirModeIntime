const { chromium } = require('@playwright/test');
const assert = require('node:assert/strict');
const base = process.env.HERO_TEST_URL || 'http://127.0.0.1:8000';
const output = 'storage/framework/testing';

(async () => {
    const browser = await chromium.launch({ channel: process.env.PLAYWRIGHT_CHANNEL || 'msedge', headless: true });
    const errors = [];
    let checks = 0;
    const check = (value, message) => { assert.ok(value, message); checks++; };
    const open = async options => {
        const context = await browser.newContext(options);
        const page = await context.newPage();
        page.on('pageerror', error => errors.push(error.message));
        const response = await page.goto(base + '/?locale=fr', { waitUntil: 'networkidle' });
        check(response.status() === 200, 'Homepage returns 200');
        await page.waitForSelector('.louzir-carousel[data-initialized="true"]');
        const reject = page.getByRole('button', { name: 'Rejeter', exact: true });
        if (await reject.isVisible()) await reject.click();
        return { context, page };
    };
    const active = page => page.locator('.louzir-slide.is-active').getAttribute('aria-label');
    try {
        const { page, context } = await open({ viewport: { width: 1440, height: 1050 } });
        const hero = page.locator('.louzir-carousel');
        await hero.scrollIntoViewIfNeeded();
        await hero.focus();
        await page.keyboard.press('Home');
        await hero.hover();
        check(await page.locator('.louzir-slide').count() === 3, 'Three slides');
        const layout = await hero.evaluate(el => {
            const hero = el.querySelector('.is-active img').getBoundingClientRect();
            return [...el.querySelectorAll('[data-carousel-prev], [data-carousel-next]')].map(button => {
                const rect = button.getBoundingClientRect();
                return { center: rect.y + rect.height / 2, heroCenter: hero.y + hero.height / 2 };
            });
        });
        check(layout.every(button => Math.abs(button.center - button.heroCenter) < 2), 'Arrows vertically centered on the image');
        check(await active(page) === '1 / 3', 'First slide initially active');
        check(await page.locator('.louzir-slide:not(.is-active)[inert]').count() === 2, 'Hidden slides inert');
        await page.locator('[data-carousel-next]').click();
        check(await active(page) === '2 / 3', 'Next control');
        await page.locator('[data-carousel-prev]').click();
        check(await active(page) === '1 / 3', 'Previous control');
        await page.locator('[data-carousel-prev]').click();
        check(await active(page) === '3 / 3', 'Backward wrap');
        await page.locator('[data-carousel-next]').click();
        check(await active(page) === '1 / 3', 'Forward wrap');
        await page.locator('.louzir-carousel-dot').nth(1).click();
        check(await active(page) === '2 / 3', 'Pagination');
        await hero.focus();
        await page.keyboard.press('End');
        check(await active(page) === '3 / 3', 'End key');
        await page.keyboard.press('Home');
        await page.keyboard.press('ArrowRight');
        check(await active(page) === '2 / 3', 'Keyboard navigation');
        await page.waitForTimeout(900);
        await page.screenshot({ path: output + '/hero-desktop.png' });
        for (const i of [0, 1, 2]) {
            await page.locator('.louzir-carousel-dot').nth(i).click();
            check(await page.locator('.louzir-slide').nth(i).locator('img').evaluate(img => img.complete && img.naturalWidth > 1500), 'High resolution image loads');
        }
        await context.close();

        const auto = await open({ viewport: { width: 1440, height: 1050 } });
        await auto.page.locator('.louzir-carousel-dot').first().click();
        await auto.page.mouse.move(1430, 1040);
        await auto.page.waitForTimeout(5300);
        check(await active(auto.page) === '2 / 3', 'Autoplay after five seconds');
        await auto.page.locator('.louzir-carousel').hover();
        const held = await active(auto.page);
        await auto.page.waitForTimeout(5300);
        check(await active(auto.page) !== held, 'Autoplay continues with the pointer over the hero');
        await auto.page.locator('[data-carousel-next]').click();
        const afterArrow = await active(auto.page);
        await auto.page.waitForTimeout(5300);
        check(await active(auto.page) !== afterArrow, 'Autoplay resumes after clicking an arrow');
        await auto.page.locator('[data-carousel-toggle]').click();
        const pausedSlide = await active(auto.page);
        await auto.page.locator('[data-carousel-toggle]').blur();
        await auto.page.mouse.move(1430, 1040);
        await auto.page.waitForTimeout(5300);
        check(await active(auto.page) === pausedSlide, 'Explicit pause persists');
        await auto.page.locator('[data-carousel-toggle]').click();
        await auto.page.waitForTimeout(5300);
        check(await active(auto.page) !== pausedSlide, 'Explicit resume works with the pointer and focus on the control');
        await auto.page.locator('.louzir-carousel').focus();
        await auto.page.keyboard.press('Home');
        const keyboardSlide = await active(auto.page);
        await auto.page.waitForTimeout(5300);
        check(await active(auto.page) === keyboardSlide, 'Keyboard focus pauses automatic rotation');
        await auto.context.close();

        const mobile = await open({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
        const mobileHero = mobile.page.locator('.louzir-carousel');
        await mobileHero.scrollIntoViewIfNeeded();
        await mobileHero.focus();
        await mobile.page.keyboard.press('Home');
        await mobileHero.dispatchEvent('touchstart', { touches: [{ identifier: 0, clientX: 320, clientY: 300 }] });
        await mobileHero.dispatchEvent('touchend', { changedTouches: [{ identifier: 0, clientX: 70, clientY: 305 }] });
        check(await active(mobile.page) === '2 / 3', 'Horizontal swipe');
        await mobileHero.dispatchEvent('touchstart', { touches: [{ identifier: 0, clientX: 200, clientY: 200 }] });
        await mobileHero.dispatchEvent('touchend', { changedTouches: [{ identifier: 0, clientX: 205, clientY: 450 }] });
        check(await active(mobile.page) === '2 / 3', 'Vertical scroll does not change slide');
        check(await mobile.page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No mobile horizontal overflow');
        await mobile.page.waitForTimeout(900);
        await mobile.page.screenshot({ path: output + '/hero-mobile.png' });
        await mobile.page.setViewportSize({ width: 320, height: 800 });
        check(await mobile.page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No overflow at 320px');
        const mobileLayout = await mobileHero.evaluate(el => {
            const hero = el.querySelector('.is-active img').getBoundingClientRect();
            return [...el.querySelectorAll('[data-carousel-prev], [data-carousel-next]')].every(button => {
                const rect = button.getBoundingClientRect();
                return Math.abs(rect.y + rect.height / 2 - (hero.y + hero.height / 2)) < 2;
            });
        });
        check(mobileLayout, 'Arrows vertically centered on the mobile image at 320px');
        await mobile.page.keyboard.press('End');
        const socks = mobile.page.locator('.louzir-slide.is-active img');
        check((await socks.getAttribute('src')).includes('cotton-socks.webp'), 'Socks image published in the third slide');
        await mobile.page.waitForTimeout(900);
        await mobile.page.screenshot({ path: output + '/hero-socks-mobile.png' });
        await mobile.context.close();

        const reduced = await open({ viewport: { width: 1440, height: 1050 }, reducedMotion: 'reduce' });
        await reduced.page.mouse.move(1430, 1040);
        await reduced.page.waitForTimeout(5300);
        check(await active(reduced.page) === '1 / 3', 'Reduced motion disables autoplay');
        await reduced.page.locator('[data-carousel-next]').click();
        check(await active(reduced.page) === '2 / 3', 'Manual navigation with reduced motion');
        await reduced.context.close();

        const noJs = await browser.newContext({ javaScriptEnabled: false });
        const fallback = await noJs.newPage();
        await fallback.goto(base + '/?locale=fr');
        check(await fallback.locator('.louzir-slide.is-active h1').isVisible(), 'Hero visible without JavaScript');
        check(!await fallback.locator('.louzir-carousel-controls').isVisible(), 'No unusable controls without JavaScript');
        await noJs.close();
        check(errors.length === 0, 'No JavaScript errors: ' + errors.join(', '));
        console.log('Hero carousel: ' + checks + ' browser checks passed.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
