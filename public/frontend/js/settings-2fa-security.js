"use strict";

(function ($) {
    function setCopyButtonState($button, isSuccess) {
        var label = isSuccess ? $button.data('copy-success-label') : $button.data('copy-default-label');

        $button.attr('title', label);
        $button.attr('aria-label', label);
        $button.find('[data-copy-icon-default]').toggleClass('d-none', isSuccess);
        $button.find('[data-copy-icon-success]').toggleClass('d-none', !isSuccess);
    }

    function resetCopyButtonState($button) {
        setCopyButtonState($button, false);
        $button.prop('disabled', false);
    }

    function fallbackCopyText(value) {
        var $tempInput = $('<textarea>', {
            class: 'visually-hidden',
            readonly: true
        }).val(value).appendTo('body');

        $tempInput.trigger('focus').trigger('select');

        var isCopied = document.execCommand('copy');

        $tempInput.remove();

        if (!isCopied) {
            throw new Error('Copy command failed.');
        }
    }

    async function copySecretKey(value) {
        if (window.isSecureContext && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            await navigator.clipboard.writeText(value);

            return;
        }

        fallbackCopyText(value);
    }

    $(document).on('click', '.settings-2fa-scan__copy', async function () {
        var $button = $(this);
        var value = $button.data('copy-target');

        if (!value) {
            return;
        }

        $button.prop('disabled', true);

        try {
            await copySecretKey(value);

            setCopyButtonState($button, true);

            window.setTimeout(function () {
                resetCopyButtonState($button);
            }, 1500);
        } catch (error) {
            resetCopyButtonState($button);

            if (typeof notifyEvs === 'function') {
                notifyEvs('error', $button.data('copy-failed-message'));
            }
        }
    });
})(jQuery);
