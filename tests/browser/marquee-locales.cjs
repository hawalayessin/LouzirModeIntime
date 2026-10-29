const assert = require('node:assert/strict');
const { chromium } = require('@playwright/test');

// Read-only checks; also usable against the deployed storefront.
const baseUrl = process.env.MARQUEE_BASE_URL || 'http://127.0.0.1:8000';

(async () => {
    const browser = await chromium.launch({ channel: 'msedge', headless: true });
    let checks = 0;

    try {
        for (const locale of ['fr', 'en', 'ar']) {
            for (const width of [320, 390, 1366, 3840]) {
                const page = await browser.newPage({ viewport: { width, height: 900 } });
                try {
                    await page.goto(`${baseUrl}/?locale=${locale}`);
                    const track = page.locator('.louzir-marquee-track');
                    await track.waitFor();
                    await page.evaluate(() => document.fonts.ready);

                    for (const phase of [0, 0.25, 0.5, 0.75, 0.99]) {
                        const visible = await track.evaluate((element, progress) => {
                            const animation = element.getAnimations()[0];
                            animation.pause();
                            animation.currentTime = animation.effect.getTiming().duration * progress;
                            const viewport = element.parentElement.getBoundingClientRect();

                            return [...element.querySelectorAll('.louzir-marquee-group > span')].some(message => {
                                const box = message.getBoundingClientRect();
                                return message.textContent.trim() && box.right > viewport.left + 8
                                    && box.left < viewport.right - 8;
                            });
                        }, phase);
                        assert.ok(visible, `Blank marquee: ${locale}, ${width}px, phase ${phase}`);
                        checks++;
                    }

                    // Restart the CSS animation after seeking so pause is tested on a running track.
                    await track.evaluate(element => {
                        element.style.animation = 'none';
                        void element.offsetWidth;
                        element.style.animation = '';
                    });
                    const toggle = page.locator('.louzir-marquee-toggle');
                    await toggle.click();
                    await page.waitForFunction(() => document.querySelector('.louzir-marquee-toggle').getAttribute('aria-pressed') === 'true');
                    assert.equal(await track.evaluate(element => element.getAnimations()[0].playState), 'paused');
                    checks++;
                    await page.emulateMedia({ reducedMotion: 'reduce' });
                    assert.ok(await track.evaluate(element => {
                        const viewport = element.parentElement.getBoundingClientRect();
                        const message = element.querySelector('.louzir-marquee-group > span').getBoundingClientRect();
                        return message.right > viewport.left && message.left < viewport.right;
                    }), `Reduced-motion text hidden: ${locale}, ${width}px`);
                    checks++;
                } finally {
                    await page.close();
                }
            }
        }
        console.log(`PASS: ${checks} marquee checks (FR/EN/AR, mobile to TV, pause and reduced motion).`);
    } finally {
        await browser.close();
    }
})().catch(error => {
    console.error(error.message);
    process.exitCode = 1;
});
