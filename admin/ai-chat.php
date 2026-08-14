<?php
/**
 * AI Chat: settings only for now -- provider, embed script or API key,
 * greeting text, enabled toggle. No public-site widget or LLM backend
 * yet; that ships once a provider is actually chosen. Values live in the
 * existing site_settings key/value table under the 'ai_chat' group,
 * same pattern settings.php uses for topbar/navbar/footer.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'ai_chat.view');
$canEdit = in_array('ai_chat.edit', $admin['permissions'], true);

const AI_CHAT_PROVIDERS = [
    ''         => '-- Not selected --',
    'openai'    => 'OpenAI (custom widget)',
    'intercom'  => 'Intercom',
    'crisp'     => 'Crisp',
    'custom'    => 'Custom embed script',
];

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        $errors[] = "You don't have permission to make changes here.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $provider = array_key_exists($_POST['ai_chat_provider'] ?? '', AI_CHAT_PROVIDERS) ? $_POST['ai_chat_provider'] : '';
        $embed    = trim((string) ($_POST['ai_chat_embed'] ?? ''));
        $greeting = trim((string) ($_POST['ai_chat_greeting'] ?? ''));
        $enabled  = isset($_POST['ai_chat_enabled']) ? '1' : '0';

        foreach ([
            'ai_chat_enabled'  => $enabled,
            'ai_chat_provider' => $provider,
            'ai_chat_embed'    => $embed,
            'ai_chat_greeting' => $greeting,
        ] as $key => $value) {
            db_exec(
                "INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, 'ai_chat')
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [$key, $value]
            );
        }
        // settings() caches per-request; this request is about to re-render
        // the form from POST values anyway, so no cache-bust needed here.
        admin_audit((int) $admin['id'], 'update', 'site_settings', null, 'ai_chat settings');
        $notice = 'AI chat settings saved.';
    }
}

// Queried directly rather than through setting()'s per-request cache, so a
// save above is reflected immediately instead of showing the pre-save value.
$aiRow = [];
foreach (db_all("SELECT setting_key, setting_value FROM site_settings WHERE setting_group = 'ai_chat'") as $row) {
    $aiRow[$row['setting_key']] = $row['setting_value'];
}
$aiEnabled  = ($aiRow['ai_chat_enabled'] ?? '0') === '1';
$aiProvider = $aiRow['ai_chat_provider'] ?? '';
$aiEmbed    = $aiRow['ai_chat_embed'] ?? '';
$aiGreeting = $aiRow['ai_chat_greeting'] ?? '';

$pageTitle = 'AI Chat';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'ai_chat';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main">
    <h1>AI Chat</h1>
    <p class="cd-admin-lede">Settings only for now. Turning this on does not add a chat widget to the
       public site yet -- that ships separately once a provider is chosen and wired up.</p>

<?php if (!$canEdit): ?>
    <div class="cd-admin-alert cd-admin-alert-info" role="status"><p>You have view-only access here. Changes are saved by someone with the ai_chat.edit permission.</p></div>
<?php endif; ?>
<?php if ($notice): ?>
    <div class="cd-admin-alert cd-admin-alert-ok"><p><?= esc($notice) ?></p></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="cd-admin-alert cd-admin-alert-error" role="alert">
<?php foreach ($errors as $e): ?>
        <p><?= esc($e) ?></p>
<?php endforeach; ?>
    </div>
<?php endif; ?>

    <section class="cd-admin-panel cd-admin-panel-narrow">
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="ai_chat_enabled" value="1" <?= $aiEnabled ? 'checked' : '' ?>>
                Enabled
            </label>

            <div>
                <label for="ai_chat_provider">Provider</label>
                <select id="ai_chat_provider" name="ai_chat_provider">
<?php foreach (AI_CHAT_PROVIDERS as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= $aiProvider === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
<?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="ai_chat_embed">Embed script / API key</label>
                <textarea id="ai_chat_embed" name="ai_chat_embed" rows="4"><?= esc($aiEmbed) ?></textarea>
                <span class="cd-admin-hint">Paste the provider's embed snippet, or an API key, depending on
                   what the provider needs. Stored as-is; not rendered on the public site yet.</span>
            </div>

            <div>
                <label for="ai_chat_greeting">Greeting text</label>
                <input type="text" id="ai_chat_greeting" name="ai_chat_greeting" maxlength="255" value="<?= esc($aiGreeting) ?>">
            </div>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Save AI chat settings</button>
            </div>
        </form>
<?php else: ?>
        <p><strong>Enabled:</strong> <?= $aiEnabled ? 'Yes' : 'No' ?></p>
        <p><strong>Provider:</strong> <?= esc(AI_CHAT_PROVIDERS[$aiProvider] ?? '(none)') ?></p>
        <p><strong>Greeting:</strong> <?= esc($aiGreeting ?: '(none)') ?></p>
<?php endif; ?>
    </section>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
