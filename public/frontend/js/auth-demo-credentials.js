/**
 * Auth demo credentials interactions.
 *
 * Three behaviours:
 *
 *   1. "Use this account" → autofills the login + password fields the
 *      auth blade renders (#login, #password), focuses the submit, and
 *      surfaces a simple-notify confirmation. We deliberately STOP short
 *      of auto-submitting so the visitor can review what they're about
 *      to send (matches the spec: "copy-paste hoye user ciale login
 *      korte parbe").
 *
 *   2. Copy email / copy password → tries `navigator.clipboard.writeText`
 *      first, falls back to a synthetic textarea + `document.execCommand`
 *      for browsers that block the modern API (older Safari, file://, etc).
 *      Surfaces a transient "Copied!" state on the button and a toast.
 *
 *   3. Defensive: every selector is null-checked, errors degrade to a
 *      neutral "Could not copy" toast — never throw uncaught to the user.
 */
(function () {
    'use strict';

    var SELECTOR_BLOCK = '[data-demo-credentials]';
    var SELECTOR_ITEM  = '[data-demo-credentials-item]';
    var SELECTOR_FILL  = '[data-demo-credentials-fill]';
    var SELECTOR_COPY  = '[data-demo-credentials-copy]';
    var COPIED_CLASS   = 'is-copied';
    var COPIED_RESET_MS = 1600;

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function notify(type, message) {
        if (typeof window.Notify !== 'function') {
            return;
        }

        try {
            new window.Notify({
                status: type,
                title: type === 'success' ? 'Done' : 'Notice',
                text: message,
                effect: 'fade',
                speed: 300,
                showIcon: true,
                showCloseButton: false,
                autoclose: true,
                autotimeout: 2400,
                gap: 16,
                position: 'right top',
                customClass: 'demo-credentials-toast'
            });
        } catch (e) {
            /* simple-notify not available — silently ignore */
        }
    }

    function copyText(text) {
        if (!text) {
            return Promise.reject(new Error('empty'));
        }

        if (window.navigator && window.navigator.clipboard && window.navigator.clipboard.writeText) {
            return window.navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            try {
                var area = document.createElement('textarea');
                area.value = text;
                area.setAttribute('readonly', '');
                area.style.position = 'absolute';
                area.style.left = '-9999px';
                document.body.appendChild(area);
                area.select();
                var ok = document.execCommand('copy');
                document.body.removeChild(area);
                ok ? resolve() : reject(new Error('execCommand-failed'));
            } catch (err) {
                reject(err);
            }
        });
    }

    function flashCopied(button) {
        if (!button) {
            return;
        }
        button.classList.add(COPIED_CLASS);
        var icon = button.querySelector('i');
        var iconClasses = icon ? icon.className : null;
        if (icon) {
            icon.className = 'fa-regular fa-circle-check';
        }
        window.setTimeout(function () {
            button.classList.remove(COPIED_CLASS);
            if (icon && iconClasses !== null) {
                icon.className = iconClasses;
            }
        }, COPIED_RESET_MS);
    }

    function findForm(block) {
        var formId = block.getAttribute('data-demo-credentials-form');
        if (formId) {
            var byId = document.getElementById(formId);
            if (byId) {
                return byId;
            }
        }

        // Fallback: closest form to the credentials block, then the
        // first form on the page (auth pages render exactly one).
        return block.closest('form') || document.querySelector('.auth-card form') || document.querySelector('form');
    }

    function fill(block, item) {
        var form = findForm(block);
        if (!form) {
            notify('error', 'No login form found on this page.');
            return;
        }

        var email = item.getAttribute('data-demo-email') || '';
        var password = item.getAttribute('data-demo-password') || '';

        var loginField = form.querySelector('#login, [name="login"], [name="email"]');
        var passwordField = form.querySelector('#password, [name="password"]');

        if (!loginField || !passwordField) {
            notify('error', 'Login fields missing from the form.');
            return;
        }

        loginField.value = email;
        loginField.dispatchEvent(new Event('input', { bubbles: true }));
        loginField.dispatchEvent(new Event('change', { bubbles: true }));

        passwordField.value = password;
        passwordField.dispatchEvent(new Event('input', { bubbles: true }));
        passwordField.dispatchEvent(new Event('change', { bubbles: true }));

        var submit = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submit && typeof submit.focus === 'function') {
            try { submit.focus({ preventScroll: false }); } catch (e) { submit.focus(); }
        }

        notify('success', 'Demo credentials filled. Click Sign In when ready.');
    }

    function bindBlock(block) {
        if (block.dataset.demoCredentialsBound === '1') {
            return;
        }
        block.dataset.demoCredentialsBound = '1';

        block.addEventListener('click', function (event) {
            var fillBtn = event.target.closest(SELECTOR_FILL);
            if (fillBtn) {
                event.preventDefault();
                var item = fillBtn.closest(SELECTOR_ITEM);
                if (item) {
                    fill(block, item);
                }
                return;
            }

            var copyBtn = event.target.closest(SELECTOR_COPY);
            if (copyBtn) {
                event.preventDefault();
                var text = copyBtn.getAttribute('data-clipboard-text') || '';
                var label = copyBtn.getAttribute('data-demo-credentials-copy') || 'value';
                copyText(text).then(function () {
                    flashCopied(copyBtn);
                    notify('success', 'Copied ' + label + ' to clipboard.');
                }).catch(function () {
                    notify('error', 'Could not copy — select the text manually.');
                });
            }
        });
    }

    ready(function () {
        var blocks = document.querySelectorAll(SELECTOR_BLOCK);
        for (var i = 0; i < blocks.length; i++) {
            bindBlock(blocks[i]);
        }
    });
})();
