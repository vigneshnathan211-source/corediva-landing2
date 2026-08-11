/* Corediva site scripts. Loaded after the Synck theme bundle. */
(function () {
    'use strict';

    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var mobileQuery    = window.matchMedia('(max-width: 991px)');

    /* ---------------- Hero slider ---------------- */

    var heroEl = document.querySelector('.cd-hero-swiper');
    if (heroEl && typeof Swiper !== 'undefined') {
        var multiple = heroEl.querySelectorAll('.swiper-slide').length > 1;

        new Swiper(heroEl, {
            loop: multiple,
            speed: 700,
            autoHeight: true,

            // An auto-advancing carousel is exactly the kind of motion that
            // reduced-motion users ask to be spared, so drop it entirely
            // rather than merely slowing it down.
            autoplay: (multiple && !prefersReduced) && {
                delay: 6500,

                // Both of these default to fighting the user on a hero this
                // large. pauseOnMouseEnter would stall the carousel whenever
                // the cursor merely rests in the top half of the page, which
                // is most of the time on desktop, and disableOnInteraction
                // would kill autoplay permanently after a single click on a
                // pagination bar. Neither reads as intentional; both read as
                // broken.
                pauseOnMouseEnter: false,
                disableOnInteraction: false
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

    /* ---------------- Country switcher ---------------- */

    var countrySwitch = document.querySelector('.cd-country-switch');
    var countryToggle = document.getElementById('country-switcher');

    if (countrySwitch && countryToggle) {
        countryToggle.addEventListener('click', function (e) {
            e.preventDefault();
            var open = countrySwitch.classList.toggle('is-open');
            countryToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!countrySwitch.contains(e.target)) {
                countrySwitch.classList.remove('is-open');
                countryToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                countrySwitch.classList.remove('is-open');
                countryToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---------------- Scroll reveal (skeleton swap + stagger fade-in) ---------------- */

    var revealGrids = Array.prototype.slice.call(
        document.querySelectorAll('[data-cd-reveal], [data-cd-stagger]')
    );

    if (revealGrids.length && !prefersReduced) {
        // [data-cd-reveal] grids swap a skeleton placeholder for real
        // content via .cd-loading (see the Expertise section). Everything
        // else -- [data-cd-stagger] -- just needs .is-revealed added once,
        // permanently, to run its CSS stagger transition.
        var triggerReveal = function (grid) {
            if (grid.dataset.revealed) { return; }
            grid.dataset.revealed = 'true';
            if (grid.hasAttribute('data-cd-reveal')) {
                grid.classList.add('cd-loading');
                window.setTimeout(function () {
                    grid.classList.remove('cd-loading');
                }, 600);
            } else {
                grid.classList.add('is-revealed');
            }
        };

        if ('IntersectionObserver' in window) {
            var revealObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) { return; }
                    observer.unobserve(entry.target);
                    triggerReveal(entry.target);
                });
            }, { threshold: 0.2 });

            revealGrids.forEach(function (grid) { revealObserver.observe(grid); });
        }

        // Belt and suspenders: some layouts (body as the scrolling element
        // rather than the viewport -- this theme's body {overflow-x:hidden}
        // is exactly that trigger) have been seen to leave a default-root
        // IntersectionObserver never re-firing on scroll. A throttled
        // scroll/resize check against getBoundingClientRect catches those
        // cases too; triggerReveal's dataset guard stops either path from
        // firing twice.
        var revealCheckQueued = false;
        var checkRevealGrids = function () {
            revealCheckQueued = false;
            var viewportHeight = window.innerHeight;
            revealGrids.forEach(function (grid) {
                if (grid.dataset.revealed) { return; }
                var rect = grid.getBoundingClientRect();
                if (rect.top < viewportHeight * 0.8 && rect.bottom > 0) {
                    triggerReveal(grid);
                }
            });
        };
        var queueRevealCheck = function () {
            if (revealCheckQueued) { return; }
            revealCheckQueued = true;
            window.requestAnimationFrame(checkRevealGrids);
        };

        window.addEventListener('scroll', queueRevealCheck, { passive: true });
        window.addEventListener('resize', queueRevealCheck);
        checkRevealGrids();
    }

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
