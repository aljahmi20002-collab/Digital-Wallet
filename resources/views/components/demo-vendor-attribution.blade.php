@php
    if (! (bool) config('app.demo', false)) {
        return;
    }

    $vendorName  = (string) config('app.demo_vendor_name', 'Coevs');
    $vendorUrl   = (string) config('app.demo_vendor_url', '');
    $productName = (string) config('app.demo_product_name', 'DigiKash');
    $salesUrl    = (string) config('app.demo_sales_url', '');
    $disclosureUrl = url('/demo-disclosure');

    /** @var array{label:string,url:string|null}|null $vendorLink */
    $vendorLink = $vendorUrl !== '' ? ['label' => $vendorName, 'url' => $vendorUrl] : null;
    /** @var array{label:string,url:string|null}|null $salesLink */
    $salesLink  = $salesUrl !== '' ? ['label' => __('View product'), 'url' => $salesUrl] : null;

    $variant = $variant ?? 'public';
@endphp

<div @class([
    'demo-vendor-attribution',
    'demo-vendor-attribution--public' => $variant === 'public',
    'demo-vendor-attribution--admin'  => $variant === 'admin',
]) data-demo-vendor-attribution role="contentinfo" aria-label="{{ __('Software vendor demo attribution') }}">
    <div class="demo-vendor-attribution__inner">
        <span class="demo-vendor-attribution__badge" aria-hidden="true">{{ __('DEMO INSTALLATION') }}</span>

        <p class="demo-vendor-attribution__text">
            {!! __('This site is a public demo of the :product software product, operated by the software vendor :vendor for evaluation purposes only. No real financial services, deposits, withdrawals, or cryptocurrency investments are offered here. All data displayed is fictitious.', [
                'product' => '<strong>'.e($productName).'</strong>',
                'vendor'  => $vendorLink
                    ? '<a href="'.e($vendorLink['url']).'" target="_blank" rel="noopener noreferrer">'.e($vendorLink['label']).'</a>'
                    : '<strong>'.e($vendorName).'</strong>',
            ]) !!}
        </p>

        <ul class="demo-vendor-attribution__links">
            <li>
                <a href="{{ $disclosureUrl }}">{{ __('Full demo disclosure') }}</a>
            </li>
            @if($salesLink)
                <li>
                    <a href="{{ $salesLink['url'] }}" target="_blank" rel="noopener noreferrer">{{ $salesLink['label'] }}</a>
                </li>
            @endif
        </ul>

        <button type="button"
                class="demo-vendor-attribution__close"
                data-demo-vendor-attribution-close
                aria-label="{{ __('Hide this notice on this device') }}"
                title="{{ __('Hide on this device') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
</div>

