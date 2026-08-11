<?php
/**
 * Shared site footer + script tags.
 * Expects $country in scope.
 */

declare(strict_types=1);

$footerServices = array_slice(get_services(), 0, 6);
$footerProducts = array_slice(get_products((int) $country['id']), 0, 6);
$allOffices     = get_offices();
?>
    </div><!-- /#main-content -->

    <!-- Footer -->
    <footer class="footer-area">
        <img src="<?= esc(asset('imgs/bg-shape-4.svg')) ?>" alt="" aria-hidden="true"
             class="animation-slide-right bg-shape" />

        <div class="footer-top">
            <div class="custom-container">
                <div class="custom-row align-items-end justify-content-between">
                    <div class="left-content">
                        <a href="<?= esc(country_url($country['code'])) ?>" class="logo">
                            <img src="<?= esc(asset('imgs/corediva-logo-white.png')) ?>"
                                 alt="<?= esc(setting('site_name')) ?>" width="200" height="25" />
                        </a>
                        <p><?= esc(setting('site_tagline')) ?></p>

                        <div class="footer-clients d-flex align-items-center">
<?php foreach (get_partners() as $partner): ?>
                            <div class="footer-client-img" title="<?= esc($partner['description']) ?>">
                                <span><?= esc($partner['name']) ?></span>
                            </div>
<?php endforeach; ?>
                        </div>
                    </div>

                    <div class="right-content">
                        <div class="right-content-inner">
                            <h2>Let&rsquo;s get started on something great</h2>
                            <p>Tell us what you're building. We reply to every enquiry within
                               <?= esc(setting('response_time_hours', '4')) ?> hours on business days.</p>
                            <a href="#contact" class="theme-btn">Get a free consultation</a>

                            <div class="footer-experience d-flex align-items-center">
<?php foreach (get_stats() as $stat): ?>
                                <div class="footer-experience-item">
                                    <p class="cd-footer-stat"><?= esc($stat['value']) ?><span><?= esc($stat['suffix']) ?></span></p>
                                    <p><?= esc($stat['label']) ?></p>
                                </div>
<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="custom-container">
                <div class="custom-row">
                    <div class="footer-all-links-wrap justify-content-between d-flex">

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
<?php foreach (get_countries() as $c): ?>
                                <li><a href="<?= esc(country_url($c['code'])) ?>"><?= esc($c['name']) ?></a></li>
<?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="footer-links">
                            <h3>Offices</h3>
                            <ul>
<?php foreach ($allOffices as $office): ?>
                                <li>
                                    <strong><?= esc($office['city']) ?></strong><br>
                                    <small><?= esc($office['badge']) ?></small>
                                </li>
<?php endforeach; ?>
                            </ul>
                        </div>

                    </div>

                    <div class="footer-contact-info">
                        <div class="footer-contact-info-item">
                            <h4>Phone</h4>
                            <p><a href="tel:<?= esc(setting('phone_e164')) ?>"><?= esc(setting('phone_display')) ?></a></p>
                        </div>
                        <div class="footer-contact-info-item">
                            <h4>Mail</h4>
                            <p><a href="mailto:<?= esc(setting('email')) ?>"><?= esc(setting('email')) ?></a></p>
                        </div>
                        <div class="footer-contact-info-item">
                            <h4>WhatsApp</h4>
                            <p><a href="<?= esc(whatsapp_url()) ?>" target="_blank" rel="noopener">Message us</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="copyright-area">
            <div class="custom-container">
                <div class="custom-row d-flex align-items-center justify-content-between">
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
                    <p>&copy; <?= date('Y') ?> <?= esc(setting('site_name')) ?>.
                       All rights reserved. <?= esc(setting('credentials')) ?>.</p>
                </div>
            </div>
        </div>
    </footer>

</main>

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
