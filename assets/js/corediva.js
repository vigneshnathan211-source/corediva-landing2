/* Corediva site scripts. Loaded after the Synck theme bundle. */
(function () {
    'use strict';

    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var mobileQuery    = window.matchMedia('(max-width: 991px)');

    /* ---------------- Hero slider ---------------- */

    var heroEl = document.querySelector('.cd-hero-swiper');
    if (heroEl && typeof Swiper !== 'undefined') {
        new Swiper(heroEl, {
            loop: heroEl.querySelectorAll('.swiper-slide').length > 1,
            speed: 700,
            autoHeight: true,
            autoplay: {
                delay: 8000,
                disableOnInteraction: true,
                pauseOnMouseEnter: true
            },
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

    /* ---------------- Navigation ---------------- */

    var nav       = document.getElementById('cd-primary-nav');
    var menuBtn   = document.querySelector('.cd-menu-toggle');
    var toggles   = Array.prototype.slice.call(document.querySelectorAll('.cd-panel-toggle'));

    function closeAllPanels(except) {
        toggles.forEach(function (t) {
            if (t !== except) { t.setAttribute('aria-expanded', 'false'); }
        });
    }

    // On desktop the panels open on hover via CSS; the button only needs to
    // drive them for keyboard and touch. On mobile they behave as accordions.
    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var open = toggle.getAttribute('aria-expanded') === 'true';
            closeAllPanels(toggle);
            toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
        });
    });

    if (menuBtn && nav) {
        menuBtn.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            if (!open) { closeAllPanels(null); }
        });
    }

    // Click outside closes any open panel (desktop) or the whole menu (mobile).
    document.addEventListener('click', function (e) {
        if (nav && !nav.contains(e.target) && (!menuBtn || !menuBtn.contains(e.target))) {
            closeAllPanels(null);
            if (mobileQuery.matches && nav.classList.contains('is-open')) {
                nav.classList.remove('is-open');
                if (menuBtn) {
                    menuBtn.setAttribute('aria-expanded', 'false');
                    menuBtn.setAttribute('aria-label', 'Open menu');
                }
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeAllPanels(null); }
    });

    // Reset state when crossing the mobile breakpoint, so a menu left open on
    // one layout doesn't leak into the other.
    mobileQuery.addEventListener('change', function () {
        closeAllPanels(null);
        if (nav) { nav.classList.remove('is-open'); }
        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', 'false');
            menuBtn.setAttribute('aria-label', 'Open menu');
        }
    });

    /* ---------------- In-page anchors ---------------- */

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

            // Close the mobile menu after navigating to a section.
            if (nav && nav.classList.contains('is-open')) {
                nav.classList.remove('is-open');
                closeAllPanels(null);
                if (menuBtn) { menuBtn.setAttribute('aria-expanded', 'false'); }
            }
        });
    });

    /* ---------------- Form feedback ---------------- */

    var alertEl = document.querySelector('.lead-form-wrap .alert');
    if (alertEl) {
        alertEl.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'center' });
    }
}());
