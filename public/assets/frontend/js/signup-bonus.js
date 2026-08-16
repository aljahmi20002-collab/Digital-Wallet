/* global jQuery */
(function ($) {
    'use strict';

    $(function () {
        var $overlay = $('#signupBonusOverlay');
        if ($overlay.length === 0) {
            return;
        }

        var acknowledgeUrl = $overlay.data('acknowledge-url');
        var acknowledged = false;
        var $modal = $overlay.find('.signup-bonus-modal');
        var previousActive = null;

        function getFocusable() {
            return $modal.find(
                'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
            ).filter(':visible');
        }

        function openModal() {
            previousActive = document.activeElement;
            $overlay.addClass('is-open');
            $('body').addClass('signup-bonus-popup-open');

            // Move focus to the modal shell so screen readers announce it
            // and keyboard tab order starts inside the modal.
            window.setTimeout(function () {
                $modal.attr('tabindex', '-1').trigger('focus');
            }, 30);
        }

        function closeModal() {
            $overlay.removeClass('is-open is-loading');
            $('body').removeClass('signup-bonus-popup-open');

            if (previousActive && typeof previousActive.focus === 'function') {
                try {
                    previousActive.focus();
                } catch (e) {
                    /* ignore — element may be detached */
                }
            }
        }

        function acknowledgeAndClose(navigateUrl) {
            if (acknowledged) {
                if (navigateUrl) {
                    window.location.href = navigateUrl;
                } else {
                    closeModal();
                }
                return;
            }

            acknowledged = true;
            $overlay.addClass('is-loading');

            var token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: acknowledgeUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).always(function () {
                if (navigateUrl) {
                    window.location.href = navigateUrl;
                } else {
                    closeModal();
                }
            });
        }

        // Auto-open on page load
        openModal();

        // Close (×) button and any CTA marked data-signup-bonus-close
        $overlay.on('click', '[data-signup-bonus-close]', function (event) {
            var $target = $(this);
            var href = $target.attr('href');

            if (href && href !== '#') {
                event.preventDefault();
                acknowledgeAndClose(href);
                return;
            }

            event.preventDefault();
            acknowledgeAndClose();
        });

        // Backdrop click — only when the click hits the overlay itself, not the modal
        $overlay.on('click', function (event) {
            if (event.target === this) {
                acknowledgeAndClose();
            }
        });

        // Escape key closes
        $(document).on('keydown.signupBonus', function (event) {
            if (event.key === 'Escape' && $overlay.hasClass('is-open')) {
                event.preventDefault();
                acknowledgeAndClose();
            }
        });

        // Focus trap — keep keyboard navigation inside the modal while open
        $(document).on('keydown.signupBonusTrap', function (event) {
            if (event.key !== 'Tab' || !$overlay.hasClass('is-open')) {
                return;
            }

            var $focusable = getFocusable();
            if ($focusable.length === 0) {
                return;
            }

            var first = $focusable.first()[0];
            var last  = $focusable.last()[0];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    });
})(jQuery);
