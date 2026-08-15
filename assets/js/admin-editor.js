/**
 * Upgrades every `<textarea data-rich-text>` on an admin page into a Quill
 * WYSIWYG editor, so admins writing long-form copy for service/product/
 * blog/case-study fields don't have to hand-type HTML. The textarea stays
 * in the DOM (just hidden) as the actual form field Quill's HTML gets
 * copied back into on submit -- the server-side save logic is untouched,
 * it still just receives an HTML string in that field's POST value.
 *
 * Section-type fields (admin/services.php, admin/products.php) that are
 * conditionally shown/hidden by another script still get a Quill instance
 * up front; they're just hidden along with their wrapper until relevant.
 */
(function () {
    'use strict';

    if (typeof Quill === 'undefined') {
        return;
    }

    var toolbarOptions = [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean'],
    ];

    document.querySelectorAll('textarea[data-rich-text]').forEach(function (textarea) {
        var wrap = document.createElement('div');
        wrap.className = 'cd-admin-editor-wrap';
        textarea.parentNode.insertBefore(wrap, textarea);

        var editorEl = document.createElement('div');
        editorEl.className = 'cd-admin-editor';
        wrap.appendChild(editorEl);

        textarea.classList.add('cd-admin-editor-source');
        wrap.appendChild(textarea);

        var quill = new Quill(editorEl, {
            theme: 'snow',
            modules: { toolbar: toolbarOptions },
        });

        quill.root.innerHTML = textarea.value;

        var form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                textarea.value = typeof quill.getSemanticHTML === 'function'
                    ? quill.getSemanticHTML()
                    : quill.root.innerHTML;
            });
        }
    });
})();
