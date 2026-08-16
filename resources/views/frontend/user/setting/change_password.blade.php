@extends('frontend.user.setting.index')
@section('title', __('Change Password'))

@section('user_setting_content')
    @include('frontend.user.setting.partials._security_tabs')

    <div class="row g-3">
        <div class="col-12">
            <form id="settings-password-form" action="{{ route('user.settings.password.update') }}" method="POST">
                @csrf

                <section class="settings-section credential-password-card">
                    <header class="settings-section__header">
                        <div>
                            <h6 class="settings-section__title">{{ __('Update Login Password') }}</h6>
                            <p class="settings-section__subtitle">
                                {{ __('Rotate your password regularly to keep account access secure.') }}
                            </p>
                        </div>
                    </header>

                    <div class="settings-section__body">
                        <div class="mb-3">
                            <label for="old-password" class="form-label">{{ __('Current Password') }}</label>
                            <input type="password" id="old-password" name="old_password"
                                   class="form-control" placeholder="{{ __('Enter current password') }}" required
                                   autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('New Password') }}</label>
                            <input type="password" id="password" name="password"
                                   class="form-control" placeholder="{{ __('Enter new password') }}" required
                                   autocomplete="new-password"
                                   data-password-insight-input>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" placeholder="{{ __('Repeat new password') }}" required
                                   autocomplete="new-password"
                                   data-password-insight-confirm>
                        </div>
                    </div>
                </section>

                <footer class="settings-actions">
                    <p class="settings-actions__hint">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        {{ __('You will remain signed in on this device after updating your password.') }}
                    </p>
                    <button type="submit" class="btn btn-primary settings-actions__submit">
                        <i class="fas fa-check" aria-hidden="true"></i>
                        <span>{{ __('Update Password') }}</span>
                    </button>
                </footer>
            </form>
        </div>

        <div class="col-12">
            <aside class="password-insight" data-password-insight aria-live="polite">
                <header class="password-insight__header">
                    <span class="password-insight__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M12 2v20"/>
                        </svg>
                    </span>
                    <div class="password-insight__heading">
                        <h6 class="password-insight__title">{{ __('Password Strength & Best Practices') }}</h6>
                        <p class="password-insight__subtitle">{{ __('Live feedback as you type — aim for green across every check.') }}</p>
                    </div>
                    <span class="password-insight__score-pill" data-password-insight-pill>
                        <span class="password-insight__score-pill-dot" aria-hidden="true"></span>
                        <span data-password-insight-pill-label>{{ __('Idle') }}</span>
                    </span>
                </header>

                <div class="password-insight__meter">
                    <div class="password-insight__meter-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-password-insight-track>
                        <span class="password-insight__meter-fill" data-password-insight-fill></span>
                    </div>
                    <div class="password-insight__meter-meta">
                        <span class="password-insight__meter-label" data-password-insight-label>
                            {{ __('Type a password to see how strong it is.') }}
                        </span>
                        <span class="password-insight__meter-match" data-password-insight-match hidden>
                            <svg class="password-insight__meter-match-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-password-insight-match-icon aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                            <span data-password-insight-match-label>{{ __('Confirmation matches') }}</span>
                        </span>
                    </div>
                </div>

                <div class="password-insight__columns">
                    <section class="password-insight__panel">
                        <h6 class="password-insight__panel-title">{{ __('Strength Checks') }}</h6>
                        <ul class="password-insight__criteria">
                            <li class="password-insight__criterion" data-password-insight-criterion="length-min">
                                <span class="password-insight__criterion-icon" aria-hidden="true"></span>
                                <span class="password-insight__criterion-text">{{ __('At least 6 characters') }}</span>
                                <span class="password-insight__criterion-tag">{{ __('Required') }}</span>
                            </li>
                            <li class="password-insight__criterion" data-password-insight-criterion="length-rec">
                                <span class="password-insight__criterion-icon" aria-hidden="true"></span>
                                <span class="password-insight__criterion-text">{{ __('12 or more characters') }}</span>
                                <span class="password-insight__criterion-tag password-insight__criterion-tag--soft">{{ __('Bonus') }}</span>
                            </li>
                            <li class="password-insight__criterion" data-password-insight-criterion="uppercase">
                                <span class="password-insight__criterion-icon" aria-hidden="true"></span>
                                <span class="password-insight__criterion-text">{{ __('Uppercase (A–Z)') }}</span>
                            </li>
                            <li class="password-insight__criterion" data-password-insight-criterion="lowercase">
                                <span class="password-insight__criterion-icon" aria-hidden="true"></span>
                                <span class="password-insight__criterion-text">{{ __('Lowercase (a–z)') }}</span>
                            </li>
                            <li class="password-insight__criterion" data-password-insight-criterion="number">
                                <span class="password-insight__criterion-icon" aria-hidden="true"></span>
                                <span class="password-insight__criterion-text">{{ __('Number (0–9)') }}</span>
                            </li>
                            <li class="password-insight__criterion" data-password-insight-criterion="symbol">
                                <span class="password-insight__criterion-icon" aria-hidden="true"></span>
                                <span class="password-insight__criterion-text">{{ __('Symbol (!@#$…)') }}</span>
                            </li>
                        </ul>
                    </section>

                    <section class="password-insight__panel">
                        <h6 class="password-insight__panel-title">{{ __('Good Habits') }}</h6>
                        <ul class="password-insight__habits">
                            <li>
                                <span class="password-insight__habit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 4 23 10 17 10"/>
                                        <polyline points="1 20 1 14 7 14"/>
                                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/>
                                        <path d="M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                                    </svg>
                                </span>
                                <span>{{ __('Avoid reusing passwords from other sites.') }}</span>
                            </li>
                            <li>
                                <span class="password-insight__habit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </span>
                                <span>{{ __('Never share — not even with support.') }}</span>
                            </li>
                            <li>
                                <span class="password-insight__habit-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                        <line x1="12" y1="18" x2="12.01" y2="18"/>
                                    </svg>
                                </span>
                                <span>{{ __('Enable 2FA for an extra protection layer.') }}</span>
                            </li>
                        </ul>
                    </section>
                </div>
            </aside>
        </div>
    </div>

    @push('styles')
        <style>
            /* ╔══════════════════════════════════════════════════════════════╗
               ║  Password Insight — live strength + best-practice card     ║
               ╚══════════════════════════════════════════════════════════════╝ */
            .password-insight {
                position: relative;
                display: grid;
                gap: 12px;
                padding: 14px 16px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 12px;
                background: #ffffff;
                box-shadow: 0 1px 0 rgba(15, 23, 42, 0.02);
            }

            .password-insight__header {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) auto;
                gap: 12px;
                align-items: center;
            }

            .password-insight__icon {
                display: inline-grid;
                place-items: center;
                width: 32px;
                height: 32px;
                border-radius: 9px;
                background: rgba(79, 70, 229, 0.1);
                color: #4338ca;
            }

            .password-insight__icon svg {
                width: 16px;
                height: 16px;
            }

            .password-insight__heading {
                min-width: 0;
            }

            .password-insight__title {
                margin: 0;
                color: #0f172a;
                font-size: 0.88rem;
                font-weight: 800;
                letter-spacing: -0.005em;
                line-height: 1.25;
            }

            .password-insight__subtitle {
                margin: 2px 0 0;
                color: #64748b;
                font-size: 0.72rem;
                line-height: 1.4;
            }

            .password-insight__score-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 3px 10px;
                border-radius: 999px;
                background: rgba(148, 163, 184, 0.14);
                color: #475569;
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                transition: background-color 0.18s ease, color 0.18s ease;
            }

            .password-insight__score-pill-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: currentColor;
            }

            .password-insight__score-pill[data-tier="critical"] {
                background: rgba(220, 38, 38, 0.12);
                color: #b91c1c;
            }

            .password-insight__score-pill[data-tier="weak"] {
                background: rgba(239, 68, 68, 0.12);
                color: #dc2626;
            }

            .password-insight__score-pill[data-tier="fair"] {
                background: rgba(245, 158, 11, 0.16);
                color: #b45309;
            }

            .password-insight__score-pill[data-tier="good"] {
                background: rgba(59, 130, 246, 0.14);
                color: #1d4ed8;
            }

            .password-insight__score-pill[data-tier="strong"],
            .password-insight__score-pill[data-tier="excellent"] {
                background: rgba(16, 185, 129, 0.14);
                color: #047857;
            }

            /* ─── Meter ───────────────────────────────────────────────── */
            .password-insight__meter-track {
                position: relative;
                height: 6px;
                border-radius: 999px;
                background: #eef2f7;
                overflow: hidden;
            }

            .password-insight__meter-fill {
                display: block;
                width: 0;
                height: 100%;
                border-radius: 999px;
                background: #94a3b8;
                transition: width 0.28s ease, background 0.28s ease;
            }

            .password-insight__meter-fill[data-tier="critical"] { background: linear-gradient(90deg, #b91c1c, #dc2626); }
            .password-insight__meter-fill[data-tier="weak"]     { background: linear-gradient(90deg, #ef4444, #f97316); }
            .password-insight__meter-fill[data-tier="fair"]     { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
            .password-insight__meter-fill[data-tier="good"]     { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
            .password-insight__meter-fill[data-tier="strong"]   { background: linear-gradient(90deg, #10b981, #34d399); }
            .password-insight__meter-fill[data-tier="excellent"]{ background: linear-gradient(90deg, #047857, #10b981); }

            .password-insight__meter-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 6px;
                gap: 10px;
                flex-wrap: wrap;
            }

            .password-insight__meter-label {
                color: #64748b;
                font-size: 0.72rem;
                font-weight: 600;
            }

            .password-insight__meter-match {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                color: #047857;
                font-size: 0.7rem;
                font-weight: 700;
            }

            .password-insight__meter-match[hidden] {
                display: none;
            }

            .password-insight__meter-match[data-state="mismatch"] {
                color: #b91c1c;
            }

            .password-insight__meter-match-icon {
                width: 13px;
                height: 13px;
            }

            /* ─── Two-column criteria + habits ────────────────────────── */
            .password-insight__columns {
                display: grid;
                grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
                gap: 14px;
            }

            .password-insight__panel-title {
                margin: 0 0 7px;
                color: #94a3b8;
                font-size: 0.6rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            .password-insight__criteria {
                list-style: none;
                margin: 0;
                padding: 0;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 5px 8px;
            }

            .password-insight__criterion {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) auto;
                align-items: center;
                gap: 7px;
                padding: 5px 9px;
                border: 1px solid #eef2f7;
                border-radius: 7px;
                background: #f8fafc;
                color: #64748b;
                font-size: 0.73rem;
                line-height: 1.25;
                transition: color 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
            }

            .password-insight__criterion-icon {
                display: inline-grid;
                place-items: center;
                width: 14px;
                height: 14px;
                border: 1.5px solid #cbd5e1;
                border-radius: 50%;
                color: transparent;
                font-size: 8px;
                font-weight: 900;
                transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
            }

            .password-insight__criterion-icon::before {
                content: '✓';
                line-height: 1;
            }

            .password-insight__criterion-text {
                color: inherit;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .password-insight__criterion-tag {
                padding: 1px 6px;
                border-radius: 999px;
                background: rgba(245, 158, 11, 0.14);
                color: #b45309;
                font-size: 0.55rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }

            .password-insight__criterion-tag--soft {
                background: rgba(148, 163, 184, 0.14);
                color: #475569;
            }

            .password-insight__criterion.is-met {
                color: #047857;
                background: rgba(16, 185, 129, 0.07);
                border-color: rgba(16, 185, 129, 0.22);
            }

            .password-insight__criterion.is-met .password-insight__criterion-icon {
                background: #10b981;
                border-color: #10b981;
                color: #ffffff;
            }

            .password-insight__criterion.is-met .password-insight__criterion-tag {
                background: rgba(16, 185, 129, 0.14);
                color: #047857;
            }

            /* ─── Good habits panel ───────────────────────────────────── */
            .password-insight__habits {
                list-style: none;
                margin: 0;
                padding: 0;
                display: grid;
                gap: 4px;
            }

            .password-insight__habits li {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr);
                gap: 8px;
                align-items: center;
                padding: 6px 9px;
                border-radius: 7px;
                background: #f8fafc;
                color: #475569;
                font-size: 0.72rem;
                line-height: 1.4;
            }

            .password-insight__habit-icon {
                display: inline-grid;
                place-items: center;
                width: 22px;
                height: 22px;
                border-radius: 6px;
                background: rgba(79, 70, 229, 0.1);
                color: #4338ca;
                flex-shrink: 0;
            }

            .password-insight__habit-icon svg {
                width: 12px;
                height: 12px;
            }

            /* ─── Dark theme ──────────────────────────────────────────── */
            [data-coreui-theme="dark"] .password-insight {
                background: #0f172a;
                border-color: rgba(248, 250, 252, 0.08);
            }

            [data-coreui-theme="dark"] .password-insight__title { color: #f1f5f9; }
            [data-coreui-theme="dark"] .password-insight__subtitle { color: #94a3b8; }
            [data-coreui-theme="dark"] .password-insight__meter-track { background: rgba(248, 250, 252, 0.08); }
            [data-coreui-theme="dark"] .password-insight__criterion,
            [data-coreui-theme="dark"] .password-insight__habits li {
                background: rgba(15, 23, 42, 0.55);
                border-color: rgba(248, 250, 252, 0.06);
                color: #cbd5e1;
            }

            /* ─── Responsive ──────────────────────────────────────────── */
            @media (max-width: 767.98px) {
                .password-insight {
                    padding: 16px 14px;
                }
                .password-insight__header {
                    grid-template-columns: auto minmax(0, 1fr);
                }
                .password-insight__score-pill {
                    grid-column: 1 / -1;
                    justify-self: start;
                }
                .password-insight__columns {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            'use strict';
            (function () {
                /**
                 * Password Insight — live strength meter + best-practice
                 * checklist. Pure visual feedback; server-side validation
                 * (UpdateUserPasswordRequest) remains the source of truth.
                 *
                 * Scoring rubric (max 6 points):
                 *   • length ≥ 6   (required by validator)
                 *   • length ≥ 12  (recommended bonus)
                 *   • contains uppercase
                 *   • contains lowercase
                 *   • contains digit
                 *   • contains symbol
                 *
                 * Tiers map score → coloured fill + label.
                 */
                var card        = document.querySelector('[data-password-insight]');
                var passwordEl  = document.querySelector('[data-password-insight-input]');
                if (! card || ! passwordEl) {
                    return;
                }

                var confirmEl = document.querySelector('[data-password-insight-confirm]');
                var fillEl    = card.querySelector('[data-password-insight-fill]');
                var trackEl   = card.querySelector('[data-password-insight-track]');
                var labelEl   = card.querySelector('[data-password-insight-label]');
                var pillEl    = card.querySelector('[data-password-insight-pill]');
                var pillTxtEl = card.querySelector('[data-password-insight-pill-label]');
                var matchEl   = card.querySelector('[data-password-insight-match]');
                var matchTxt  = matchEl ? matchEl.querySelector('[data-password-insight-match-label]') : null;
                var matchIcon = matchEl ? matchEl.querySelector('[data-password-insight-match-icon]') : null;

                // Pre-built SVG paths so we can swap match / mismatch
                // without re-rendering the whole element.
                var MATCH_SVG    = '<circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/>';
                var MISMATCH_SVG = '<circle cx="12" cy="12" r="10"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>';

                var TIERS = {
                    idle:      { label: '{{ __('Idle') }}',          hint: '{{ __('Type a password to see how strong it is.') }}',  width: 0 },
                    critical:  { label: '{{ __('Too short') }}',     hint: '{{ __('Add a few more characters — minimum 6 required.') }}', width: 18 },
                    weak:      { label: '{{ __('Weak') }}',          hint: '{{ __('Add an uppercase letter, a number, or a symbol.') }}', width: 36 },
                    fair:      { label: '{{ __('Fair') }}',          hint: '{{ __('Looking better — mix in another character type.') }}', width: 55 },
                    good:      { label: '{{ __('Good') }}',          hint: '{{ __('Strong enough — 12+ characters makes it even safer.') }}', width: 72 },
                    strong:    { label: '{{ __('Strong') }}',        hint: '{{ __('Strong password — your account is well protected.') }}', width: 88 },
                    excellent: { label: '{{ __('Excellent') }}',     hint: '{{ __('Excellent — long + varied. Top-tier protection.') }}',  width: 100 }
                };

                var CRITERIA = [
                    { key: 'length-min', test: function (v) { return v.length >= 6; } },
                    { key: 'length-rec', test: function (v) { return v.length >= 12; } },
                    { key: 'uppercase',  test: function (v) { return /[A-Z]/.test(v); } },
                    { key: 'lowercase',  test: function (v) { return /[a-z]/.test(v); } },
                    { key: 'number',     test: function (v) { return /[0-9]/.test(v); } },
                    { key: 'symbol',     test: function (v) { return /[^A-Za-z0-9]/.test(v); } }
                ];

                function evaluate(value) {
                    if (! value) {
                        return { tier: 'idle', score: 0, bonus: 0 };
                    }
                    if (value.length < 6) {
                        return { tier: 'critical', score: 1, bonus: 0 };
                    }

                    var bonus = 0;
                    if (value.length >= 12) bonus++;
                    if (/[A-Z]/.test(value)) bonus++;
                    if (/[a-z]/.test(value)) bonus++;
                    if (/[0-9]/.test(value)) bonus++;
                    if (/[^A-Za-z0-9]/.test(value)) bonus++;

                    if (bonus <= 1) return { tier: 'weak',      bonus: bonus };
                    if (bonus === 2) return { tier: 'fair',     bonus: bonus };
                    if (bonus === 3) return { tier: 'good',     bonus: bonus };
                    if (bonus === 4) return { tier: 'strong',   bonus: bonus };
                    return { tier: 'excellent', bonus: bonus };
                }

                function applyState(value) {
                    var result = evaluate(value);
                    var tier   = TIERS[result.tier];

                    fillEl.style.width  = tier.width + '%';
                    fillEl.setAttribute('data-tier', result.tier);
                    trackEl.setAttribute('aria-valuenow', String(tier.width));
                    labelEl.textContent = tier.hint;
                    pillEl.setAttribute('data-tier', result.tier);
                    pillTxtEl.textContent = tier.label;

                    CRITERIA.forEach(function (c) {
                        var node = card.querySelector('[data-password-insight-criterion="' + c.key + '"]');
                        if (! node) {
                            return;
                        }
                        node.classList.toggle('is-met', c.test(value));
                    });
                }

                function applyMatchState() {
                    if (! matchEl || ! confirmEl) {
                        return;
                    }
                    var pwd = passwordEl.value;
                    var cfm = confirmEl.value;

                    if (! pwd || ! cfm) {
                        matchEl.hidden = true;
                        return;
                    }

                    matchEl.hidden = false;
                    if (pwd === cfm) {
                        matchEl.setAttribute('data-state', 'match');
                        if (matchIcon) {
                            matchIcon.innerHTML = MATCH_SVG;
                        }
                        if (matchTxt) {
                            matchTxt.textContent = '{{ __('Confirmation matches') }}';
                        }
                    } else {
                        matchEl.setAttribute('data-state', 'mismatch');
                        if (matchIcon) {
                            matchIcon.innerHTML = MISMATCH_SVG;
                        }
                        if (matchTxt) {
                            matchTxt.textContent = '{{ __('Confirmation does not match yet') }}';
                        }
                    }
                }

                passwordEl.addEventListener('input', function () {
                    applyState(passwordEl.value);
                    applyMatchState();
                });

                if (confirmEl) {
                    confirmEl.addEventListener('input', applyMatchState);
                }

                applyState('');
                applyMatchState();
            })();
        </script>
    @endpush
@endsection
