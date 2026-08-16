@php
    /**
     * Notice key — also whitelisted in AdminNoticeController::KNOWN_NOTICES.
     * Renaming this string anywhere requires updating the controller, the
     * model column data, and the test.
     */
    $sqNoticeKey = 'scheduler-and-queue-setup';
@endphp

{{--
    Anti-flash guard: this <script> runs SYNCHRONOUSLY before the
    <section> below is parsed by the browser, so the class lands on
    <html> before the banner gets any layout — and the CSS rule in
    @push('styles') hides it from the very first paint. Without this,
    the banner flashes for a few hundred ms on every page load until
    the bottom-of-page handler removes it from the DOM.
--}}
<script>
    (function () {
        try {
            if (window.localStorage && window.localStorage.getItem('digikash:notice-dismissed:scheduler-and-queue-setup') === '1') {
                document.documentElement.classList.add('sq-notice-dismissed');
            }
        } catch (e) {
            /* localStorage disabled — banner stays visible until the click handler hides it. */
        }
    })();
</script>

<section class="sq-notice"
         data-sq-notice
         data-dismiss-url="{{ route('admin.notice.dismiss', $sqNoticeKey) }}"
         role="region"
         aria-labelledby="sq-notice-title">

    {{-- ─── Header ─────────────────────────────────────────────── --}}
    <header class="sq-notice__head">
        <span class="sq-notice__head-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </span>

        <div class="sq-notice__head-text">
            <span class="sq-notice__tag">
                <span class="sq-notice__tag-dot" aria-hidden="true"></span>
                {{ __('Pre-flight check · complete to activate background work') }}
            </span>
            <h2 class="sq-notice__title" id="sq-notice-title">
                {{ __('Connect your server scheduler and queue worker') }}
            </h2>
            <p class="sq-notice__lead">
                {{ __('Subscriptions, P2P expirations, wallet-earn rewards, deposit confirmations, withdrawal payouts, and notifications all sit idle until your server runs both pieces below. This usually takes 2 minutes — one cron line and one worker process.') }}
            </p>
        </div>

        <button type="button"
                class="sq-notice__close"
                data-sq-dismiss
                aria-label="{{ __('Dismiss this notice permanently') }}"
                title="{{ __('Dismiss permanently') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </header>

    {{-- ─── 3 service cards ────────────────────────────────────── --}}
    <div class="sq-notice__grid">

        <article class="sq-notice__service sq-notice__service--scheduler">
            <header class="sq-notice__service-head">
                <span class="sq-notice__service-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2"  x2="16" y2="6"/>
                        <line x1="8"  y1="2"  x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                        <circle cx="8"  cy="15" r="1.2" fill="currentColor"/>
                        <circle cx="12" cy="15" r="1.2" fill="currentColor"/>
                        <circle cx="16" cy="15" r="1.2" fill="currentColor"/>
                    </svg>
                </span>
                <div>
                    <span class="sq-notice__service-step">{{ __('Step 1') }}</span>
                    <strong class="sq-notice__service-title">{{ __('Scheduler (cron)') }}</strong>
                </div>
                <span class="sq-notice__service-badge">{{ __('Required') }}</span>
            </header>
            <p class="sq-notice__service-text">
                {{ __('Adds one line to your crontab. Runs every minute and fires scheduled commands like subscription renewals, P2P expirations, and wallet-earn payouts.') }}
            </p>
        </article>

        <article class="sq-notice__service sq-notice__service--queue">
            <header class="sq-notice__service-head">
                <span class="sq-notice__service-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/>
                        <path d="M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                </span>
                <div>
                    <span class="sq-notice__service-step">{{ __('Step 2') }}</span>
                    <strong class="sq-notice__service-title">{{ __('Queue worker') }}</strong>
                </div>
                <span class="sq-notice__service-badge">{{ __('Required') }}</span>
            </header>
            <p class="sq-notice__service-text">
                {{ __('Long-running process that handles queued jobs — deposits, withdrawals, notifications, webhook deliveries. Run it under Supervisor in production so it auto-restarts.') }}
            </p>
        </article>

        <article class="sq-notice__service sq-notice__service--monitor">
            <header class="sq-notice__service-head">
                <span class="sq-notice__service-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </span>
                <div>
                    <span class="sq-notice__service-step">{{ __('Step 3') }}</span>
                    <strong class="sq-notice__service-title">{{ __('Monitor & retry') }}</strong>
                </div>
                <span class="sq-notice__service-badge sq-notice__service-badge--optional">{{ __('Built in') }}</span>
            </header>
            <p class="sq-notice__service-text">
                {{ __('Watch every scheduled run, inspect output, retry failed jobs and trigger commands manually from the Background Tasks panel — no SSH needed.') }}
            </p>
        </article>
    </div>

    {{-- ─── Footer actions ─────────────────────────────────────── --}}
    <footer class="sq-notice__actions">
        <a href="{{ route('admin.background-tasks.scheduler') }}" class="sq-notice__cta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14"/>
                <path d="M12 5l7 7-7 7"/>
            </svg>
            <span>{{ __('Open setup guide') }}</span>
        </a>
        <a href="{{ route('admin.background-tasks.index') }}" class="sq-notice__link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="3" y1="9" x2="21" y2="9"/>
                <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
            <span>{{ __('View tasks panel') }}</span>
        </a>
        <span class="sq-notice__hint">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8"  x2="12.01" y2="8"/>
            </svg>
            {{ __('You can dismiss this card permanently — but the underlying setup is still required for background work to run.') }}
        </span>
    </footer>
