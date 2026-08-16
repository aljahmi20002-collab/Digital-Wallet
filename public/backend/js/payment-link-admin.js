"use strict";

/**
 * Admin Payment Link — show page interactions.
 *
 * Single behaviour: click-to-copy on the public URL row. Uses
 * navigator.clipboard when available and falls back to a hidden textarea
 * + execCommand. Visual feedback comes from data attributes / Bootstrap
 * utility classes only — no inline styles, no injected stylesheets.
 */
(function () {
    const COPY_SELECTOR = '[data-pla-copy]';
    const COPIED_CLASS  = 'pla-copy-success';
    const RESET_DELAY   = 1600;

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            const helper = document.createElement('textarea');
            helper.value           = text;
            helper.setAttribute('readonly', '');
            helper.setAttribute('aria-hidden', 'true');
            helper.classList.add('visually-hidden');
            document.body.appendChild(helper);
            helper.select();

            try {
                const ok = document.execCommand('copy');
                document.body.removeChild(helper);
                ok ? resolve() : reject();
            } catch (err) {
                document.body.removeChild(helper);
                reject(err);
            }
        });
    }

    function flash(button) {
        button.classList.add(COPIED_CLASS);
        button.setAttribute('data-pla-copied', '1');

        window.setTimeout(function () {
            button.classList.remove(COPIED_CLASS);
            button.removeAttribute('data-pla-copied');
        }, RESET_DELAY);
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest(COPY_SELECTOR);
        if (!button) {
            return;
        }

        event.preventDefault();
        const value = button.getAttribute('data-pla-copy') || '';

        if (!value) {
            return;
        }

        copyText(value).then(function () {
            flash(button);
        }).catch(function () {
            // Silent failure — clipboard access can be denied; the input
            // group still shows the URL so the admin can copy manually.
        });
    });
})();
