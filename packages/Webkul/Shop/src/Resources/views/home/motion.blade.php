<script>
    (() => {
        const initializeHomeMotion = () => {
            const home = document.querySelector('.louzir-home');

            if (!home || !('IntersectionObserver' in window) || !Element.prototype.animate) return;

            const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
            const animations = new Map();
            const targets = home.querySelectorAll(
                '.louzir-hero-photo, .louzir-hero-copy > *, .louzir-feature, .louzir-section-head, .louzir-card, .louzir-boutique-card, .louzir-community-card'
            );

            // Keep the document visible by default, including without JavaScript.
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;

                    const element = entry.target;
                    observer.unobserve(element);

                    if (preference.matches || element.contains(document.activeElement)) return;

                    const siblings = Array.from(element.parentElement.children);
                    const index = siblings.indexOf(element);
                    const isHeroContent = element.parentElement.classList.contains('louzir-hero-copy');
                    const isHeroImage = element.classList.contains('louzir-hero-photo');
                    // Animate opacity only so the image keeps its scroll-driven transform.
                    const frames = isHeroImage ? [{ opacity: 0 }, { opacity: 1 }] : [
                        { opacity: isHeroContent ? 0 : .25, transform: 'translateY(28px)' },
                        { opacity: 1, transform: 'translateY(0)' },
                    ];
                    const animation = element.animate(frames, {
                        duration: isHeroImage ? 1200 : (isHeroContent ? 800 : 650),
                        delay: isHeroImage ? 0 : (isHeroContent ? index * 100 : (window.innerWidth > 640 ? (index % 3) * 80 : 0)),
                        easing: 'cubic-bezier(.22, 1, .36, 1)',
                        fill: 'backwards',
                    });

                    animations.set(element, animation);
                    animation.onfinish = animation.oncancel = () => animations.delete(element);
                });
            }, { threshold: .12, rootMargin: '0px 0px -24px 0px' });

            if (!preference.matches) targets.forEach(element => observer.observe(element));

            home.addEventListener('focusin', event => {
                targets.forEach(element => {
                    if (!element.contains(event.target)) return;

                    observer.unobserve(element);
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
            });

            window.addEventListener('pagehide', stopMotion, { once: true });
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