</section>

@push('styles')
    <style>
        /* ╔══════════════════════════════════════════════════════════════╗
           ║  Scheduler & Queue setup notice  (sq-notice)                 ║
           ╚══════════════════════════════════════════════════════════════╝ */

        /* Anti-flash: hide before first paint when localStorage marker
           is present (class is set by the inline pre-paint script at the
           top of this partial). */
        html.sq-notice-dismissed .sq-notice {
            display: none !important;
        }

        .sq-notice {
            position: relative;
            display: grid;
            gap: 18px;
            padding: 22px 24px 20px;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(var(--color-primary-rgb), 0.2);
            border-radius: 14px;
            background: #ffffff;
            overflow: hidden;
            transition: opacity 0.25s ease, max-height 0.32s ease, padding 0.25s ease, margin 0.25s ease;
        }

        .sq-notice.is-dismissing {
            opacity: 0;
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            margin-bottom: 0;
            border-width: 0;
        }

        /* ─── Header ──────────────────────────────────────────────── */
        .sq-notice__head {
            position: relative;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 18px;
            align-items: flex-start;
        }

        .sq-notice__head-icon {
            display: inline-grid;
            place-items: center;
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: rgba(var(--color-primary-rgb), 0.1);
            color: var(--color-primary, #4f46e5);
        }

        .sq-notice__head-icon svg {
            width: 24px;
            height: 24px;
        }

        .sq-notice__head-text {
            min-width: 0;
        }

        .sq-notice__tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 4px 12px 4px 9px;
            margin-bottom: 10px;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sq-notice__tag-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #f59e0b;
        }

        .sq-notice__title {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 1.18rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.012em;
        }

        .sq-notice__lead {
            margin: 0;
            color: #475569;
            font-size: 0.84rem;
            line-height: 1.6;
            max-width: 78ch;
        }

        .sq-notice__close {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
            transition: color 0.16s ease, border-color 0.16s ease, background-color 0.16s ease;
        }

        .sq-notice__close:hover,
        .sq-notice__close:focus {
            color: #0f172a;
            border-color: rgba(15, 23, 42, 0.18);
            background: #f8fafc;
        }

        .sq-notice__close svg {
            width: 14px;
            height: 14px;
        }

        /* ─── Service cards grid ──────────────────────────────────── */
        .sq-notice__grid {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .sq-notice__service {
            position: relative;
            display: grid;
            gap: 10px;
            padding: 14px 14px 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 10px;
            background: #fbfcfe;
            transition: border-color 0.16s ease, background-color 0.16s ease;
        }

        .sq-notice__service:hover {
            border-color: rgba(15, 23, 42, 0.16);
            background: #ffffff;
        }

        .sq-notice__service-head {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
        }

        .sq-notice__service-icon {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }

        .sq-notice__service--scheduler .sq-notice__service-icon {
            background: rgba(79, 70, 229, 0.1);
            color: #4338ca;
        }

        .sq-notice__service--queue .sq-notice__service-icon {
            background: rgba(139, 92, 246, 0.1);
            color: #7c3aed;
        }

        .sq-notice__service--monitor .sq-notice__service-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #047857;
        }

        .sq-notice__service-icon svg {
            width: 16px;
            height: 16px;
        }

        .sq-notice__service-step {
            display: block;
            font-size: 0.62rem;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .sq-notice__service-title {
            display: block;
            font-size: 0.86rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            letter-spacing: -0.005em;
        }

        .sq-notice__service-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .sq-notice__service-badge--optional {
            background: rgba(5, 150, 105, 0.1);
            color: #047857;
        }

        .sq-notice__service-text {
            margin: 0;
            color: #475569;
            font-size: 0.78rem;
            line-height: 1.55;
        }

        /* ─── Footer ──────────────────────────────────────────────── */
        .sq-notice__actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            padding-top: 4px;
        }

        .sq-notice__cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            background: var(--color-primary, #4f46e5);
            color: #ffffff;
            font-size: 0.83rem;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: -0.005em;
            transition: background-color 0.16s ease;
        }

        .sq-notice__cta:hover,
        .sq-notice__cta:focus {
            color: #ffffff;
            background: var(--color-primary-hover, #4338ca);
        }

        .sq-notice__cta svg {
            width: 15px;
            height: 15px;
        }

        .sq-notice__link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--color-primary, #4f46e5);
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.16s ease;
        }

        .sq-notice__link:hover,
        .sq-notice__link:focus {
            color: var(--color-primary-hover, #4338ca);
            text-decoration: underline;
        }

        .sq-notice__link svg {
            width: 14px;
            height: 14px;
        }

        .sq-notice__hint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            color: #94a3b8;
            font-size: 0.72rem;
            line-height: 1.4;
            max-width: 38ch;
        }

        .sq-notice__hint svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            color: #cbd5e1;
        }

        /* ─── Dark theme ──────────────────────────────────────────── */
        [data-coreui-theme="dark"] .sq-notice {
            background: #0f172a;
            border-color: rgba(var(--color-primary-rgb), 0.32);
        }

        [data-coreui-theme="dark"] .sq-notice__title { color: #f1f5f9; }
        [data-coreui-theme="dark"] .sq-notice__lead  { color: #cbd5e1; }
        [data-coreui-theme="dark"] .sq-notice__service {
            background: rgba(15, 23, 42, 0.55);
            border-color: rgba(248, 250, 252, 0.08);
        }
        [data-coreui-theme="dark"] .sq-notice__service-title { color: #f1f5f9; }
        [data-coreui-theme="dark"] .sq-notice__service-text  { color: #cbd5e1; }
        [data-coreui-theme="dark"] .sq-notice__close {
            background: rgba(15, 23, 42, 0.55);
            border-color: rgba(248, 250, 252, 0.12);
            color: #94a3b8;
        }
        [data-coreui-theme="dark"] .sq-notice__hint { color: #64748b; }

        /* ─── Responsive ──────────────────────────────────────────── */
        @media (max-width: 991.98px) {
            .sq-notice__grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .sq-notice {
                padding: 18px 18px 16px;
            }
            .sq-notice__head {
                grid-template-columns: auto minmax(0, 1fr);
                gap: 12px;
            }
            .sq-notice__head-icon {
                width: 42px;
                height: 42px;
                border-radius: 11px;
            }
            .sq-notice__head-icon svg {
                width: 20px;
                height: 20px;
            }
            .sq-notice__close {
                position: absolute;
                top: 14px;
                right: 14px;
            }
            .sq-notice__title {
                font-size: 1.05rem;
            }
            .sq-notice__hint {
                margin-left: 0;
                max-width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        'use strict';
        (function () {
            /**
             * Three layers of defence so the banner never reappears
             * after dismissal on this domain:
             *
             *   1. localStorage  — same browser, instant, survives even
             *      if the AJAX persist call fails (CSRF expired, network
             *      blip, edge cache, etc.).
             *   2. server-side `dismissed_notices` JSON column on the
             *      admin row — covers every other device / browser the
             *      same admin signs in from.
             *   3. blade `@hasDismissedNotice(...)` check on render —
             *      stops the partial from ever being emitted server-side
             *      once layer 2 succeeded.
             */
            var STORAGE_KEY = 'digikash:notice-dismissed:scheduler-and-queue-setup';

            var notice = document.querySelector('[data-sq-notice]');
            if (! notice) {
                return;
            }

            /* Layer 1 — if this browser already dismissed it, hide
               immediately on render. Protects against stale Cloudflare
               or browser cache that serves the dashboard HTML with the
               banner still embedded. The pre-paint script at the top
               of this partial already set the `sq-notice-dismissed`
               class on <html> so CSS removed it from the layout before
               first paint — here we just clean up the DOM node. */
            try {
                if (window.localStorage && window.localStorage.getItem(STORAGE_KEY) === '1') {
                    document.documentElement.classList.add('sq-notice-dismissed');
                    if (notice.parentNode) {
                        notice.parentNode.removeChild(notice);
                    }
                    return;
                }
            } catch (_) {
                /* localStorage disabled — fall through to AJAX path. */
            }

            var closeBtn = notice.querySelector('[data-sq-dismiss]');
            if (! closeBtn) {
                return;
            }
            var url   = notice.dataset.dismissUrl;
            var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            closeBtn.addEventListener('click', function () {
                closeBtn.disabled = true;

                /* Write the local marker FIRST so this browser will not
                   show the banner again even if the request below
                   never reaches the server. Also set the <html> class
                   immediately so the next page navigation has no
                   chance of flashing the banner before our pre-paint
                   script runs. */
                try {
                    if (window.localStorage) {
                        window.localStorage.setItem(STORAGE_KEY, '1');
                    }
                } catch (_) {
                    /* ignore — server-side persistence is the fallback. */
                }
                document.documentElement.classList.add('sq-notice-dismissed');

                notice.classList.add('is-dismissing');
                window.setTimeout(function () {
                    if (notice.parentNode) {
                        notice.parentNode.removeChild(notice);
                    }
                }, 380);

                if (! url) {
                    return;
                }
                fetch(url, {
                    method:      'POST',
                    credentials: 'same-origin',
                    cache:       'no-store',
                    headers:     {
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).then(function (response) {
                    if (! response.ok && window.console && window.console.warn) {
                        window.console.warn(
                            '[sq-notice] server-side dismissal failed (' + response.status + ') — local fallback active.'
                        );
                    }
                }).catch(function (err) {
                    if (window.console && window.console.warn) {
                        window.console.warn('[sq-notice] dismissal request failed — local fallback active.', err);
                    }
                });
            });
        })();
    </script>
@endpush
