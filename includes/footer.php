<?php
/**
 * Shared site footer + script tags.
 * Expects $country in scope.
 */

declare(strict_types=1);

$footerServices = array_slice(get_services(), 0, 6);
$footerProducts = array_slice(get_products((int) $country['id']), 0, 6);
$allOffices     = get_offices();

/* Markets previously linked to every country row regardless of whether
 * that country's page exists -- 404s. nav_target() already guards against
 * this for the header nav; footer links get the same filesystem check
 * rather than assuming the URL pattern implies the page is real. */
$footerCountries = array_map(
    static fn (array $c) => $c + ['built' => is_file(path_to_file($c['code'], ''))],
    get_countries()
);
?>
    </div><!-- /#main-content -->

    <!-- Footer -->
    <footer class="footer-area cd-footer">
        <img src="<?= esc(asset('imgs/bg-shape-4.svg')) ?>" alt="" aria-hidden="true"
             class="animation-slide-right bg-shape" />

        <div class="footer-top">
            <div class="custom-container">
                <div class="cd-footer-top-grid">
                    <div class="cd-footer-brand">
                        <a href="<?= esc(country_url($country['code'])) ?>" class="logo">
                            <img src="<?= esc(asset('imgs/corediva-logo-white.png')) ?>"
                                 alt="<?= esc(setting('site_name')) ?>" width="200" height="25" />
                        </a>
                        <p><?= esc(setting('site_tagline')) ?></p>
                    </div>

                    <div class="cd-footer-cta">
                        <h2>Talk to an engineer, not a sales queue.</h2>
                        <p>Tell us what you're building. We reply within
                           <?= esc(setting('response_time_hours', '4')) ?> hours on business days,
                           no sales sequence.</p>
                        <a href="<?= esc(whatsapp_url()) ?>" target="_blank" rel="noopener" class="theme-btn">
                            Get a free consultation
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom cd-footer-bottom">
            <div class="custom-container">
                <div class="cd-footer-columns">

                    <div class="footer-links">
                        <h3>Services</h3>
                        <ul>
<?php foreach ($footerServices as $svc): ?>
                            <li><a href="#services"><?= esc($svc['title']) ?></a></li>
<?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="footer-links">
                        <h3>Products</h3>
                        <ul>
<?php foreach ($footerProducts as $prod): ?>
                            <li><a href="#products"><?= esc($prod['display_title']) ?></a></li>
<?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="footer-links">
                        <h3>Markets</h3>
                        <ul>
<?php foreach ($footerCountries as $c): ?>
                            <li>
<?php if ($c['built']): ?>
                                <a href="<?= esc(country_url($c['code'])) ?>"><?= esc($c['name']) ?></a>
<?php else: ?>
                                <span class="cd-footer-pending"><?= esc($c['name']) ?></span>
<?php endif; ?>
                            </li>
<?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="footer-links cd-footer-company">
                        <h3>Company</h3>
                        <ul class="cd-footer-offices">
<?php foreach ($allOffices as $office): ?>
                            <li><strong><?= esc($office['city']) ?></strong> &mdash; <?= esc($office['badge']) ?></li>
<?php endforeach; ?>
                        </ul>
                        <ul class="cd-footer-contact">
                            <li>
                                <i class="las la-phone" aria-hidden="true"></i>
                                <a href="tel:<?= esc(setting('phone_e164')) ?>"><?= esc(setting('phone_display')) ?></a>
                            </li>
                            <li>
                                <i class="las la-envelope" aria-hidden="true"></i>
                                <a href="mailto:<?= esc(setting('email')) ?>"><?= esc(setting('email')) ?></a>
                            </li>
                            <li>
                                <i class="lab la-whatsapp" aria-hidden="true"></i>
                                <a href="<?= esc(whatsapp_url()) ?>" target="_blank" rel="noopener">Message us</a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <div class="copyright-area">
            <div class="custom-container">
                <div class="cd-footer-legal-row">
                    <ul class="social-links d-flex align-items-center">
<?php foreach (get_social_links() as $social): ?>
                        <li>
                            <a href="<?= esc($social['url']) ?>" target="_blank" rel="noopener"
                               aria-label="<?= esc($social['platform']) ?>">
                                <i class="<?= esc($social['icon']) ?>"></i>
                            </a>
                        </li>
<?php endforeach; ?>
                    </ul>

                    <p class="cd-footer-copyright">&copy; <?= date('Y') ?> <?= esc(setting('site_name')) ?>.
                       All rights reserved. <?= esc(setting('credentials')) ?>.</p>

<?php
    /* No privacy/terms pages exist yet. Shown as pending labels (same
     * treatment as the header's unbuilt nav entries) rather than either
     * a dead link or omitting the legal row outright. */
?>
                    <ul class="cd-footer-legal">
                        <li><span class="cd-footer-pending">Privacy Policy</span></li>
                        <li><span class="cd-footer-pending">Terms of Service</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

</main>

<a href="<?= esc(whatsapp_url()) ?>" class="cd-whatsapp-float" target="_blank" rel="noopener"
   aria-label="Chat with us on WhatsApp">
    <i class="lab la-whatsapp" aria-hidden="true"></i>
</a>

<script src="<?= esc(asset('js/jquery-3.7.0.min.js')) ?>"></script>
<script src="<?= esc(asset('js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= esc(asset('js/gsap.min.js')) ?>"></script>
<script src="<?= esc(asset('js/Draggable.min.js')) ?>"></script>
<script src="<?= esc(asset('js/swiper-bundle.min.js')) ?>"></script>
<script src="<?= esc(asset('js/client-marquee.js')) ?>"></script>
<script src="<?= esc(asset('js/theme-custom.js')) ?>"></script>
<script src="<?= esc(asset('js/corediva.js')) ?>"></script>

</body>
</html>
