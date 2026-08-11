<?php
/**
 * Reusable lead capture form.
 *
 * Expects in scope:
 *   $country      array        row from `countries`
 * Optional:
 *   $lead_variant string       'full' (default) or 'short'
 *   $lead_result  array|null   return value of save_lead(), if the page
 *                              already handled a POST
 *
 * The page including this is responsible for handling the POST, e.g.:
 *
 *   $lead_result = null;
 *   if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
 *       $lead_result = save_lead($_POST);
 *   }
 */

declare(strict_types=1);

$lead_variant = $lead_variant ?? 'full';
$lead_result  = $lead_result  ?? null;
$isShort      = $lead_variant === 'short';
$sourceUrl    = ($_SERVER['REQUEST_URI'] ?? '/');
?>

<div class="lead-form-wrap">

<?php if ($lead_result && $lead_result['ok']): ?>
    <div class="alert alert-success" role="status">
        <strong>Thank you.</strong> Your enquiry has reached us — we reply within
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

    <form method="post" action="#contact" class="contact-form" novalidate>
        <input type="hidden" name="form" value="lead">
        <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="country_code" value="<?= esc($country['code']) ?>">
        <input type="hidden" name="source_url" value="<?= esc($sourceUrl) ?>">

        <?php /* Honeypot: hidden from humans, irresistible to bots. */ ?>
        <div class="hp-field" aria-hidden="true"
             style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
            <label for="website">Website</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="input-row d-flex">
            <div class="input-group">
                <label for="lead-name">Your name <span aria-hidden="true">*</span></label>
                <input type="text" id="lead-name" name="name" required
                       autocomplete="name"
                       value="<?= esc($_POST['name'] ?? '') ?>">
            </div>
            <div class="input-group">
                <label for="lead-email">Email address <span aria-hidden="true">*</span></label>
                <input type="email" id="lead-email" name="email" required
                       autocomplete="email"
                       value="<?= esc($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="input-row d-flex">
            <div class="input-group">
                <label for="lead-phone">Phone / WhatsApp</label>
                <input type="tel" id="lead-phone" name="phone"
                       autocomplete="tel"
                       value="<?= esc($_POST['phone'] ?? '') ?>">
            </div>
            <div class="input-group">
                <label for="lead-service">What do you need?</label>
                <select id="lead-service" name="service_interest">
                    <option value="">Select a service</option>
<?php foreach (get_services() as $svc): ?>
                    <option value="<?= esc($svc['title']) ?>"
                        <?= (($_POST['service_interest'] ?? '') === $svc['title']) ? 'selected' : '' ?>>
                        <?= esc($svc['title']) ?>
                    </option>
<?php endforeach; ?>
                </select>
            </div>
        </div>

<?php if (!$isShort): ?>
        <div class="input-row">
            <div class="input-group">
                <label for="lead-message">Tell us about your project</label>
                <textarea id="lead-message" name="message" rows="5"><?= esc($_POST['message'] ?? '') ?></textarea>
            </div>
        </div>
<?php endif; ?>

        <div class="input-row">
            <div class="input-group">
                <button type="submit" class="theme-btn">
                    Get my free consultation <i class="iconoir-arrow-up-right"></i>
                </button>
            </div>
        </div>

        <p class="form-note">
            <small>
                We use your details only to respond to this enquiry.
                <?php if ($country['code'] === 'sg'): ?>
                    Handled in line with Singapore&rsquo;s PDPA.
                <?php endif; ?>
            </small>
        </p>
    </form>

<?php endif; ?>

</div>
