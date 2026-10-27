<script>
    (() => {
        const initializeHomeMotion = () => {
            const home = document.querySelector('.louzir-home');

            if (!home || home.dataset.motionInitialized) return;
            home.dataset.motionInitialized = 'true';

            const marquee = home.querySelector('.louzir-marquee');
            const toggle = marquee?.querySelector('.louzir-marquee-toggle');

            toggle?.addEventListener('click', () => {
                const paused = marquee.classList.toggle('is-paused');
                const label = paused ? toggle.dataset.resumeLabel : toggle.dataset.pauseLabel;
                toggle.setAttribute('aria-pressed', String(paused));
                toggle.setAttribute('aria-label', label);
                toggle.setAttribute('title', label);
            });

            if (home.dataset.motionEnabled === '0' || !('IntersectionObserver' in window) || !Element.prototype.animate) return;

            const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
            const desktop = window.matchMedia('(min-width: 1024px) and (hover: hover) and (pointer: fine)');
            const duration = Math.max(250, Math.min(1000, Number(home.dataset.motionDuration) || 700));
            const stagger = Math.max(0, Math.min(120, Number(home.dataset.motionStagger) || 0));
            const animations = new Map();
            const revealed = new WeakSet();
            const targets = new Set(home.querySelectorAll(
                '.louzir-feature, .louzir-section-head, .louzir-card, .louzir-signature-head, .louzir-signature-product, .louzir-boutique-card, .louzir-community-card'
            ));

            const animateEntrance = (element, index, distance = 8) => {
                if (preference.matches || element.contains(document.activeElement)) return;

                animations.get(element)?.cancel();
                const frames = desktop.matches
                    ? [{ opacity: .65, transform: `translateY(${distance}px)` }, { opacity: 1, transform: 'translateY(0)' }]
                    : [{ opacity: .7 }, { opacity: 1 }];
                const animation = element.animate(frames, {
                    duration: desktop.matches ? duration : Math.min(350, duration),
                    delay: Math.min(index, 5) * (desktop.matches ? stagger : Math.min(stagger, 30)),
                    easing: 'cubic-bezier(.22, 1, .36, 1)',
                    fill: 'backwards',
                });
                animations.set(element, animation);
                animation.onfinish = animation.oncancel = () => {
                    if (animations.get(element) === animation) animations.delete(element);
                };
            };

            // Keep painted content visible. Only sections entering later receive an entrance.
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;

                    const element = entry.target;
                    observer.unobserve(element);
                    revealed.add(element);

                    if (preference.matches || element.contains(document.activeElement)) return;

                    const siblings = Array.from(element.parentElement.children);
                    const index = siblings.indexOf(element);
                    animateEntrance(element, index % 3);
                });
            }, { threshold: .12, rootMargin: '0px 0px -24px 0px' });

            const observeBelowFold = element => {
                if (!revealed.has(element) && !preference.matches && element.getBoundingClientRect().top >= window.innerHeight) {
                    observer.observe(element);
                }
            };
            targets.forEach(observeBelowFold);

            home.addEventListener('louzir-products-ready', event => {
                event.target.querySelectorAll('.louzir-signature-product').forEach(element => {
                    if (targets.has(element)) return;

                    targets.add(element);
                    observeBelowFold(element);
                });
            });

            home.addEventListener('focusin', event => {
                targets.forEach(element => {
                    if (!element.contains(event.target)) return;

                    observer.unobserve(element);
                    revealed.add(element);
                    animations.get(element)?.cancel();
                });
            });

            const stopMotion = () => {
                observer.disconnect();
                animations.forEach(animation => animation.cancel());
                animations.clear();
            };

            preference.addEventListener('change', event => {
                if (event.matches) stopMotion();
                else targets.forEach(observeBelowFold);
            });

            window.addEventListener('pagehide', stopMotion);
            window.addEventListener('pageshow', event => {
                if (event.persisted) targets.forEach(observeBelowFold);
            });
        };

        // The storefront mounts Vue on load and replaces the original DOM.
        const afterMount = () => requestAnimationFrame(initializeHomeMotion);

        if (document.readyState !== 'complete') {
            window.addEventListener('load', afterMount, { once: true });
        } else {
            afterMount();
        }
    })();
</script>
