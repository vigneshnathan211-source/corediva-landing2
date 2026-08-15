<?php
/**
 * Blog: a plain list + create/edit/delete form, unlike services/products'
 * section-builder pattern -- blog_posts.body is a single long-form field,
 * not an ordered stack of typed blocks, so there's no section vocabulary
 * to build a UI around. Posts are added over time rather than picked from
 * a fixed catalogue, so this is the one admin screen with a genuine
 * create-new flow (?new=1) rather than a country/service selector.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'blog.view');
$canEdit = in_array('blog.edit', $admin['permissions'], true);

$countries = get_countries();

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        $errors[] = "You don't have permission to make changes here.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'save_post') {
            $id          = (int) ($_POST['id'] ?? 0);
            $slug        = trim((string) ($_POST['slug'] ?? ''));
            $countryId   = (int) ($_POST['country_id'] ?? 0) ?: null;
            $category    = trim((string) ($_POST['category'] ?? '')) ?: null;
            $title       = trim((string) ($_POST['title'] ?? ''));
            $excerpt     = trim((string) ($_POST['excerpt'] ?? '')) ?: null;
            $body        = trim((string) ($_POST['body'] ?? '')) ?: null;
            $image       = trim((string) ($_POST['featured_image'] ?? '')) ?: null;
            $imageAlt    = trim((string) ($_POST['featured_image_alt'] ?? '')) ?: null;
            $author      = trim((string) ($_POST['author'] ?? '')) ?: null;
            $publishedAt = trim((string) ($_POST['published_at'] ?? '')) ?: null;
            $metaTitle   = trim((string) ($_POST['meta_title'] ?? '')) ?: null;
            $metaDesc    = trim((string) ($_POST['meta_description'] ?? '')) ?: null;
            $isPublished = isset($_POST['is_published']) ? 1 : 0;

            if ($title === '') {
                $errors[] = 'Enter a title.';
            }
            if ($slug === '' || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
                $errors[] = 'Slug must be lowercase letters, numbers and hyphens only.';
            }
            if (!$errors && db_value('SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id <> ?', [$slug, $id]) > 0) {
                $errors[] = 'That slug is already used by another post.';
            }

            if (!$errors) {
                if ($id > 0) {
                    db_exec(
                        'UPDATE blog_posts SET
                            slug=?, country_id=?, category=?, title=?, excerpt=?, body=?,
                            featured_image=?, featured_image_alt=?, author=?, published_at=?,
                            meta_title=?, meta_description=?, is_published=?
                         WHERE id = ?',
                        [$slug, $countryId, $category, $title, $excerpt, $body, $image, $imageAlt,
                         $author, $publishedAt, $metaTitle, $metaDesc, $isPublished, $id]
                    );
                    admin_audit((int) $admin['id'], 'update', 'blog_posts', $id, $slug);
                    $notice = 'Post saved.';
                } else {
                    db_exec(
                        'INSERT INTO blog_posts
                            (slug, country_id, category, title, excerpt, body, featured_image, featured_image_alt,
                             author, published_at, meta_title, meta_description, is_published)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                        [$slug, $countryId, $category, $title, $excerpt, $body, $image, $imageAlt,
                         $author, $publishedAt, $metaTitle, $metaDesc, $isPublished]
                    );
                    $id = (int) db()->lastInsertId();
                    admin_audit((int) $admin['id'], 'create', 'blog_posts', $id, $slug);
                    $notice = 'Post created.';
                }
                header('Location: ' . admin_url('blog.php?edit=' . $id . '&saved=1'));
                exit;
            }
        } elseif ($action === 'delete_post') {
            $id     = (int) ($_POST['id'] ?? 0);
            $target = db_one('SELECT id, slug FROM blog_posts WHERE id = ?', [$id]);
            if ($target === null) {
                $errors[] = 'That post no longer exists.';
            } else {
                db_exec('DELETE FROM blog_posts WHERE id = ?', [$id]);
                admin_audit((int) $admin['id'], 'delete', 'blog_posts', $id, (string) $target['slug']);
                $notice = 'Post deleted.';
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $notice = 'Post saved.';
}

$editing = null;
$isNew   = isset($_GET['new']);
if (isset($_GET['edit'])) {
    $editing = db_one('SELECT * FROM blog_posts WHERE id = ?', [(int) $_GET['edit']]);
    if ($editing === null) {
        $errors[] = 'That post no longer exists.';
    }
}
$showForm = $isNew || $editing !== null;

$posts = db_all(
    'SELECT p.*, c.name AS country_name FROM blog_posts p
     LEFT JOIN countries c ON c.id = p.country_id
     ORDER BY p.created_at DESC'
);

$pageTitle = 'Blog';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'blog';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main cd-admin-main-wide">
    <h1>Blog</h1>
    <p class="cd-admin-lede">Posts are unpublished by default. Nothing appears on the public site until "Published" is checked.</p>

<?php if (!$canEdit): ?>
    <div class="cd-admin-alert cd-admin-alert-info" role="status"><p>You have view-only access here. Changes are saved by someone with the blog.edit permission.</p></div>
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

<?php if ($showForm): ?>
<?php if ($canEdit): ?>
    <section class="cd-admin-panel">
        <h2><?= $editing ? 'Edit post' : 'New post' ?></h2>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_post">
<?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
<?php endif; ?>

            <div class="cd-admin-form-row">
                <div>
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required value="<?= esc($editing['title'] ?? '') ?>">
                </div>
                <div>
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" required value="<?= esc($editing['slug'] ?? '') ?>">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="country_id">Country</label>
                    <select id="country_id" name="country_id">
                        <option value="0" <?= empty($editing['country_id']) ? 'selected' : '' ?>>&mdash; All countries &mdash;</option>
<?php foreach ($countries as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($editing['country_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
<?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" value="<?= esc($editing['category'] ?? '') ?>">
                </div>
                <div>
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" value="<?= esc($editing['author'] ?? '') ?>">
                </div>
                <div>
                    <label for="published_at">Published date/time</label>
                    <input type="datetime-local" id="published_at" name="published_at"
                           value="<?= esc(!empty($editing['published_at']) ? str_replace(' ', 'T', substr((string) $editing['published_at'], 0, 16)) : '') ?>">
                </div>
            </div>

            <div>
                <label for="excerpt">Excerpt (used on the hub card)</label>
                <textarea id="excerpt" name="excerpt" rows="2"><?= esc($editing['excerpt'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="body">Body</label>
                <textarea id="body" name="body" rows="10" data-rich-text><?= esc($editing['body'] ?? '') ?></textarea>
                <span class="cd-admin-hint">Renders directly on the public page.</span>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="featured_image">Featured image URL</label>
                    <input type="text" id="featured_image" name="featured_image" value="<?= esc($editing['featured_image'] ?? '') ?>">
                </div>
                <div>
                    <label for="featured_image_alt">Image alt text</label>
                    <input type="text" id="featured_image_alt" name="featured_image_alt" value="<?= esc($editing['featured_image_alt'] ?? '') ?>">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="meta_title">Meta title</label>
                    <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= esc($editing['meta_title'] ?? '') ?>">
                </div>
                <div>
                    <label for="meta_description">Meta description</label>
                    <input type="text" id="meta_description" name="meta_description" maxlength="320" value="<?= esc($editing['meta_description'] ?? '') ?>">
                </div>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_published" value="1" <?= !empty($editing['is_published']) ? 'checked' : '' ?>>
                Published
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn"><?= $editing ? 'Save changes' : 'Create post' ?></button>
                <a href="<?= esc(admin_url('blog.php')) ?>" class="cd-admin-btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
<?php endif; ?>
<?php endif; ?>

    <section class="cd-admin-panel">
        <div class="cd-admin-form-actions" style="margin-bottom: 1rem;">
<?php if ($canEdit): ?>
            <a href="<?= esc(admin_url('blog.php?new=1')) ?>" class="cd-admin-btn">New post</a>
<?php endif; ?>
        </div>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr><th>Title</th><th>Country</th><th>Category</th><th>Published</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
<?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= esc($post['title']) ?></td>
                        <td><?= esc($post['country_name'] ?? 'All countries') ?></td>
                        <td><?= esc($post['category'] ?? '—') ?></td>
                        <td><?= esc($post['published_at'] ?? '—') ?></td>
                        <td>
<?php if ($post['is_published']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Published</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Draft</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if ($canEdit): ?>
                            <a href="<?= esc(admin_url('blog.php?edit=' . $post['id'])) ?>">Edit</a>
                            &nbsp;
                            <form method="post" class="cd-admin-inline-form" onsubmit="return confirm('Delete this post?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                <button type="submit" class="cd-admin-link-btn">Delete</button>
                            </form>
<?php endif; ?>
                        </td>
                    </tr>
<?php endforeach; ?>
<?php if (!$posts): ?>
                    <tr><td colspan="6">No posts yet.</td></tr>
<?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