<style>
    .demo-vendor-attribution {
        margin-top: 12px;
        padding: 14px 18px;
        border-radius: 10px;
        background: rgba(15, 23, 42, 0.55);
        border: 1px solid rgba(245, 158, 11, 0.28);
        color: #e5e7eb;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 12.5px;
        line-height: 1.55;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.04) inset;
    }

    .demo-vendor-attribution--admin {
        margin: 8px 0 0;
        padding: 10px 14px;
        font-size: 11.5px;
        background: rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.35);
        color: #334155;
    }

    .demo-vendor-attribution__inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 8px 14px;
        text-align: center;
    }

    .demo-vendor-attribution--admin .demo-vendor-attribution__inner {
        justify-content: flex-start;
        text-align: left;
    }

    .demo-vendor-attribution__badge {
        flex-shrink: 0;
        background: linear-gradient(135deg, #f59e0b, #f97316);
        color: #1a1300;
        font-weight: 800;
        font-size: 10px;
        letter-spacing: 0.14em;
        padding: 3px 9px;
        border-radius: 999px;
        text-transform: uppercase;
        line-height: 1.4;
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.35);
    }

    .demo-vendor-attribution__text {
        margin: 0;
        max-width: 720px;
        color: inherit;
    }

    .demo-vendor-attribution__text strong {
        color: #f8fafc;
        font-weight: 600;
    }

    .demo-vendor-attribution--admin .demo-vendor-attribution__text strong {
        color: #0f172a;
    }

    .demo-vendor-attribution__text a {
        color: #fbbf24;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .demo-vendor-attribution--admin .demo-vendor-attribution__text a {
        color: #b45309;
    }

    .demo-vendor-attribution__text a:hover {
        color: #fde68a;
    }

    .demo-vendor-attribution__links {
        list-style: none;
        margin: 0;
        padding: 0;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px 14px;
    }

    .demo-vendor-attribution__links li {
        list-style: none;
    }

    .demo-vendor-attribution__links a {
        color: #fbbf24;
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
        padding: 4px 10px;
        border: 1px solid rgba(245, 158, 11, 0.35);
        border-radius: 999px;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .demo-vendor-attribution--admin .demo-vendor-attribution__links a {
        color: #b45309;
        border-color: rgba(180, 83, 9, 0.35);
        font-size: 11px;
        padding: 3px 9px;
    }

    .demo-vendor-attribution__links a:hover {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.55);
        color: #fde68a;
    }

    .demo-vendor-attribution--admin .demo-vendor-attribution__links a:hover {
        background: rgba(180, 83, 9, 0.08);
        color: #92400e;
    }

    .demo-vendor-attribution__close {
        flex-shrink: 0;
        display: inline-grid;
        place-items: center;
        width: 26px;
        height: 26px;
        margin-left: 4px;
        padding: 0;
        border: 1px solid rgba(245, 158, 11, 0.35);
        border-radius: 999px;
        background: transparent;
        color: #fbbf24;
        cursor: pointer;
        transition: color 0.15s ease, border-color 0.15s ease, background-color 0.15s ease;
    }

    .demo-vendor-attribution__close:hover,
    .demo-vendor-attribution__close:focus {
        color: #fde68a;
        border-color: rgba(245, 158, 11, 0.7);
        background: rgba(245, 158, 11, 0.12);
        outline: none;
    }

    .demo-vendor-attribution__close svg {
        width: 12px;
        height: 12px;
    }

    .demo-vendor-attribution--admin .demo-vendor-attribution__close {
        color: #b45309;
        border-color: rgba(180, 83, 9, 0.35);
    }

    .demo-vendor-attribution--admin .demo-vendor-attribution__close:hover,
    .demo-vendor-attribution--admin .demo-vendor-attribution__close:focus {
        color: #92400e;
        border-color: rgba(180, 83, 9, 0.55);
        background: rgba(180, 83, 9, 0.08);
    }

    @media (max-width: 575px) {
        .demo-vendor-attribution {
            padding: 12px 14px;
            font-size: 12px;
            text-align: center;
        }

        .demo-vendor-attribution__inner {
            justify-content: center;
            text-align: center;
        }

        .demo-vendor-attribution__links {
            justify-content: center;
        }
    }

    @media print {
        .demo-vendor-attribution {
            display: none !important;
        }
    }
</style>

<script>
    'use strict';
    (function () {
        /**
         * Device-only dismissal — localStorage *only*, no server call.
         * The user explicitly wants this banner to:
         *   • disappear on the device where it was closed
         *   • come back when signing in from any other device / browser
         * so we deliberately do NOT persist to the database.
         *
         * Each variant (public / admin) gets its own storage key so an
         * admin who hides it in the dashboard still sees it on the
         * public site, and vice-versa.
         */
        var nodes = document.querySelectorAll('[data-demo-vendor-attribution]');
        if (! nodes.length) {
            return;
        }

        nodes.forEach(function (node) {
            var variant   = node.classList.contains('demo-vendor-attribution--admin') ? 'admin' : 'public';
            var storageKey = 'digikash:demo-vendor-attribution-dismissed:' + variant;

            try {
                if (window.localStorage && window.localStorage.getItem(storageKey) === '1') {
                    if (node.parentNode) {
                        node.parentNode.removeChild(node);
                    }
                    return;
                }
            } catch (_) {
                /* localStorage disabled — leave banner visible. */
            }

            var closeBtn = node.querySelector('[data-demo-vendor-attribution-close]');
            if (! closeBtn) {
                return;
            }

            closeBtn.addEventListener('click', function () {
                try {
                    if (window.localStorage) {
                        window.localStorage.setItem(storageKey, '1');
                    }
                } catch (_) {
                    /* ignore — best-effort dismissal only. */
                }
                if (node.parentNode) {
                    node.parentNode.removeChild(node);
                }
            });
        });
    })();
</script>
