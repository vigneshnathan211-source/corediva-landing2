/* Corediva site scripts. Loaded after the Synck theme bundle. */
(function () {
    'use strict';

    // Hero slider. Guarded so pages without a hero don't error.
    var heroEl = document.querySelector('.cd-hero-swiper');
    if (heroEl && typeof Swiper !== 'undefined') {
        new Swiper(heroEl, {
            loop: heroEl.querySelectorAll('.swiper-slide').length > 1,
            speed: 700,
            autoplay: { delay: 7000, disableOnInteraction: true },
            pagination: {
                el: '.cd-hero-pagination',
                clickable: true
            },
            a11y: {
                enabled: true,
                prevSlideMessage: 'Previous slide',
                nextSlideMessage: 'Next slide'
            }
        });
    }

    // Smooth scroll for in-page anchors, respecting reduced-motion.
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var id = link.getAttribute('href');
            if (id === '#' || id.length < 2) { return; }
            var target = document.querySelector(id);
            if (!target) { return; }
            e.preventDefault();
            target.scrollIntoView({
                behavior: prefersReduced ? 'auto' : 'smooth',
                block: 'start'
            });
            history.replaceState(null, '', id);
        });
    });

    // If the lead form came back with errors or a success message, bring it
    // into view so the user isn't left staring at the top of the page.
    var alertEl = document.querySelector('.lead-form-wrap .alert');
    if (alertEl) {
        alertEl.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'center' });
    }
}());
