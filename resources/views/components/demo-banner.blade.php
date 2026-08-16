@php
    if (! (bool) config('app.demo', false)) {
        return;
    }

    $disclosureUrl = url('/demo-disclosure');
@endphp

<div class="demo-banner" role="note" aria-label="{{ __('Demo site notice') }}" data-demo-banner>
    <div class="demo-banner__inner">
        <span class="demo-banner__badge" aria-hidden="true">{{ __('DEMO') }}</span>
        <span class="demo-banner__text">
            <strong class="demo-banner__title">{{ __('Software product demo.') }}</strong>
            <span class="demo-banner__detail">{{ __('No real financial services or transactions — all data is fictitious.') }}</span>
            <a class="demo-banner__link" href="{{ $disclosureUrl }}">{{ __('Learn more') }}</a>
        </span>
        <button
            type="button"
            class="demo-banner__close"
            data-demo-banner-close
            aria-label="{{ __('Dismiss demo notice') }}"
            title="{{ __('Dismiss') }}"
        >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</div>

<style>
    :root {
        --demo-banner-height: 30px;
        --demo-banner-safe-top: env(safe-area-inset-top, 0px);
        --demo-banner-total: calc(var(--demo-banner-height) + var(--demo-banner-safe-top));
    }

    @media (max-width: 575px) {
        :root {
            --demo-banner-height: 28px;
        }
    }

    html {
        scroll-padding-top: var(--demo-banner-total);
    }

    body {
        padding-top: var(--demo-banner-total) !important;
    }

    .demo-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 2147483646;
        width: 100%;
        height: var(--demo-banner-total);
        padding-top: var(--demo-banner-safe-top);
        box-sizing: border-box;
        background: linear-gradient(90deg, #0b1220 0%, #111827 50%, #0b1220 100%);
        color: #e5e7eb;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 12.5px;
        font-weight: 500;
        line-height: 1;
        border-bottom: 1px solid rgba(245, 158, 11, 0.4);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        letter-spacing: 0.01em;
        pointer-events: none;
    }

    .demo-banner__inner {
        max-width: 1400px;
        height: 100%;
        margin: 0 auto;
        padding: 0 44px 0 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-align: center;
        position: relative;
    }

    .demo-banner__badge {
        flex-shrink: 0;
        background: linear-gradient(135deg, #f59e0b, #f97316);
        color: #1a1300;
        font-weight: 800;
        font-size: 9.5px;
        letter-spacing: 0.14em;
        padding: 3px 8px;
        border-radius: 999px;
        text-transform: uppercase;
        line-height: 1;
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.35);
    }

    .demo-banner__text {
        color: inherit;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: calc(100% - 60px);
    }

    .demo-banner__title {
        color: #f8fafc;
        font-weight: 600;
    }

    .demo-banner__detail {
        color: #cbd5e1;
        margin-left: 5px;
        font-weight: 400;
    }

    .demo-banner__link {
        margin-left: 8px;
        color: #fbbf24;
        text-decoration: underline;
        text-underline-offset: 2px;
        text-decoration-thickness: 1px;
        font-weight: 600;
        pointer-events: auto;
    }

    .demo-banner__link:hover,
    .demo-banner__link:focus-visible {
        color: #fde68a;
        outline: none;
    }

    .demo-banner__close {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        padding: 0;
        margin: 0;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #cbd5e1;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        pointer-events: auto;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        font-family: inherit;
        line-height: 0;
    }

    .demo-banner__close:hover {
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(255, 255, 255, 0.22);
        color: #f8fafc;
    }

    .demo-banner__close:focus-visible {
        outline: 2px solid rgba(245, 158, 11, 0.6);
        outline-offset: 1px;
        color: #f8fafc;
    }

    .demo-banner__close svg {
        display: block;
    }

    /* === Dismissed state — banner hidden, all overrides reset === */
    .demo-banner-dismissed {
        --demo-banner-height: 0px !important;
        --demo-banner-safe-top: 0px !important;
        --demo-banner-total: 0px !important;
    }

    .demo-banner-dismissed body {
        padding-top: 0 !important;
    }

    .demo-banner-dismissed .demo-banner {
        display: none !important;
    }

    /* === Conflict resolution: push existing top-anchored elements below banner === */

    .header-top-section {
        top: var(--demo-banner-total) !important;
    }

    .header-1 {
        top: calc(60px + var(--demo-banner-total)) !important;
    }

    @media (max-width: 991px) {
        .header-1 {
            top: var(--demo-banner-total) !important;
        }
    }

    .sticky.header-1,
    .sticky {
        top: var(--demo-banner-total) !important;
    }

    .premium-header {
        top: var(--demo-banner-total) !important;
    }

    .sidebar.sidebar-fixed,
    .sidebar-fixed {
        top: var(--demo-banner-total) !important;
        height: calc(100vh - var(--demo-banner-total)) !important;
        min-height: calc(100vh - var(--demo-banner-total)) !important;
    }

    .header.header-sticky,
    .app-header {
        top: var(--demo-banner-total) !important;
    }

    /* Mobile dashboard fixed header (.dk-mobile-header — z-index: 1030) */
    .dk-mobile-header {
        top: var(--demo-banner-total) !important;
    }

    /* === Dismissed state restores original positions === */
    .demo-banner-dismissed .header-top-section,
    .demo-banner-dismissed .sticky,
    .demo-banner-dismissed .sticky.header-1,
    .demo-banner-dismissed .premium-header,
    .demo-banner-dismissed .header.header-sticky,
    .demo-banner-dismissed .app-header,
    .demo-banner-dismissed .dk-mobile-header {
        top: 0 !important;
    }

    .demo-banner-dismissed .header-1 {
        top: 60px !important;
    }

    @media (max-width: 991px) {
        .demo-banner-dismissed .header-1 {
            top: 0 !important;
        }
    }

    .demo-banner-dismissed .sidebar.sidebar-fixed,
    .demo-banner-dismissed .sidebar-fixed {
        top: 0 !important;
        height: 100vh !important;
        min-height: 100vh !important;
    }

    /* === Mobile responsive === */
    @media (max-width: 991px) {
        .demo-banner__inner {
            padding: 0 36px 0 12px;
            gap: 8px;
            justify-content: flex-start;
            text-align: left;
        }

        .demo-banner__detail {
            display: none;
        }
    }

    @media (max-width: 575px) {
        .demo-banner {
            font-size: 11.5px;
        }

        .demo-banner__badge {
            font-size: 9px;
            padding: 2px 7px;
        }

        .demo-banner__inner {
            padding: 0 32px 0 10px;
            gap: 7px;
        }

        .demo-banner__text {
            max-width: calc(100% - 56px);
        }

        .demo-banner__close {
            right: 8px;
            width: 18px;
            height: 18px;
        }

        .demo-banner__close svg {
            width: 10px;
            height: 10px;
        }
    }

    @media print {
        body {
            padding-top: 0 !important;
        }

        .demo-banner {
            display: none !important;
        }
    }
</style>

<script>
    (function () {
        'use strict';

        var STORAGE_KEY = 'dk_demo_banner_dismissed';
        var DISMISS_CLASS = 'demo-banner-dismissed';

        function readDismissed() {
            try {
                return window.localStorage && window.localStorage.getItem(STORAGE_KEY) === '1';
            } catch (e) {
                return false;
            }
        }

        function writeDismissed() {
            try {
                window.localStorage.setItem(STORAGE_KEY, '1');
            } catch (e) {
                /* localStorage unavailable — silently ignore */
            }
        }

        function applyDismissed() {
            var root = document.documentElement;
            if (root && !root.classList.contains(DISMISS_CLASS)) {
                root.classList.add(DISMISS_CLASS);
            }
        }

        if (readDismissed()) {
            applyDismissed();
            return;
        }

        function bindClose() {
            var button = document.querySelector('[data-demo-banner-close]');
            if (!button || button.dataset.demoBannerBound === '1') {
                return;
            }
            button.dataset.demoBannerBound = '1';
            button.addEventListener('click', function (event) {
                event.preventDefault();
                writeDismissed();
                applyDismissed();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindClose);
        } else {
            bindClose();
        }
    })();
</script>
