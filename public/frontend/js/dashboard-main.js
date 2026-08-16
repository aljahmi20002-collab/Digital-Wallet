"use strict";

/* =========================================================================
 * Dashboard Main Script
 * - UI interactions (navbar, sidebar, slider, tooltips, clipboard)
 * - Desktop scroll manager (Facebook-style smooth scroll routing)
 * ========================================================================= */

(function ($) {

    /* -----------------------------------------------------------------
     * § UI Interactions
     * ----------------------------------------------------------------- */

    $(document).ready(function () {

        /* Navbar Dropdown Link Prevent */
        $(document).on('click', '.navbar-area .navbar-nav li.menu-item-has-children > a', function (e) {
            e.preventDefault();
        });

        /* Search Popup */
        var $bodyOverlay = $('#body-overlay');
        var $searchPopup = $('#td-search-popup');
        var $searchBtn   = $('.search-bar-btn');
        var $sidebarMenu = $('#sidebar-menu');

        $(document).on('click', '#body-overlay', function (e) {
            e.preventDefault();
            $bodyOverlay.removeClass('active');
            $searchPopup.removeClass('active');
            $sidebarMenu.removeClass('active');
        });

        $(document).on('click', '.search-bar-btn', function (e) {
            e.preventDefault();
            $searchPopup.toggleClass('active');
            $searchBtn.toggleClass('active');
        });

        /* Mobile Sidebar Menu */
        $(document).on('click', '.sidebar-menu-close', function (e) {
            e.preventDefault();
            $bodyOverlay.removeClass('active');
            $sidebarMenu.removeClass('active');
        });

        $(document).on('click', '#navigation-button', function (e) {
            e.preventDefault();
            $sidebarMenu.addClass('active');
            $bodyOverlay.addClass('active');
        });

        /* Wallet Slider */
        if ($('.walet-slider').length) {
            $('.walet-slider').each(function () {
                var $slider = $(this);
                var isSidebarWallet = $slider.is('[data-sidebar-wallet-slider]');

                $slider.owlCarousel({
                    nav: true,
                    margin: 5,
                    dots: isSidebarWallet,
                    smartSpeed: 1500,
                    items: 1,
                    loop: false,
                    autoplay: false,
                    navText: [
                        '<i class="fa fa-angle-left mx-2"></i>',
                        '<i class="fa fa-angle-right"></i>'
                    ],
                    responsive: {
                        0:   { items: 1 },
                        576: { items: isSidebarWallet ? 1 : 2 },
                        992: { items: 1 }
                    }
                });
            });
        }

        /* Wallet Currency Preview */
        $(document).on('change', '[data-wallet-currency-select]', function () {
            var $select = $(this);
            var currencyId = $select.val();
            var urlTemplate = $select.data('currency-info-url') || '';
            var loadingText = $select.data('loading-text') || 'Loading...';
            var $preview = $select.closest('form').find('[data-wallet-currency-preview]');

            if (!currencyId || !urlTemplate || !$preview.length) {
                return;
            }

            $preview.html(
                '<div class="d-flex align-items-center justify-content-center">' +
                    '<div class="spinner-border text-primary" role="status">' +
                        '<span class="visually-hidden">' + loadingText + '</span>' +
                    '</div>' +
                '</div>'
            );

            $.get(urlTemplate.replace('__currency_id__', currencyId), function (data) {
                $preview.html(data);
            });
        });

        /* Back to Top Click */
        $(document).on('click', '.back-to-top', function () {
            var mainPanel = document.querySelector('[data-dashboard-main-scroll]');
            if (mainPanel) {
                mainPanel.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                $('html, body').animate({ scrollTop: 0 }, 800);
            }
        });

        /* Dismiss verified KYC banner after the user has seen it once. */
        function hasDismissedVerifiedKycNotice(key) {
            try {
                return key && window.localStorage && window.localStorage.getItem(key) === '1';
            } catch (error) {
                return false;
            }
        }

        function rememberVerifiedKycNoticeDismissal(key) {
            try {
                if (key && window.localStorage) {
                    window.localStorage.setItem(key, '1');
                }
            } catch (error) {
                // Keep the visual dismiss working even when storage is blocked.
            }
        }

        $('[data-kyc-verified-notice]').each(function () {
            var notice = this;
            var key = notice.getAttribute('data-kyc-dismiss-key');

            if (hasDismissedVerifiedKycNotice(key)) {
                notice.remove();
                return;
            }

            notice.hidden = false;
        });

        $(document).on('click', '[data-kyc-notice-dismiss]', function () {
            var notice = this.closest('[data-kyc-verified-notice]');
            var key = notice ? notice.getAttribute('data-kyc-dismiss-key') : '';

            rememberVerifiedKycNoticeDismissal(key);

            if (notice) {
                notice.remove();
            }
        });

        /* User Dropdown Toggle */
        $('.navbar-area .header-right li .user').on('click', function (e) {
            e.stopPropagation();
            $(this).toggleClass('active');
        });

        /* Close User Dropdown on Outside Click */
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.navbar-area .header-right li .user').length) {
                $('.navbar-area .header-right li .user').removeClass('active');
            }
        });
    });

    /* On Load Events */
    $(window).on('load', function () {
        var $preloader = $('#preloader');
        $preloader.fadeOut(0);
        $('.back-to-top').fadeOut();

        $(document).on('click', '.cancel-preloader a', function (e) {
            e.preventDefault();
            $preloader.fadeOut(2000);
        });
    });

    /* Initialize Bootstrap Tooltip */
    var tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipElements.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    /* Initialize ClipboardJS */
    if (typeof ClipboardJS !== 'undefined') {
        var clipboard = new ClipboardJS('.copyNow');
        clipboard.on('success', function (e) {
            var button  = e.trigger;
            var tooltip = bootstrap.Tooltip.getInstance(button);
            if (tooltip) {
                button.setAttribute('data-bs-original-title', 'Copied!');
                tooltip.show();
                setTimeout(function () {
                    var originalTitle = tooltip._config.title || '';
                    button.setAttribute('data-bs-original-title', originalTitle);
                }, 2000);
            }
            e.clearSelection();
        });
    }

    /* -----------------------------------------------------------------
     * § Desktop Scroll Manager
     *   Facebook-style: outside scroll → main panel (smooth rAF lerp),
     *   sidebar menu region → sidebar menu only (native).
     *   Uses requestAnimationFrame for jank-free 60fps scroll routing.
     * ----------------------------------------------------------------- */

    var DESKTOP_MQ   = window.matchMedia('(min-width: 992px)');
    var LERP_FACTOR  = 0.16;
    var SNAP_EPSILON = 0.5;

    var _scroll = {
        mainEl:    null,
        sidebarEl: null,
        targetY:   0,
        currentY:  0,
        ticking:   false
    };

    function _cacheElements() {
        var root = document.querySelector('[data-dashboard-scroll-root]');
        if (!root) {
            _scroll.mainEl    = null;
            _scroll.sidebarEl = null;
            return;
        }
        _scroll.mainEl    = root.querySelector('[data-dashboard-main-scroll]');
        _scroll.sidebarEl = root.querySelector('[data-dashboard-sidebar-scroll]');

        if (_scroll.mainEl) {
            _scroll.currentY = _scroll.mainEl.scrollTop;
            _scroll.targetY  = _scroll.currentY;
        }
    }

    function _isDesktop() {
        return DESKTOP_MQ.matches;
    }

    function _hasOverlay(target) {
        return target && target.closest('.modal.show, .offcanvas.show, .dropdown-menu.show');
    }

    function _isInsideSidebar(target) {
        return target && target.closest('[data-dashboard-sidebar-scroll]');
    }

    function _isInsideMainPanel(target) {
        return target && target.closest('[data-dashboard-main-scroll]');
    }

    /* Find a scrollable ancestor between target and main panel */
    function _findNestedScroller(target) {
        if (!target) return null;
        var el = target;
        var mainEl = _scroll.mainEl;
        while (el && el !== mainEl && el !== document.body) {
            if (el.scrollHeight > el.clientHeight + 1) {
                var style = window.getComputedStyle(el);
                var ov = style.overflowY;
                if (ov === 'auto' || ov === 'scroll') {
                    return el;
                }
            }
            el = el.parentElement;
        }
        return null;
    }

    /* Smooth interpolation tick via rAF (60fps lerp like Facebook) */
    function _smoothTick() {
        var s = _scroll;
        if (!s.mainEl) {
            s.ticking = false;
            return;
        }

        var diff = s.targetY - s.currentY;

        if (Math.abs(diff) < SNAP_EPSILON) {
            s.currentY = s.targetY;
            s.mainEl.scrollTop = s.targetY;
            s.ticking = false;
            return;
        }

        s.currentY += diff * LERP_FACTOR;
        s.mainEl.scrollTop = Math.round(s.currentY);

        requestAnimationFrame(_smoothTick);
    }

    function _startSmooth() {
        if (!_scroll.ticking) {
            _scroll.ticking = true;
            requestAnimationFrame(_smoothTick);
        }
    }

    function _clampTarget() {
        var el = _scroll.mainEl;
        if (!el) return;
        var max = el.scrollHeight - el.clientHeight;
        if (_scroll.targetY < 0) _scroll.targetY = 0;
        if (_scroll.targetY > max) _scroll.targetY = max;
    }

    /* Main wheel handler */
    function _onWheel(e) {
        if (!_isDesktop()) return;
        if (_hasOverlay(e.target)) return;

        /* Horizontal scroll → ignore */
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;

        /* Sidebar menu region → native sidebar scroll */
        if (_isInsideSidebar(e.target)) return;

        /* Inside main panel → check for nested scrollers first */
        if (_isInsideMainPanel(e.target)) {
            var nested = _findNestedScroller(e.target);
            if (nested) {
                var atTop    = nested.scrollTop <= 0 && e.deltaY < 0;
                var atBottom = (nested.scrollTop >= (nested.scrollHeight - nested.clientHeight - 1)) && e.deltaY > 0;
                if (!atTop && !atBottom) return;
            }
            /* Route to smooth main scroll */
            e.preventDefault();
            _scroll.targetY += e.deltaY;
            _clampTarget();
            _startSmooth();
            return;
        }

        /* Outside both panels (header, wallet card, empty areas) → route to main */
        if (!e.target.closest('.dashboard-user-layout')) return;

        e.preventDefault();
        _scroll.targetY += e.deltaY;
        _clampTarget();
        _startSmooth();
    }

    /* Sync state when user scrolls main panel natively (touch, scrollbar drag) */
    function _onMainScroll() {
        if (!_scroll.mainEl) return;
        if (!_scroll.ticking) {
            _scroll.currentY = _scroll.mainEl.scrollTop;
            _scroll.targetY  = _scroll.currentY;
        }
    }

    /* Reset outer document scroll to 0 (prevent drift) */
    function _resetOuter() {
        if (_isDesktop()) {
            window.scrollTo(0, 0);
            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;
        }
    }

    /* Bootstrap the scroll system */
    function _initScrollManager() {
        _cacheElements();

        if (_scroll.mainEl) {
            _scroll.mainEl.addEventListener('scroll', _onMainScroll, { passive: true });
        }

        document.addEventListener('wheel', _onWheel, { passive: false });

        window.addEventListener('resize', function () {
            _cacheElements();
            _resetOuter();
        });

        _resetOuter();
    }

    /* Start on DOM ready */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _initScrollManager);
    } else {
        _initScrollManager();
    }

    window.addEventListener('load', _resetOuter);

})(jQuery);
