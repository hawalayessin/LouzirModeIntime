<script>
(() => {
    const initialize = () => {
        const carousel = document.querySelector('.louzir-carousel');
        if (!carousel || carousel.dataset.initialized) return;
        carousel.dataset.initialized = 'true';
        const slides = [...carousel.querySelectorAll('.louzir-slide')];
        if (slides.length < 2) return;
        const controls = carousel.querySelector('.louzir-carousel-controls');
        controls.hidden = false;
        const dots = [...carousel.querySelectorAll('.louzir-carousel-dot')];
        const counter = carousel.querySelector('.louzir-carousel-counter');
        const toggle = carousel.querySelector('[data-carousel-toggle]');
        const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
        const interval = Math.max(3, Math.min(30, Number(carousel.dataset.interval) || 5)) * 1000;
        let index = 0, timer = null, focused = false, touching = false;
        let paused = carousel.dataset.autoplay !== '1' || preference.matches;
        let touch = null;
        const stop = () => { clearTimeout(timer); timer = null; };
        const syncToggle = () => {
            toggle.setAttribute('aria-pressed', String(paused));
            toggle.setAttribute('aria-label', paused ? toggle.dataset.resumeLabel : toggle.dataset.pauseLabel);
            toggle.querySelector('span').textContent = paused ? '▶' : 'Ⅱ';
        };
        const schedule = () => {
            stop();
            if (!paused && !focused && !touching && !document.hidden) {
                timer = setTimeout(() => { show(index + 1); }, interval);
            }
        };
        const show = next => {
            index = (next + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                const active = i === index;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', String(!active));
                slide.inert = !active;
                if (active) slide.querySelector('img').loading = 'eager';
                dots[i].setAttribute('aria-current', String(active));
            });
            counter.textContent = String(index + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0');
            carousel.dispatchEvent(new CustomEvent('louzir-slide-change', { bubbles: true, detail: { index } }));
            schedule();
        };
        carousel.querySelector('[data-carousel-prev]').addEventListener('click', () => show(index - 1));
        carousel.querySelector('[data-carousel-next]').addEventListener('click', () => show(index + 1));
        dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));
        toggle.addEventListener('click', () => {
            paused = !paused;
            if (!paused) focused = false;
            syncToggle(); schedule();
        });
        // Pointer navigation must not leave autoplay suspended after a click.
        // Keyboard focus still pauses rotation so links remain stable while tabbing.
        carousel.addEventListener('pointerdown', () => { focused = false; schedule(); });
        carousel.addEventListener('focusin', event => {
            focused = event.target.matches(':focus-visible');
            schedule();
        });
        carousel.addEventListener('focusout', event => {
            if (!carousel.contains(event.relatedTarget)) { focused = false; schedule(); }
        });
        carousel.addEventListener('keydown', event => {
            focused = true;
            stop();
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key) || /INPUT|TEXTAREA|SELECT/.test(event.target.tagName)) return;
            event.preventDefault();
            // Focus stays on a persistent control when the current slide becomes inert.
            if (slides.some(slide => slide.contains(document.activeElement))) carousel.focus();
            const rtl = getComputedStyle(carousel).direction === 'rtl';
            show(event.key === 'Home' ? 0 : event.key === 'End' ? slides.length - 1 : index + ((event.key === 'ArrowRight') !== rtl ? 1 : -1));
        });
        carousel.addEventListener('touchstart', event => {
            touch = event.touches.length === 1 ? { x: event.touches[0].clientX, y: event.touches[0].clientY } : null;
            touching = true; stop();
        }, { passive: true });
        carousel.addEventListener('touchend', event => {
            touching = false;
            if (touch) {
                const dx = event.changedTouches[0].clientX - touch.x;
                const dy = event.changedTouches[0].clientY - touch.y;
                if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) {
                    show(index + (dx < 0 ? 1 : -1));
                }
            }
            touch = null; schedule();
        }, { passive: true });
        carousel.addEventListener('touchcancel', () => { touch = null; touching = false; schedule(); });
        document.addEventListener('visibilitychange', schedule);
        preference.addEventListener('change', () => {
            if (preference.matches) paused = true;
            syncToggle(); schedule();
        });
        window.addEventListener('pagehide', stop);
        window.addEventListener('pageshow', schedule);
        syncToggle(); schedule();
    };
    // Vue mounts the storefront on load; bind to the resulting DOM.
    const afterMount = () => requestAnimationFrame(initialize);
    if (document.readyState === 'complete') afterMount();
    else window.addEventListener('load', afterMount, { once: true });
})();
</script>
