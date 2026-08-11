<?php
/**
 * Reusable lead capture form.
 *
 * Expects in scope:
 *   $country      array        row from `countries`
 * Optional:
 *   $lead_variant string       'hero' | 'short' | 'full' | 'rfq' (default 'full')
 *   $lead_result  array|null   return value of save_lead(), if the page
 *                              already handled a POST
 *   $lead_service array|null   a single row from `services`. When set, the
 *                              "Interested in" field is restricted to this
 *                              one service instead of listing all of them --
 *                              for service-detail pages, where the visitor
 *                              already picked a service by being on that
 *                              page. Ignored on 'hero', which has no service
 *                              field at all.
 *
 * The page including this is responsible for handling the POST, e.g.:
 *
 *   $lead_result = null;
 *   if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
 *       $lead_result = save_lead($_POST);
 *   }
 *
 * Variants:
 *   hero  -- compact card for the hero panel: name, email, phone, interest
 *   short -- same fields, page-body styling
 *   full  -- adds the free-text message field
 *   rfq   -- "Request a Quote" framing for the services overview page and
 *            (once built) individual service-detail pages; same fields as
 *            full, restrictable via $lead_service
 */

declare(strict_types=1);

$lead_variant = $lead_variant ?? 'full';
$lead_result  = $lead_result  ?? null;
$lead_service = $lead_service ?? null;
$isHero       = $lead_variant === 'hero';
$isRfq        = $lead_variant === 'rfq';
$showHeader   = $isHero || $isRfq;
$showMessage  = $lead_variant === 'full' || $isRfq;
$sourceUrl    = ($_SERVER['REQUEST_URI'] ?? '/');
$formId       = $isHero ? 'hero' : ($isRfq ? 'rfq' : 'main');
?>

<div class="lead-form-wrap<?= $isHero ? ' cd-hero-form' : '' ?><?= $isRfq ? ' cd-rfq-form' : '' ?>" id="<?= $formId ?>-form">

<?php if ($showHeader): ?>
<?php if ($isHero): ?>
    <span class="cd-form-kicker">Free Consultation</span>
    <h2 class="cd-form-title">Tell us what you're building</h2>
    <p class="cd-form-sub">We'll respond within <?= esc(setting('response_time_hours', '4')) ?> hours.</p>
<?php else: ?>
    <span class="cd-form-kicker">Request a Quote</span>
    <h2 class="cd-form-title"><?= $lead_service ? 'Get a quote for ' . esc($lead_service['title']) : 'Get a fixed-scope quote' ?></h2>
    <p class="cd-form-sub">Tell us what you need and we'll reply within <?= esc(setting('response_time_hours', '4')) ?> hours with next steps, not a sales call.</p>
<?php endif; ?>
<?php endif; ?>

<?php if ($lead_result && $lead_result['ok']): ?>
    <div class="alert alert-success" role="status">
        <strong>Thank you.</strong> Your enquiry has reached us. We reply within
        <?= esc(setting('response_time_hours', '4')) ?> hours on business days.
    </div>
<?php else: ?>

    <?php if ($lead_result && !empty($lead_result['errors'])): ?>
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
        <?php foreach ($lead_result['errors'] as $error): ?>
            <li><?= esc($error) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post" action="#<?= $formId ?>-form" class="cd-form" novalidate>
        <input type="hidden" name="form" value="lead">
        <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="country_code" value="<?= esc($country['code']) ?>">
        <input type="hidden" name="source_url" value="<?= esc($sourceUrl) ?>">

        <?php /* Honeypot: hidden from humans, irresistible to bots. */ ?>
        <div class="cd-hp" aria-hidden="true">
            <label for="website-<?= $formId ?>">Website</label>
            <input type="text" id="website-<?= $formId ?>" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="cd-field">
            <label for="name-<?= $formId ?>">Name <span class="cd-req" aria-hidden="true">*</span></label>
            <input type="text" id="name-<?= $formId ?>" name="name" required
                   autocomplete="name" placeholder="John Doe"
                   value="<?= esc($_POST['name'] ?? '') ?>">
        </div>

        <div class="cd-field">
            <label for="email-<?= $formId ?>">Email <span class="cd-req" aria-hidden="true">*</span></label>
            <input type="email" id="email-<?= $formId ?>" name="email" required
                   autocomplete="email" placeholder="john@company.com"
                   value="<?= esc($_POST['email'] ?? '') ?>">
        </div>

        <div class="cd-field">
            <label for="phone-<?= $formId ?>">WhatsApp / Mobile</label>
            <div class="cd-phone-row">
                <span class="cd-dial-code" aria-hidden="true">
                    <strong><?= esc(strtoupper($country['code'])) ?></strong> <?= esc($country['dial_code']) ?>
                </span>
                <input type="tel" id="phone-<?= $formId ?>" name="phone"
                       autocomplete="tel" placeholder="9876 5432"
                       value="<?= esc($_POST['phone'] ?? '') ?>">
            </div>
        </div>

<?php if ($lead_service): ?>
        <div class="cd-field">
            <label for="service-<?= $formId ?>">Service</label>
            <select id="service-<?= $formId ?>" name="service_interest">
                <option value="<?= esc($lead_service['title']) ?>" selected><?= esc($lead_service['title']) ?></option>
            </select>
        </div>
<?php else: ?>
        <div class="cd-field">
            <label for="service-<?= $formId ?>">Interested in</label>
            <select id="service-<?= $formId ?>" name="service_interest">
                <option value="">Select a service</option>
<?php foreach (get_services() as $svc): ?>
                <option value="<?= esc($svc['title']) ?>"
                    <?= (($_POST['service_interest'] ?? '') === $svc['title']) ? 'selected' : '' ?>>
                    <?= esc($svc['title']) ?>
                </option>
<?php endforeach; ?>
            </select>
        </div>
<?php endif; ?>

<?php if ($showMessage): ?>
        <div class="cd-field">
            <label for="message-<?= $formId ?>">Tell us about your project</label>
            <textarea id="message-<?= $formId ?>" name="message" rows="5"
                      placeholder="What are you trying to solve?"><?= esc($_POST['message'] ?? '') ?></textarea>
        </div>
<?php endif; ?>

        <button type="submit" class="theme-btn cd-btn-block">
            <?= $isRfq ? 'Request a Quote' : 'Get Free Consultation' ?> <i class="iconoir-arrow-up-right" aria-hidden="true"></i>
        </button>

        <p class="cd-form-note">
            No spam. Your details go straight to our engineering team.
            <?php if ($country['code'] === 'sg'): ?>Handled in line with Singapore's PDPA.<?php endif; ?>
        </p>
    </form>

<?php endif; ?>

</div>
