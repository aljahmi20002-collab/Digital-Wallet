<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Install DigitalWallet') }}</title>
    <link rel="icon" href="{{ asset('general/static/logo/digitalwallet-mark.svg') }}">
    <style>
        :root {
            --ink: #14213d;
            --muted: #667085;
            --line: #e2e8f0;
            --panel: #ffffff;
            --page: #f8fafc;
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-rgb: 37, 99, 235;
            --side: #111827;
            --side-deep: #172554;
            --side-soft: #1f2937;
            --accent: #f59e0b;
            --accent-soft: #fff7e6;
            --success: #16a37a;
            --success-soft: #e8f7f1;
            --danger: #bd2d2d;
            --danger-soft: #fff1f1;
            --warning: #a36300;
            --warning-soft: #fff7e6;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            background: var(--page);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        button,
        input,
        select {
            font: inherit;
        }

        .installer-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
        }

        .installer-aside {
            min-height: 100vh;
            padding: 24px;
            color: #ffffff;
            background:
                linear-gradient(180deg, var(--side) 0%, var(--side-deep) 100%);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 16px;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 8px 18px rgba(var(--primary-rgb), .28);
        }

        .brand-mark svg {
            width: 23px;
            height: 23px;
            display: block;
        }

        .aside-copy {
            display: grid;
            gap: 12px;
        }

        .aside-copy h1 {
            margin: 0;
            font-size: 26px;
            line-height: 1.08;
            letter-spacing: 0;
        }

        .aside-copy p {
            margin: 0;
            color: #d8e8ef;
            line-height: 1.55;
            font-size: 13px;
        }

        .progress-shell {
            display: grid;
            gap: 10px;
        }

        .progress-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #d8e8ef;
            font-size: 12px;
            font-weight: 700;
        }

        .progress-track {
            height: 8px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .18);
            overflow: hidden;
        }

        .progress-fill {
            width: 17%;
            height: 100%;
            border-radius: 8px;
            background: var(--accent);
            transition: width .22s ease;
        }

        .step-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .step-button {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 8px;
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr);
            align-items: center;
            gap: 9px;
            padding: 8px;
            color: #d8e8ef;
            background: rgba(255, 255, 255, .06);
            text-align: left;
            cursor: pointer;
        }

        .step-button[aria-current="step"] {
            color: #ffffff;
            border-color: rgba(245, 158, 11, .72);
            background: rgba(245, 158, 11, .12);
        }

        .step-button:disabled {
            cursor: not-allowed;
            opacity: .72;
        }

        .step-number {
            width: 30px;
            height: 30px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
            color: var(--side);
            background: var(--accent);
            font-weight: 900;
        }

        .step-label strong {
            display: block;
            font-size: 13px;
            line-height: 1.25;
        }

        .step-label span {
            display: block;
            margin-top: 3px;
            font-size: 11px;
            color: #bcd2de;
            line-height: 1.35;
        }

        .aside-facts {
            margin-top: auto;
            display: grid;
            gap: 6px;
        }

        .aside-facts__head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 2px;
        }

        .aside-facts__head strong {
            display: block;
            color: #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .aside-facts__head span {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .insight-card {
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            gap: 8px;
            padding: 7px 0;
            color: #d7deeb;
            border-top: 1px solid rgba(255, 255, 255, .06);
        }

        .insight-card strong {
            display: block;
            color: #e5eaf3;
            font-size: 11px;
            line-height: 1.25;
        }

        .insight-card span:last-child {
            display: block;
            margin-top: 2px;
            color: #a8b3c7;
            font-size: 10px;
            line-height: 1.35;
        }

        .insight-card .mini-icon,
        .button-icon {
            width: 22px;
            height: 22px;
            display: inline-grid;
            place-items: center;
            border-radius: 6px;
            color: #d8c08a;
            background: rgba(245, 158, 11, .06);
            border: 1px solid rgba(245, 158, 11, .10);
            font-size: 8px;
            font-weight: 900;
        }

        .button .button-icon {
            width: 22px;
            height: 22px;
            color: #ffffff;
            background: rgba(255, 255, 255, .18);
            font-size: 11px;
            border-radius: 7px;
            border: 0;
        }

        .installer-main {
            padding: 20px;
            display: flex;
            align-items: flex-start;
        }

        .installer-wrap {
            width: min(900px, 100%);
            margin: 0 auto;
            display: grid;
            gap: 14px;
        }

        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .topbar h2 {
            margin: 0;
            font-size: 21px;
            letter-spacing: 0;
        }

        .topbar p,
        .topbar span {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .wizard-panel {
            height: min(590px, calc(100vh - 112px));
            min-height: 500px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: 0 18px 40px rgba(var(--primary-rgb), .08);
            display: grid;
            grid-template-rows: minmax(0, 1fr) auto;
        }

        .wizard-body {
            min-height: 0;
            overflow: auto;
            padding: 18px;
            scrollbar-width: thin;
            scrollbar-color: rgba(var(--primary-rgb), .36) transparent;
        }

        .wizard-body::-webkit-scrollbar {
            width: 8px;
        }

        .wizard-body::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(var(--primary-rgb), .28);
        }

        .wizard-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .step-panel {
            display: none;
            gap: 14px;
        }

        .step-panel.is-active {
            display: grid;
        }

        .section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .section-title {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 10px;
            align-items: start;
        }

        .section-icon {
            width: 34px;
            height: 34px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
            color: #ffffff;
            background: var(--primary);
            font-size: 11px;
            font-weight: 900;
        }

        .section-title h3 {
            margin: 0;
            font-size: 17px;
            letter-spacing: 0;
        }

        .section-title p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .status-pill {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 8px;
            padding: 6px 9px;
            color: var(--success);
            background: var(--success-soft);
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-pill.is-danger {
            color: var(--danger);
            background: var(--danger-soft);
        }

        .check-layout {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .check-group,
        .guide,
        .review-list,
        .db-result {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }

        .check-group {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .check-group__body {
            min-height: 0;
            max-height: calc(var(--check-row-height, 56px) * 4);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(var(--primary-rgb), .36) transparent;
        }

        .check-group__body::-webkit-scrollbar {
            width: 6px;
        }

        .check-group__body::-webkit-scrollbar-track {
            background: transparent;
        }

        .check-group__body::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(var(--primary-rgb), .28);
        }

        .check-group__body::-webkit-scrollbar-thumb:hover {
            background: rgba(var(--primary-rgb), .48);
        }

        .check-group__body > .check-row {
            min-height: var(--check-row-height, 56px);
        }

        @media (max-width: 720px) {
            .check-group__body {
                --check-row-height: 52px;
            }
        }

        .check-title,
        .guide-title,
        .review-title,
        .result-title {
            margin: 0;
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            background: #f8fafc;
            color: #344054;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .check-row,
        .review-row,
        .result-row {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            gap: 9px;
            align-items: start;
            padding: 9px 12px;
            border-bottom: 1px solid #edf1f5;
        }

        .check-row:last-child,
        .review-row:last-child,
        .result-row:last-child {
            border-bottom: 0;
        }

        .check-icon,
        .result-icon {
            width: 28px;
            height: 28px;
            display: inline-grid;
            place-items: center;
            border-radius: 50%;
            color: #ffffff;
            background: var(--danger);
            font-size: 10px;
            font-weight: 900;
        }

        .check-icon.is-ok,
        .result-icon.is-success {
            background: var(--success);
        }

        .result-icon.is-warning {
            background: var(--warning);
        }

        .check-label,
        .result-label,
        .review-label {
            display: block;
            font-weight: 800;
            font-size: 13px;
            color: #26364a;
        }

        .check-help,
        .result-detail,
        .review-value {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .guide {
            background: #fbfcfe;
        }

        .guide ul {
            margin: 0;
            padding: 10px 14px 12px 28px;
            color: #44546a;
            font-size: 12px;
            line-height: 1.5;
        }

        .notice,
        .alert {
            display: grid;
            grid-template-columns: 32px minmax(0, 1fr);
            gap: 9px;
            align-items: start;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
            line-height: 1.45;
        }

        .notice {
            border: 1px solid #f3d391;
            color: #744d00;
            background: var(--warning-soft);
        }

        .alert {
            border: 1px solid #f1b3b3;
            color: #8f1d1d;
            background: var(--danger-soft);
        }

        .notice .button-icon,
        .alert .button-icon {
            width: 24px;
            height: 24px;
            color: #744d00;
            background: rgba(245, 158, 11, .14);
            border: 1px solid rgba(245, 158, 11, .28);
            font-size: 9px;
            line-height: 1;
        }

        .alert .button-icon {
            color: var(--danger);
            background: rgba(189, 45, 45, .10);
            border-color: rgba(189, 45, 45, .22);
        }

        .install-error {
            display: grid;
            gap: 10px;
            padding: 14px 16px;
            border: 1px solid rgba(189, 45, 45, .35);
            border-radius: 12px;
            background:
                radial-gradient(circle at 100% 0%, rgba(189, 45, 45, .10), transparent 55%),
                linear-gradient(180deg, #fff5f5 0%, #ffffff 100%);
            box-shadow: 0 1px 2px rgba(189, 45, 45, .05), 0 8px 20px -12px rgba(189, 45, 45, .25);
        }

        .install-error__head {
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
        }

        .install-error__icon {
            display: inline-grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            color: #ffffff;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            font-size: 16px;
            font-weight: 900;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.18) inset, 0 4px 10px -4px rgba(189, 45, 45, .45);
        }

        .install-error__copy strong {
            display: block;
            color: #7f1d1d;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 3px;
        }

        .install-error__copy p {
            margin: 0;
            color: #9b2c2c;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .install-error__guide {
            border: 1px solid rgba(189, 45, 45, .18);
            border-radius: 10px;
            background: #ffffff;
            overflow: hidden;
        }

        .install-error__guide summary {
            padding: 10px 12px;
            color: #7f1d1d;
            background: #fff5f5;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            list-style: none;
        }

        .install-error__guide summary::-webkit-details-marker {
            display: none;
        }

        .install-error__guide summary::after {
            content: "+";
            float: right;
            font-size: 14px;
            font-weight: 900;
            color: #b91c1c;
        }

        .install-error__guide[open] summary::after {
            content: "−";
        }

        .install-error__guide ul {
            margin: 0;
            padding: 10px 14px 12px 28px;
            color: #44546a;
            font-size: 12px;
            line-height: 1.55;
        }

        .install-error__guide li + li {
            margin-top: 6px;
        }

        .install-error__hint {
            margin: 0;
            padding: 8px 10px;
            border-radius: 8px;
            background: rgba(189, 45, 45, .06);
            color: #7f1d1d;
            font-size: 11.5px;
            font-weight: 700;
            line-height: 1.5;
        }

        .fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            align-items: start;
        }

        .field {
            display: grid;
            gap: 7px;
            align-self: start;
        }

        .field.is-wide {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: 800;
            color: #344054;
        }

        input,
        select {
            width: 100%;
            min-height: 40px;
            border: 1px solid #cbd6e2;
            border-radius: 8px;
            padding: 8px 10px;
            color: var(--ink);
            background: #ffffff;
            outline: none;
            font-size: 13px;
        }

        input:focus,
        select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .14);
        }

        .error {
            color: var(--danger);
            font-size: 12px;
            line-height: 1.45;
        }

        .field-help {
            display: block;
            min-height: 36px;
            color: var(--muted);
            font-size: 11.5px;
            line-height: 1.5;
        }

        .field-help.is-muted {
            color: #94a3b8;
        }

        .field-help code {
            padding: 1px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #334155;
            font-size: 11.5px;
            font-weight: 600;
        }

        /* ── Password requirements helper ─────────────────────────── */
        .pw-help {
            margin-top: 4px;
            padding: 14px 16px;
            border: 1px solid #e1e7f0;
            border-radius: 10px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .pw-help__head {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eef1f6;
        }

        .pw-help__icon {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            flex-shrink: 0;
        }

        .pw-help__copy strong {
            display: block;
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .pw-help__copy small {
            display: block;
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.5;
        }

        .pw-help__list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 6px;
        }

        @media (min-width: 560px) {
            .pw-help__list {
                grid-template-columns: 1fr 1fr;
            }
        }

        .pw-help__list li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #64748b;
            transition: color 0.16s ease, font-weight 0.16s ease;
        }

        .pw-help__dot {
            position: relative;
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1.5px solid #cbd6e2;
            background: #fff;
            flex-shrink: 0;
            transition: background-color 0.16s ease, border-color 0.16s ease, transform 0.16s ease;
        }

        .pw-help__list li.is-met {
            color: #047857;
            font-weight: 600;
        }

        .pw-help__list li.is-met .pw-help__dot {
            background: #10b981;
            border-color: #10b981;
            transform: scale(1.05);
        }

        .pw-help__list li.is-met .pw-help__dot::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 7px;
            height: 4px;
            border-left: 2px solid #fff;
            border-bottom: 2px solid #fff;
            transform: translate(-55%, -75%) rotate(-45deg);
        }

        .pw-help__tip {
            margin: 12px 0 0;
            padding: 8px 10px;
            background: rgba(var(--primary-rgb), 0.06);
            border-left: 3px solid var(--primary);
            border-radius: 6px;
            font-size: 11.5px;
            color: #344054;
            line-height: 1.55;
        }

        .pw-help__tip strong {
            color: var(--primary);
            font-weight: 800;
        }

        /* Defensive: strip browser/password-manager injected icons from
           our scoped password fields so the input doesn't get a stray
           red overlay on the right edge. */
        input[data-pw-check],
        input[data-pw-confirm] {
            appearance: none;
            -webkit-appearance: none;
            background-image: none !important;
        }

        input[data-pw-check]::-webkit-credentials-auto-fill-button,
        input[data-pw-check]::-webkit-strong-password-auto-fill-button,
        input[data-pw-check]::-webkit-caps-lock-indicator,
        input[data-pw-check]::-ms-reveal,
        input[data-pw-check]::-ms-clear,
        input[data-pw-confirm]::-webkit-credentials-auto-fill-button,
        input[data-pw-confirm]::-webkit-strong-password-auto-fill-button,
        input[data-pw-confirm]::-webkit-caps-lock-indicator,
        input[data-pw-confirm]::-ms-reveal,
        input[data-pw-confirm]::-ms-clear {
            display: none !important;
            -webkit-appearance: none !important;
            appearance: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .db-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfcfe;
        }

        .db-actions.is-success {
            border-color: #a8dbc5;
            background: var(--success-soft);
        }

        .db-actions.is-error {
            border-color: #f5c6c6;
            background: #fffafa;
        }

        .db-actions p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .db-actions.is-success p {
            color: var(--success);
            font-weight: 800;
        }

        .db-actions.is-error p {
            color: #9f3a3a;
            font-weight: 700;
        }

        .db-result {
            display: none;
        }

        .db-result.is-visible {
            display: block;
        }

        .db-result.is-success {
            border-color: #a8dbc5;
        }

        .db-result.is-warning {
            border-color: #efc16d;
        }

        .db-result.is-error {
            border-color: #f3c4c4;
        }

        .db-result.is-error .result-title {
            color: #a43a3a;
            background: #fff8f8;
            border-bottom-color: #f3c4c4;
            font-weight: 700;
            text-transform: none;
        }

        .review-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .review-row {
            grid-template-columns: minmax(0, 1fr);
        }

        .review-value {
            font-weight: 600;
        }

        .review-preview {
            gap: 10px;
        }

        .review-preview .section-head {
            padding-bottom: 2px;
        }

        .review-preview .notice {
            grid-template-columns: 26px minmax(0, 1fr);
            gap: 8px;
            padding: 8px 10px;
            color: #805000;
            background: #fffaf0;
        }

        .review-preview .notice .button-icon {
            width: 22px;
            height: 22px;
            border-radius: 7px;
        }

        .review-preview .review-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .review-preview .review-list {
            border-color: #dbe5f0;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(20, 33, 61, .035);
        }

        .review-preview .review-title {
            padding: 8px 10px;
            background: #f8fafc;
            color: #475569;
            font-size: 10px;
            letter-spacing: .04em;
        }

        .review-preview .review-row {
            grid-template-columns: 92px minmax(0, 1fr);
            gap: 8px;
            align-items: center;
            padding: 8px 10px;
            min-height: 38px;
        }

        .review-preview .review-label {
            font-size: 11px;
            color: #64748b;
        }

        .review-preview .review-value {
            margin-top: 0;
            color: #25364d;
            font-size: 12px;
            line-height: 1.35;
            font-weight: 800;
        }

        .review-preview .review-list.is-wide {
            grid-column: span 2;
        }

        /* ────────────────────────────────────────────────────────────────
           Premium Final-Review (rv) styles — overlays the older
           .review-preview structure with a richer card system.
           ──────────────────────────────────────────────────────────────── */
        .step-panel.rv.is-active {
            display: grid;
            gap: 14px;
        }

        /* ── Status bar (database / admin) ────────────────────────────── */
        .rv__status {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .rv__stat {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03), 0 6px 14px -10px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
        }

        .rv__stat::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #cbd5e1 0%, transparent 100%);
            opacity: 0.7;
        }

        .rv__stat-icon {
            position: relative;
            display: inline-grid;
            place-items: center;
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #64748b;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .rv__stat-icon::after {
            content: "";
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            background: #cbd5e1;
            transition: background 0.18s ease;
        }

        .rv__stat-icon svg {
            width: 16px;
            height: 16px;
        }

        .rv__stat-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .rv__stat-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 3px;
        }

        .rv__stat-value {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rv__stat.is-pending::before {
            background: linear-gradient(90deg, #f59e0b 0%, transparent 100%);
        }

        .rv__stat.is-pending .rv__stat-icon::after {
            background: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.16);
        }

        .rv__stat.is-ok {
            border-color: rgba(5, 150, 105, 0.25);
            background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%);
            box-shadow: 0 1px 2px rgba(5, 150, 105, 0.04), 0 6px 16px -10px rgba(5, 150, 105, 0.18);
        }

        .rv__stat.is-ok::before {
            background: linear-gradient(90deg, #059669 0%, transparent 100%);
            opacity: 1;
        }

        .rv__stat.is-ok .rv__stat-icon {
            background: rgba(5, 150, 105, 0.12);
            color: #047857;
        }

        .rv__stat.is-ok .rv__stat-icon::after {
            background: #059669;
            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.16);
        }

        .rv__stat.is-ok .rv__stat-value {
            color: #047857;
        }

        /* ── Plan card — what installer will do ───────────────────────── */
        .rv__plan {
            padding: 14px 16px;
            border: 1px solid rgba(var(--primary-rgb), 0.18);
            border-radius: 12px;
            background:
                radial-gradient(circle at 0% 0%, rgba(var(--primary-rgb), 0.06), transparent 50%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .rv__plan-head {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid #eef1f6;
        }

        .rv__plan-icon {
            display: inline-grid;
            place-items: center;
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.16) 0%, rgba(var(--primary-rgb), 0.08) 100%);
            color: var(--primary);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.6) inset;
        }

        .rv__plan-icon svg {
            width: 18px;
            height: 18px;
        }

        .rv__plan-head strong {
            display: block;
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .rv__plan-head small {
            display: block;
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.5;
        }

        .rv__plan-steps {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 6px;
        }

        .rv__plan-steps li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 8px;
            font-size: 12.5px;
            color: #334155;
            line-height: 1.5;
            border-radius: 6px;
            transition: background 0.16s ease;
        }

        .rv__plan-steps li:hover {
            background: rgba(var(--primary-rgb), 0.04);
        }

        .rv__plan-steps li code {
            padding: 1px 6px;
            border-radius: 4px;
            background: rgba(var(--primary-rgb), 0.08);
            color: var(--primary);
            font-size: 11.5px;
            font-weight: 700;
        }

        .rv__plan-num {
            display: inline-grid;
            place-items: center;
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 2px 4px -2px rgba(var(--primary-rgb), 0.4);
        }

        /* ── Configuration cards grid ─────────────────────────────────── */
        .rv__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-auto-rows: 1fr;
            gap: 12px;
        }

        .rv__card {
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03), 0 6px 14px -10px rgba(15, 23, 42, 0.06);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .rv__card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 3px;
            background: var(--rv-card-accent, #cbd5e1);
            opacity: 0.85;
        }

        .rv__card--app   { --rv-card-accent: #2563eb; }
        .rv__card--db    { --rv-card-accent: #7c3aed; }
        .rv__card--admin { --rv-card-accent: #059669; }
        .rv__card--data  { --rv-card-accent: #d97706; }

        .rv__card:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 10px 22px -12px rgba(15, 23, 42, 0.12);
        }

        .rv__card-head {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 13px 14px 13px 16px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #eef1f6;
        }

        .rv__card-icon {
            display: inline-grid;
            place-items: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.6) inset;
        }

        .rv__card-icon svg {
            width: 16px;
            height: 16px;
        }

        .rv__card-icon--blue   { background: rgba(37, 99, 235, 0.1);  color: #2563eb; }
        .rv__card-icon--purple { background: rgba(139, 92, 246, 0.1); color: #7c3aed; }
        .rv__card-icon--green  { background: rgba(5, 150, 105, 0.1);  color: #047857; }
        .rv__card-icon--amber  { background: rgba(217, 119, 6, 0.1);  color: #b45309; }

        .rv__card-head strong {
            display: block;
            font-size: 13.5px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .rv__card-head small {
            font-size: 11px;
            color: #64748b;
            letter-spacing: 0.02em;
        }

        .rv__card-list {
            margin: 0;
            padding: 4px 0;
            display: grid;
            flex: 1 1 auto;
            align-content: start;
        }

        .rv__row {
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            padding: 9px 14px 9px 16px;
            border-top: 1px solid transparent;
            transition: background 0.16s ease;
        }

        .rv__row:not(:first-child) {
            border-top-color: #f1f5f9;
        }

        .rv__row:hover {
            background: #fbfcfe;
        }

        .rv__row dt {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .rv__row dd {
            margin: 0;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
            word-break: break-word;
        }

        .rv__row dd code {
            padding: 2px 7px;
            border-radius: 5px;
            background: #f1f5f9;
            color: #334155;
            font-size: 11.5px;
            font-weight: 700;
        }

        /* ── Ready banner ─────────────────────────────────────────────── */
        .rv__ready {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border: 1px solid rgba(5, 150, 105, 0.25);
            border-radius: 14px;
            background:
                radial-gradient(circle at 100% 0%, rgba(5, 150, 105, 0.1), transparent 55%),
                linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%);
            box-shadow: 0 1px 2px rgba(5, 150, 105, 0.04), 0 8px 20px -10px rgba(5, 150, 105, 0.22);
            overflow: hidden;
        }

        .rv__ready::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #10b981, #059669, transparent);
            opacity: 0.85;
        }

        .rv__ready-icon {
            position: relative;
            animation: rv-pulse 2.4s ease-in-out infinite;
            display: inline-grid;
            place-items: center;
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            color: #ffffff;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.18) inset, 0 4px 10px -4px rgba(5, 150, 105, 0.4);
        }

        @keyframes rv-pulse {
            0%, 100% {
                box-shadow: 0 1px 0 rgba(255, 255, 255, 0.18) inset,
                            0 4px 10px -4px rgba(5, 150, 105, 0.4),
                            0 0 0 0 rgba(5, 150, 105, 0.32);
            }
            50% {
                box-shadow: 0 1px 0 rgba(255, 255, 255, 0.18) inset,
                            0 4px 14px -2px rgba(5, 150, 105, 0.5),
                            0 0 0 8px rgba(5, 150, 105, 0);
            }
        }

        .rv__ready-icon svg {
            width: 20px;
            height: 20px;
        }

        .rv__ready-copy strong {
            display: block;
            font-size: 13.5px;
            font-weight: 800;
            color: #065f46;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .rv__ready-copy span {
            display: block;
            font-size: 12px;
            color: #047857;
            line-height: 1.55;
        }

        /* ── Responsive ───────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .rv__status {
                grid-template-columns: 1fr;
            }
            .rv__grid {
                grid-template-columns: 1fr;
            }
        }

        .wizard-actions {
            padding: 10px 16px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            background: #fbfcfe;
            border-radius: 0 0 8px 8px;
        }

        .action-group {
            display: flex;
            gap: 8px;
        }

        .button {
            min-height: 36px;
            border: 1px solid transparent;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 7px 11px;
            color: #ffffff;
            background: var(--primary);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .button:hover {
            background: var(--primary-dark);
        }

        .button.is-secondary {
            color: #344054;
            border-color: #cbd6e2;
            background: #ffffff;
        }

        .button.is-secondary:hover {
            background: #eef3f7;
        }

        .button.is-secondary .button-icon {
            color: var(--primary);
            background: rgba(var(--primary-rgb), .08);
            border: 1px solid rgba(var(--primary-rgb), .18);
        }

        .button.is-accent {
            color: #ffffff;
            background: var(--primary);
        }

        .button.is-accent:hover {
            background: var(--primary-dark);
        }

        .button:disabled {
            cursor: not-allowed;
            opacity: .68;
        }

        .installing {
            display: none;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .installing.is-visible {
            display: inline-flex;
        }

        .install-progress {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: none;
            place-items: center;
            padding: 24px;
            background: rgba(248, 250, 252, .94);
            backdrop-filter: blur(8px);
        }

        .wizard-panel.is-installing .install-progress {
            display: grid;
        }

        .install-progress__box {
            width: min(520px, 100%);
            border: 1px solid rgba(var(--primary-rgb), .14);
            border-radius: 8px;
            padding: 18px;
            background: #ffffff;
            box-shadow: 0 22px 55px rgba(20, 33, 61, .12);
        }

        .install-progress__head {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
        }

        .install-progress__icon {
            width: 38px;
            height: 38px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
            color: #ffffff;
            background: var(--primary);
            font-size: 11px;
            font-weight: 900;
        }

        .install-progress h3 {
            margin: 0;
            font-size: 18px;
            line-height: 1.25;
        }

        .install-progress p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .install-progress__meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            color: #344054;
            font-size: 12px;
            font-weight: 800;
        }

        .install-progress__track {
            height: 8px;
            margin-top: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #e8edf4;
        }

        .install-progress__fill {
            width: 8%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--primary), #6c83ff);
            transition: width .36s ease;
        }

        .install-progress__steps {
            display: grid;
            gap: 8px;
            margin: 16px 0 0;
            padding: 0;
            list-style: none;
        }

        .install-progress__step {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            gap: 9px;
            align-items: start;
            padding: 9px 0;
            border-top: 1px solid #edf1f5;
            color: var(--muted);
        }

        .install-progress__badge {
            width: 28px;
            height: 28px;
            display: inline-grid;
            place-items: center;
            border-radius: 50%;
            color: #64748b;
            background: #eef2f7;
            font-size: 9px;
            font-weight: 900;
        }

        .install-progress__step strong {
            display: block;
            color: #334155;
            font-size: 12px;
            line-height: 1.3;
        }

        .install-progress__step span:last-child {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            line-height: 1.35;
        }

        .install-progress__step.is-active .install-progress__badge {
            color: #ffffff;
            background: var(--primary);
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), .10);
        }

        .install-progress__step.is-complete .install-progress__badge {
            color: #ffffff;
            background: var(--success);
        }

        .install-progress__note {
            margin-top: 14px;
            padding: 9px 10px;
            border-radius: 8px;
            color: #475467;
            background: #f8fafc;
            font-size: 11px;
            line-height: 1.45;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #d8e0e8;
            border-top-color: var(--primary);
            animation: spin .75s linear infinite;
        }

        [hidden] {
            display: none !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 1020px) {
            .installer-shell {
                grid-template-columns: 1fr;
            }

            .installer-aside {
                min-height: auto;
            }

            .installer-main {
                align-items: stretch;
            }
        }

        @media (max-width: 720px) {
            .installer-aside,
            .installer-main {
                padding: 22px;
            }

            .topbar,
            .section-head,
            .db-actions,
            .wizard-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .check-layout,
            .fields,
            .review-grid {
                grid-template-columns: 1fr;
            }

            .review-preview .review-grid,
            .review-preview .review-list.is-wide {
                grid-template-columns: 1fr;
                grid-column: auto;
            }

            .review-preview .review-row {
                grid-template-columns: 1fr;
                align-items: start;
            }

            .install-progress {
                padding: 16px;
            }

            .field.is-wide {
                grid-column: auto;
            }

            .action-group,
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="installer-shell" data-can-install="{{ $status['can_install'] ? '1' : '0' }}">
        <aside class="installer-aside">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" role="img">
                        <rect x="2" y="2" width="28" height="28" rx="8" fill="rgba(255,255,255,.14)"/>
                        <rect x="8" y="15" width="16" height="7" rx="3" fill="#fff"/>
                        <path d="M8 15c0-5 16-5 16 0" fill="none" stroke="rgba(255,255,255,.4)" stroke-width="1.6" stroke-linecap="round"/>
                        <circle cx="16" cy="11.1" r="1.4" fill="#fff"/>
                        <circle cx="22.7" cy="7.7" r="2.3" fill="#fff"/>
                        <circle cx="22.7" cy="7.7" r="0.9" fill="#1D4ED8"/>
                    </svg>
                </span>
                <span>DigitalWallet</span>
            </div>

            <div class="aside-copy">
                <h1>{{ __('Installation Wizard') }}</h1>
                <p>{{ __('Follow each step, test database access, import DB/digitalwallet.sql, and create the first super admin account.') }}</p>
            </div>

            <div class="progress-shell" aria-label="{{ __('Installation progress') }}">
                <div class="progress-meta">
                    <span data-progress-label>{{ __('Step 1 of 5') }}</span>
                    <span data-progress-percent>20%</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" data-progress-fill></div>
                </div>

                <ol class="step-list">
                    <li>
                        <button class="step-button" type="button" data-step-trigger="0" aria-current="step">
                            <span class="step-number">1</span>
                            <span class="step-label">
                                <strong>{{ __('Server Check') }}</strong>
                                <span>{{ __('PHP extensions and writable paths') }}</span>
                            </span>
                        </button>
                    </li>
                    <li>
                        <button class="step-button" type="button" data-step-trigger="1" disabled>
                            <span class="step-number">2</span>
                            <span class="step-label">
                                <strong>{{ __('Application') }}</strong>
                                <span>{{ __('Name and production URL') }}</span>
                            </span>
                        </button>
                    </li>
                    <li>
                        <button class="step-button" type="button" data-step-trigger="2" disabled>
                            <span class="step-number">3</span>
                            <span class="step-label">
                                <strong>{{ __('Database') }}</strong>
                                <span>{{ __('Connection test and permissions') }}</span>
                            </span>
                        </button>
                    </li>
                    <li>
                        <button class="step-button" type="button" data-step-trigger="3" disabled>
                            <span class="step-number">4</span>
                            <span class="step-label">
                                <strong>{{ __('Super Admin') }}</strong>
                                <span>{{ __('First admin login') }}</span>
                            </span>
                        </button>
                    </li>
                    <li>
                        <button class="step-button" type="button" data-step-trigger="4" disabled>
                            <span class="step-number">5</span>
                            <span class="step-label">
                                <strong>{{ __('Review') }}</strong>
                                <span>{{ __('Install and lock setup') }}</span>
                            </span>
                        </button>
                    </li>
                </ol>
            </div>

            <div class="aside-facts">
                <div class="aside-facts__head">
                    <strong>{{ __('Setup Summary') }}</strong>
                    <span>{{ __('Auto guided') }}</span>
                </div>

                <div class="insight-card">
                    <span class="mini-icon">DB</span>
                    <span>
                        <strong>{{ __('Auto Database') }}</strong>
                        <span>{{ __('Creates MySQL/MariaDB database when access allows it.') }}</span>
                    </span>
                </div>

                <div class="insight-card">
                    <span class="mini-icon">SQL</span>
                    <span>
                        <strong>{{ __('Script Import') }}</strong>
                        <span>{{ __('Imports DB/digitalwallet.sql after connection checks pass.') }}</span>
                    </span>
                </div>

                <div class="insight-card">
                    <span class="mini-icon">LK</span>
                    <span>
                        <strong>{{ __('Safe Finish') }}</strong>
                        <span>{{ __('Creates admin access, clears cache, then locks setup.') }}</span>
                    </span>
                </div>
            </div>
        </aside>

        <section class="installer-main">
            <div class="installer-wrap">
                <div class="topbar">
                    <div>
                        <h2>{{ __('Install DigitalWallet') }}</h2>
                        <p>{{ __('Complete the checks in order. Database access is verified before data import and final installation.') }}</p>
                    </div>
                    <span>{{ __('Laravel :version', ['version' => app()->version()]) }}</span>
                </div>

                <form class="wizard-panel" method="POST" action="{{ route('install.store') }}" autocomplete="off" data-install-form>
                    @csrf

                    <div class="wizard-body">
                        @if($errors->any())
                            <div class="alert">
                                <span class="button-icon">!</span>
                                <span>{{ __('Please review the highlighted fields and continue from the step that needs attention.') }}</span>
                            </div>
                        @endif

                        @if(session()->has('install_error'))
                            <div class="install-error" data-install-error-banner role="alert">
                                <div class="install-error__head">
                                    <span class="install-error__icon" aria-hidden="true">!</span>
                                    <div class="install-error__copy">
                                        <strong>{{ __('Installation could not finish') }}</strong>
                                        <p>{{ session('install_error.message') }}</p>
                                    </div>
                                </div>
                                @if(! empty(session('install_error.guidance')))
                                    <details class="install-error__guide">
                                        <summary>{{ __('How to grant the missing permissions') }}</summary>
                                        <ul>
                                            @foreach((array) session('install_error.guidance') as $tip)
                                                <li>{{ $tip }}</li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                                <p class="install-error__hint">{{ __('Apply the fix on the server, refresh this page, then click "Start Installation" again. Your form values are kept.') }}</p>
                            </div>
                        @endif

                        <section class="step-panel is-active" data-step-panel="0">
                            <div class="section-head">
                                <div class="section-title">
                                    <span class="section-icon">SV</span>
                                    <div>
                                        <h3>{{ __('Server Requirements') }}</h3>
                                        <p>{{ __('Fix any failed item here before moving to the installation form.') }}</p>
                                    </div>
                                </div>
                                <span class="status-pill {{ $status['can_install'] ? '' : 'is-danger' }}">
                                    {{ $status['can_install'] ? __('Ready to Continue') : __('Action Needed') }}
                                </span>
                            </div>

                            @if(! $status['can_install'])
                                <div class="notice">
                                    <span class="button-icon">!</span>
                                    <span>{{ __('Resolve the failed requirement or writable-path checks, then refresh this page. Installation stays blocked until the server can safely write files and run the required PHP extensions.') }}</span>
                                </div>
                            @endif

                            <div class="check-layout">
                                <div class="check-group">
                                    <p class="check-title">{{ __('Required Extensions') }}</p>
                                    <div class="check-group__body">
                                        @foreach($status['requirements'] as $check)
                                            <div class="check-row">
                                                <span class="check-icon {{ $check['status'] ? 'is-ok' : '' }}">
                                                    {{ $check['status'] ? 'OK' : '!' }}
                                                </span>
                                                <span>
                                                    <span class="check-label">{{ $check['label'] }}</span>
                                                    <span class="check-help">{{ $check['help'] }}</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="check-group">
                                    <p class="check-title">{{ __('Writable Paths') }}</p>
                                    <div class="check-group__body">
                                        @foreach($status['writable'] as $check)
                                            <div class="check-row">
                                                <span class="check-icon {{ $check['status'] ? 'is-ok' : '' }}">
                                                    {{ $check['status'] ? 'OK' : '!' }}
                                                </span>
                                                <span>
                                                    <span class="check-label">{{ $check['label'] }}</span>
                                                    <span class="check-help">{{ $check['help'] }}</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="guide">
                                <p class="guide-title">{{ __('Permission Guide') }}</p>
                                <ul>
                                    <li>{{ __('Enable missing PHP extensions from cPanel Select PHP Version, WHM EasyApache, or php.ini, then restart PHP/Apache/Nginx.') }}</li>
                                    <li>{{ __('Make .env, storage, bootstrap/cache, and public storage paths writable by the web server user.') }}</li>
                                    <li>{{ __('On Linux/VPS, set the correct owner and write permission for storage and bootstrap/cache. On shared hosting, use File Manager permissions.') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section class="step-panel" data-step-panel="1">
                            <div class="section-head">
                                <div class="section-title">
                                    <span class="section-icon">AP</span>
                                    <div>
                                        <h3>{{ __('Application Details') }}</h3>
                                        <p>{{ __('These values shape the .env file, the admin login URL, and the currency every wallet defaults to. All four are editable later from the admin settings.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="fields">
                                <div class="field">
                                    <label for="app_name">{{ __('Application Name') }}</label>
                                    <input id="app_name" name="app_name" value="{{ old('app_name', $defaults['app_name']) }}" required maxlength="80" data-review-source="app_name">
                                    <span class="field-help">{{ __('Shown on the browser tab, emails, and headers. Default: :default.', ['default' => $defaults['app_name']]) }}</span>
                                    @error('app_name') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label for="app_url">{{ __('Application URL') }}</label>
                                    <input id="app_url" name="app_url" type="url" value="{{ old('app_url', $defaults['app_url']) }}" required maxlength="255" data-review-source="app_url" data-admin-url-base>
                                    <span class="field-help">{{ __('Production URL with the scheme (https://...). Used for password resets, payment callbacks, and email links.') }}</span>
                                    @error('app_url') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label for="admin_prefix">{{ __('Admin URL Prefix') }}</label>
                                    <input id="admin_prefix"
                                           name="admin_prefix"
                                           value="{{ old('admin_prefix', $defaults['admin_prefix']) }}"
                                           required
                                           minlength="2"
                                           maxlength="20"
                                           pattern="[a-z0-9][a-z0-9-]*[a-z0-9]"
                                           placeholder="{{ $defaults['admin_prefix'] }}"
                                           data-review-source="admin_prefix"
                                           data-admin-prefix>
                                    <span class="field-help">
                                        {{ __('Login URL preview:') }}
                                        <code data-admin-url-preview>{{ rtrim($defaults['app_url'], '/').'/'.$defaults['admin_prefix'].'/login' }}</code>
                                    </span>
                                    @error('admin_prefix') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label for="default_currency_code">{{ __('Default Currency') }}</label>
                                    <select id="default_currency_code"
                                            name="default_currency_code"
                                            required
                                            data-review-source="default_currency_code"
                                            data-currency-select>
                                        @foreach($currencyCatalog as $code => $info)
                                            <option value="{{ $code }}"
                                                    data-currency-symbol="{{ $info['symbol'] }}"
                                                    data-currency-name="{{ $info['name'] }}"
                                                    @selected(old('default_currency_code', $defaults['default_currency_code']) === $code)>
                                                {{ $code }} — {{ $info['name'] }} ({{ $info['symbol'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="field-help">{{ __('Used as the site-wide wallet currency, displayed across dashboards. Add or change currencies later from Admin → Currencies. Default: :default.', ['default' => $defaults['default_currency_code']]) }}</span>
                                    @error('default_currency_code') <span class="error">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="guide">
                                <p class="guide-title">{{ __('What these values control') }}</p>
                                <ul>
                                    <li>{{ __('Admin URL Prefix uses lowercase letters, numbers, and dashes (e.g. "control-panel"). A non-default value adds a tiny layer of obscurity to bot scanners. Leave it blank or invalid and the installer falls back to "admin".') }}</li>
                                    <li>{{ __('Default Currency is the site\'s base wallet currency and the unit shown on dashboards, transactions, and admin reports. You can still add more currencies in the backend after install.') }}</li>
                                    <li>{{ __('Both can be changed later from Admin → Settings → General Settings and Admin → Currencies. Changing the admin prefix requires logging in again under the new URL.') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section class="step-panel" data-step-panel="2">
                            <div class="section-head">
                                <div class="section-title">
                                    <span class="section-icon">DB</span>
                                    <div>
                                        <h3>{{ __('Database Connection') }}</h3>
                                        <p>{{ __('Test credentials first. Installation imports DB/digitalwallet.sql only after the database is empty and reachable.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="notice">
                                <span class="button-icon">INFO</span>
                                <span>{{ __('For MySQL/MariaDB, the test connects to the server, creates the database if it does not exist, and checks whether it is empty. Existing tables must be cleared or a new database name must be used.') }}</span>
                            </div>

                            <div class="fields">
                                <div class="field">
                                    <label for="db_connection">{{ __('Connection') }}</label>
                                    <select id="db_connection" name="db_connection" data-db-connection required data-review-source="db_connection">
                                        @foreach(['mysql' => 'MySQL', 'mariadb' => 'MariaDB', 'sqlite' => 'SQLite'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('db_connection', $defaults['db_connection']) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('db_connection') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field" data-mysql-field>
                                    <label for="db_host">{{ __('Host') }}</label>
                                    <input id="db_host" name="db_host" value="{{ old('db_host', $defaults['db_host']) }}" maxlength="255" data-db-watch data-review-source="db_host">
                                    @error('db_host') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field" data-mysql-field>
                                    <label for="db_port">{{ __('Port') }}</label>
                                    <input id="db_port" name="db_port" type="number" min="1" max="65535" value="{{ old('db_port', $defaults['db_port']) }}" data-db-watch data-review-source="db_port">
                                    @error('db_port') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label for="db_database">{{ __('Database') }}</label>
                                    <input id="db_database" name="db_database" value="{{ old('db_database', $defaults['db_database']) }}" required maxlength="255" data-db-database data-db-watch data-review-source="db_database">
                                    @error('db_database') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field" data-mysql-field>
                                    <label for="db_username">{{ __('Username') }}</label>
                                    <input id="db_username" name="db_username" value="{{ old('db_username', $defaults['db_username']) }}" maxlength="255" data-db-watch data-review-source="db_username">
                                    @error('db_username') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field" data-mysql-field>
                                    <label for="db_password">{{ __('Password') }}</label>
                                    <input id="db_password" name="db_password" type="password" maxlength="255" data-db-watch>
                                    @error('db_password') <span class="error">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="db-actions" data-db-actions>
                                <p data-db-status-text>{{ __('Database has not been tested yet. Run the test before continuing.') }}</p>
                                <button class="button is-accent" type="button" data-test-db>
                                    <span class="button-icon">?</span>
                                    <span>{{ __('Check Database Connection') }}</span>
                                </button>
                            </div>

                            <div class="db-result" data-db-result></div>

                            <div class="guide">
                                <p class="guide-title">{{ __('Database Permission Guide') }}</p>
                                <ul>
                                    <li>{{ __('The database user needs CREATE permission so the installer can create the database automatically.') }}</li>
                                    <li>{{ __('For import, grant SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, and REFERENCES permissions.') }}</li>
                                    <li>{{ __('On cPanel, open MySQL Databases, assign the user to the database, then select All Privileges.') }}</li>
                                    <li>{{ __('If pdo_mysql is missing, enable the PDO MySQL PHP extension and restart the PHP service.') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section class="step-panel" data-step-panel="3">
                            <div class="section-head">
                                <div class="section-title">
                                    <span class="section-icon">AD</span>
                                    <div>
                                        <h3>{{ __('Super Admin Account') }}</h3>
                                        <p>{{ __('This account receives full admin access after installation completes.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="fields">
                                <div class="field">
                                    <label for="admin_name">{{ __('Name') }}</label>
                                    <input id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required maxlength="120" data-review-source="admin_name">
                                    @error('admin_name') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label for="admin_email">{{ __('Email') }}</label>
                                    <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}" required maxlength="255" data-review-source="admin_email">
                                    @error('admin_email') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label for="admin_password">{{ __('Password') }}</label>
                                    <input id="admin_password"
                                           name="admin_password"
                                           type="password"
                                           required
                                           minlength="8"
                                           autocomplete="new-password"
                                           autocorrect="off"
                                           autocapitalize="off"
                                           spellcheck="false"
                                           data-lpignore="true"
                                           data-1p-ignore="true"
                                           data-form-type="other"
                                           data-bwignore="true"
                                           data-pw-check>
                                    @error('admin_password') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field">
                                    <label for="admin_password_confirmation">{{ __('Confirm Password') }}</label>
                                    <input id="admin_password_confirmation"
                                           name="admin_password_confirmation"
                                           type="password"
                                           required
                                           minlength="8"
                                           autocomplete="new-password"
                                           autocorrect="off"
                                           autocapitalize="off"
                                           spellcheck="false"
                                           data-lpignore="true"
                                           data-1p-ignore="true"
                                           data-form-type="other"
                                           data-bwignore="true"
                                           data-pw-confirm>
                                    @error('admin_password_confirmation') <span class="error">{{ $message }}</span> @enderror
                                </div>

                                <div class="field is-wide pw-help" aria-live="polite">
                                    <div class="pw-help__head">
                                        <span class="pw-help__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                            </svg>
                                        </span>
                                        <div class="pw-help__copy">
                                            <strong>{{ __('Password requirements') }}</strong>
                                            <small>{{ __('This account controls every admin feature. Use a strong, unique password — not one you use anywhere else.') }}</small>
                                        </div>
                                    </div>
                                    <ul class="pw-help__list">
                                        <li data-pw-rule="length"><span class="pw-help__dot" aria-hidden="true"></span>{{ __('At least 8 characters') }}</li>
                                        <li data-pw-rule="uppercase"><span class="pw-help__dot" aria-hidden="true"></span>{{ __('One uppercase letter (A–Z)') }}</li>
                                        <li data-pw-rule="lowercase"><span class="pw-help__dot" aria-hidden="true"></span>{{ __('One lowercase letter (a–z)') }}</li>
                                        <li data-pw-rule="number"><span class="pw-help__dot" aria-hidden="true"></span>{{ __('One number (0–9)') }}</li>
                                        <li data-pw-rule="match"><span class="pw-help__dot" aria-hidden="true"></span>{{ __('Both fields match') }}</li>
                                    </ul>
                                    <p class="pw-help__tip">
                                        <strong>{{ __('Recommended:') }}</strong>
                                        {{ __('Use 12+ characters, add a symbol (!@#$%), and store the password in a password manager. Avoid names, dates, or anything from your other accounts.') }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section class="step-panel review-preview rv" data-step-panel="4">
                            <div class="section-head">
                                <div class="section-title">
                                    <span class="section-icon">RV</span>
                                    <div>
                                        <h3>{{ __('Final Review') }}</h3>
                                        <p>{{ __('Verify the summary below — once you start the installer, these settings are locked in.') }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Status banner — DB / admin readiness at a glance --}}
                            <div class="rv__status">
                                <div class="rv__stat is-pending" data-rv-stat="database">
                                    <span class="rv__stat-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                                        </svg>
                                    </span>
                                    <div class="rv__stat-text">
                                        <span class="rv__stat-label">{{ __('Database Connection') }}</span>
                                        <span class="rv__stat-value" data-review-db-test>{{ __('Not tested') }}</span>
                                    </div>
                                </div>
                                <div class="rv__stat is-ok" data-rv-stat="admin">
                                    <span class="rv__stat-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                    </span>
                                    <div class="rv__stat-text">
                                        <span class="rv__stat-label">{{ __('Super Admin') }}</span>
                                        <span class="rv__stat-value">{{ __('Ready to create') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- What the installer will do — numbered plan --}}
                            <div class="rv__plan">
                                <div class="rv__plan-head">
                                    <span class="rv__plan-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/>
                                            <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/>
                                            <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>
                                            <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>{{ __('What happens when you click "Start Installation"') }}</strong>
                                        <small>{{ __('Each step runs in order. The page stays open until everything is done.') }}</small>
                                    </div>
                                </div>
                                <ol class="rv__plan-steps">
                                    <li><span class="rv__plan-num">1</span>{{ __('Write your DB credentials to') }} <code>.env</code>.</li>
                                    <li><span class="rv__plan-num">2</span>{{ __('Import the bundled') }} <code>DB/digitalwallet.sql</code> {{ __('schema + reference data.') }}</li>
                                    <li><span class="rv__plan-num">3</span>{{ __('Create the super admin account and assign full permissions.') }}</li>
                                    <li><span class="rv__plan-num">4</span>{{ __('Clear caches, link storage, and seal the installer.') }}</li>
                                </ol>
                            </div>

                            {{-- Configuration cards --}}
                            <div class="rv__grid">
                                <article class="rv__card rv__card--app">
                                    <header class="rv__card-head">
                                        <span class="rv__card-icon rv__card-icon--blue" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <line x1="2" y1="12" x2="22" y2="12"/>
                                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <strong>{{ __('Application') }}</strong>
                                            <small>{{ __('Site identity') }}</small>
                                        </div>
                                    </header>
                                    <dl class="rv__card-list">
                                        <div class="rv__row">
                                            <dt>{{ __('Name') }}</dt>
                                            <dd data-review="app_name">-</dd>
                                        </div>
                                        <div class="rv__row">
                                            <dt>{{ __('URL') }}</dt>
                                            <dd data-review="app_url">-</dd>
                                        </div>
                                        <div class="rv__row">
                                            <dt>{{ __('Admin Login') }}</dt>
                                            <dd><code data-review-admin-url>-</code></dd>
                                        </div>
                                        <div class="rv__row">
                                            <dt>{{ __('Currency') }}</dt>
                                            <dd data-review-currency>-</dd>
                                        </div>
                                    </dl>
                                </article>

                                <article class="rv__card rv__card--db">
                                    <header class="rv__card-head">
                                        <span class="rv__card-icon rv__card-icon--purple" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <strong>{{ __('Database') }}</strong>
                                            <small>{{ __('Connection target') }}</small>
                                        </div>
                                    </header>
                                    <dl class="rv__card-list">
                                        <div class="rv__row">
                                            <dt>{{ __('Driver') }}</dt>
                                            <dd data-review="db_connection">-</dd>
                                        </div>
                                        <div class="rv__row">
                                            <dt>{{ __('Database') }}</dt>
                                            <dd data-review="db_database">-</dd>
                                        </div>
                                        <div class="rv__row" data-review-mysql>
                                            <dt>{{ __('Host') }}</dt>
                                            <dd data-review="db_host">-</dd>
                                        </div>
                                        <div class="rv__row" data-review-mysql>
                                            <dt>{{ __('Username') }}</dt>
                                            <dd data-review="db_username">-</dd>
                                        </div>
                                    </dl>
                                </article>

                                <article class="rv__card rv__card--admin">
                                    <header class="rv__card-head">
                                        <span class="rv__card-icon rv__card-icon--green" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                                <circle cx="12" cy="11" r="2.5"/>
                                                <path d="M12 13.5v2"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <strong>{{ __('Super Admin') }}</strong>
                                            <small>{{ __('Full-access account') }}</small>
                                        </div>
                                    </header>
                                    <dl class="rv__card-list">
                                        <div class="rv__row">
                                            <dt>{{ __('Name') }}</dt>
                                            <dd data-review="admin_name">-</dd>
                                        </div>
                                        <div class="rv__row">
                                            <dt>{{ __('Email') }}</dt>
                                            <dd data-review="admin_email">-</dd>
                                        </div>
                                    </dl>
                                </article>

                                <article class="rv__card rv__card--data">
                                    <header class="rv__card-head">
                                        <span class="rv__card-icon rv__card-icon--amber" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <path d="M14 2v6h6"/>
                                                <path d="M12 18v-6"/>
                                                <path d="m9 15 3 3 3-3"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <strong>{{ __('Data Import') }}</strong>
                                            <small>{{ __('Bundled reference data') }}</small>
                                        </div>
                                    </header>
                                    <dl class="rv__card-list">
                                        <div class="rv__row">
                                            <dt>{{ __('Source') }}</dt>
                                            <dd><code>DB/digitalwallet.sql</code></dd>
                                        </div>
                                        <div class="rv__row">
                                            <dt>{{ __('Contents') }}</dt>
                                            <dd>{{ __('Schema + settings + reference rows') }}</dd>
                                        </div>
                                    </dl>
                                </article>
                            </div>

                            {{-- Ready banner --}}
                            <div class="rv__ready" data-rv-ready>
                                <span class="rv__ready-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                                <div class="rv__ready-copy">
                                    <strong>{{ __('Everything looks good.') }}</strong>
                                    <span>{{ __('Click "Start Installation" below to begin. Keep this page open until the success screen appears — installation usually takes 30–60 seconds.') }}</span>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="install-progress" data-install-progress aria-live="polite" aria-hidden="true">
                        <div class="install-progress__box">
                            <div class="install-progress__head">
                                <span class="install-progress__icon">SET</span>
                                <div>
                                    <h3>{{ __('Installing DigitalWallet') }}</h3>
                                    <p>{{ __('Please keep this page open. The installer is preparing the database, importing the bundled SQL file, and creating the admin account.') }}</p>
                                </div>
                            </div>

                            <div class="install-progress__meta">
                                <span data-install-progress-label>{{ __('Starting installation') }}</span>
                                <span data-install-progress-percent>8%</span>
                            </div>
                            <div class="install-progress__track" aria-hidden="true">
                                <div class="install-progress__fill" data-install-progress-fill></div>
                            </div>

                            <ol class="install-progress__steps">
                                <li class="install-progress__step is-active" data-install-progress-step>
                                    <span class="install-progress__badge">01</span>
                                    <span>
                                        <strong>{{ __('Writing Environment') }}</strong>
                                        <span>{{ __('Saving app and database values to the environment file.') }}</span>
                                    </span>
                                </li>
                                <li class="install-progress__step" data-install-progress-step>
                                    <span class="install-progress__badge">02</span>
                                    <span>
                                        <strong>{{ __('Preparing Database') }}</strong>
                                        <span>{{ __('Creating the database when permission is available and checking empty tables.') }}</span>
                                    </span>
                                </li>
                                <li class="install-progress__step" data-install-progress-step>
                                    <span class="install-progress__badge">03</span>
                                    <span>
                                        <strong>{{ __('Importing SQL') }}</strong>
                                        <span>{{ __('Loading DB/digitalwallet.sql into the selected database.') }}</span>
                                    </span>
                                </li>
                                <li class="install-progress__step" data-install-progress-step>
                                    <span class="install-progress__badge">04</span>
                                    <span>
                                        <strong>{{ __('Creating Super Admin') }}</strong>
                                        <span>{{ __('Creating the first admin account and default settings.') }}</span>
                                    </span>
                                </li>
                                <li class="install-progress__step" data-install-progress-step>
                                    <span class="install-progress__badge">05</span>
                                    <span>
                                        <strong>{{ __('Finishing Setup') }}</strong>
                                        <span>{{ __('Clearing cache, locking installer access, and redirecting to login.') }}</span>
                                    </span>
                                </li>
                            </ol>

                            <div class="install-progress__note">
                                {{ __('Large SQL imports can take a little time on shared hosting. Do not refresh or close the browser while this step is running.') }}
                            </div>
                        </div>
                    </div>

                    <div class="wizard-actions">
                        <span class="installing" data-installing>
                            <span class="spinner"></span>
                            {{ __('Installing... please keep this page open.') }}
                        </span>

                        <div class="action-group">
                            <button class="button is-secondary" type="button" data-prev-step hidden>
                                <span class="button-icon">&lt;</span>
                                <span>{{ __('Previous Step') }}</span>
                            </button>
                            <button class="button" type="button" data-next-step @disabled(! $status['can_install'])>
                                <span data-next-label>{{ __('Continue') }}</span>
                                <span class="button-icon">&gt;</span>
                            </button>
                            <button class="button" type="submit" data-install-button hidden @disabled(! $status['can_install'])>
                                <span class="button-icon">GO</span>
                                <span>{{ __('Start Installation') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script>
    'use strict';
        (function () {
            const shell = document.querySelector('.installer-shell');
            const canInstall = shell.dataset.canInstall === '1';
            const form = document.querySelector('[data-install-form]');
            const panels = Array.from(document.querySelectorAll('[data-step-panel]'));
            const triggers = Array.from(document.querySelectorAll('[data-step-trigger]'));
            const progressFill = document.querySelector('[data-progress-fill]');
            const progressLabel = document.querySelector('[data-progress-label]');
            const progressPercent = document.querySelector('[data-progress-percent]');
            const prevButton = document.querySelector('[data-prev-step]');
            const nextButton = document.querySelector('[data-next-step]');
            const nextLabel = document.querySelector('[data-next-label]');
            const installButton = document.querySelector('[data-install-button]');
            const installing = document.querySelector('[data-installing]');
            const installProgress = document.querySelector('[data-install-progress]');
            const installProgressFill = document.querySelector('[data-install-progress-fill]');
            const installProgressLabel = document.querySelector('[data-install-progress-label]');
            const installProgressPercent = document.querySelector('[data-install-progress-percent]');
            const installProgressSteps = Array.from(document.querySelectorAll('[data-install-progress-step]'));
            const connection = document.querySelector('[data-db-connection]');
            const mysqlFields = Array.from(document.querySelectorAll('[data-mysql-field]'));
            const database = document.querySelector('[data-db-database]');
            const dbWatchFields = Array.from(document.querySelectorAll('[data-db-watch], [data-db-connection]'));
            const testDbButton = document.querySelector('[data-test-db]');
            const dbActions = document.querySelector('[data-db-actions]');
            const dbResult = document.querySelector('[data-db-result]');
            const dbStatusText = document.querySelector('[data-db-status-text]');
            const reviewSources = Array.from(document.querySelectorAll('[data-review-source]'));
            const reviewMysqlRows = Array.from(document.querySelectorAll('[data-review-mysql]'));
            const reviewDbTest = document.querySelector('[data-review-db-test]');
            const adminPrefixField = document.querySelector('[data-admin-prefix]');
            const adminUrlBase = document.querySelector('[data-admin-url-base]');
            const adminUrlPreview = document.querySelector('[data-admin-url-preview]');
            const reviewAdminUrl = document.querySelector('[data-review-admin-url]');
            const currencySelect = document.querySelector('[data-currency-select]');
            const reviewCurrency = document.querySelector('[data-review-currency]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            let currentStep = 0;
            let maxUnlockedStep = canInstall ? 1 : 0;
            let databaseReady = false;
            let databaseMessage = @json(__('Not tested'));
            let installProgressValue = 8;
            const defaultAdminPrefix = @json($defaults['admin_prefix']);
            const nextLabels = [
                @json(__('Continue to Application')),
                @json(__('Continue to Database')),
                @json(__('Continue to Admin Account')),
                @json(__('Review Installation')),
                @json(__('Start Installation')),
            ];
            const installStageLabels = [
                @json(__('Writing environment file')),
                @json(__('Preparing database')),
                @json(__('Importing SQL file')),
                @json(__('Creating super admin')),
                @json(__('Finalizing setup')),
            ];

            function setDatabaseUntested() {
                databaseReady = false;
                databaseMessage = @json(__('Not tested'));
                dbStatusText.textContent = @json(__('Database has not been tested yet. Run the test before continuing.'));
                dbActions.className = 'db-actions';
                dbResult.className = 'db-result';
                dbResult.innerHTML = '';
                updateReview();
                updateControls();
            }

            function syncDatabaseFields() {
                const isSqlite = connection.value === 'sqlite';

                mysqlFields.forEach((field) => {
                    field.hidden = isSqlite;
                    field.querySelectorAll('input').forEach((input) => {
                        input.disabled = isSqlite;
                        input.required = ! isSqlite && ['db_host', 'db_port', 'db_username'].includes(input.name);
                    });
                });

                reviewMysqlRows.forEach((row) => {
                    row.hidden = isSqlite;
                });

                if (isSqlite && database.value === @json($defaults['db_database'])) {
                    database.value = 'database/database.sqlite';
                }

                updateReview();
            }

            function activeFields() {
                return Array.from(panels[currentStep].querySelectorAll('input, select')).filter((field) => ! field.disabled);
            }

            function validateCurrentFields() {
                for (const field of activeFields()) {
                    if (! field.checkValidity()) {
                        field.reportValidity();
                        field.focus();

                        return false;
                    }
                }

                return true;
            }

            function validateCurrentStep() {
                if (! validateCurrentFields()) {
                    return false;
                }

                if (currentStep === 2 && ! databaseReady) {
                    dbStatusText.textContent = @json(__('Please run a successful database test before continuing.'));
                    testDbButton.focus();

                    return false;
                }

                return true;
            }

            function setStep(step) {
                currentStep = Math.max(0, Math.min(step, panels.length - 1));

                panels.forEach((panel, index) => {
                    panel.classList.toggle('is-active', index === currentStep);
                });

                triggers.forEach((trigger, index) => {
                    trigger.setAttribute('aria-current', index === currentStep ? 'step' : 'false');
                    trigger.disabled = index > maxUnlockedStep;
                });

                const percent = Math.round(((currentStep + 1) / panels.length) * 100);
                progressFill.style.width = `${percent}%`;
                progressLabel.textContent = @json(__('Step :current of :total'))
                    .replace(':current', String(currentStep + 1))
                    .replace(':total', String(panels.length));
                progressPercent.textContent = `${percent}%`;

                updateReview();
                updateControls();
            }

            function updateControls() {
                prevButton.hidden = currentStep === 0;
                nextButton.hidden = currentStep === panels.length - 1;
                installButton.hidden = currentStep !== panels.length - 1;
                nextButton.disabled = ! canInstall
                    || (currentStep === 2 && ! databaseReady);
                installButton.disabled = ! canInstall;
                nextLabel.textContent = nextLabels[currentStep] || @json(__('Continue'));
            }

            function updateReview() {
                reviewSources.forEach((source) => {
                    const target = document.querySelector(`[data-review="${source.dataset.reviewSource}"]`);

                    if (target) {
                        target.textContent = source.value || '-';
                    }
                });

                reviewDbTest.textContent = databaseMessage;
                updateAdminUrlPreview();
                updateCurrencyReview();

                // Toggle the green "is-ok" tint on the top-of-review status
                // pills the moment the DB test passes.
                const dbStat = document.querySelector('[data-rv-stat="database"]');
                if (dbStat) {
                    dbStat.classList.toggle('is-ok', databaseReady === true);
                }
            }

            function buildAdminLoginUrl() {
                const base = (adminUrlBase?.value || '').trim().replace(/\/+$/, '');
                const rawPrefix = (adminPrefixField?.value || '').trim().replace(/^\/+|\/+$/g, '');
                const prefix = rawPrefix === '' ? defaultAdminPrefix : rawPrefix;

                if (base === '') {
                    return `/${prefix}/login`;
                }

                return `${base}/${prefix}/login`;
            }

            function updateAdminUrlPreview() {
                const url = buildAdminLoginUrl();

                if (adminUrlPreview) {
                    adminUrlPreview.textContent = url;
                }

                if (reviewAdminUrl) {
                    reviewAdminUrl.textContent = url;
                }
            }

            function updateCurrencyReview() {
                if (! reviewCurrency || ! currencySelect) {
                    return;
                }

                const option = currencySelect.options[currencySelect.selectedIndex];

                if (! option) {
                    reviewCurrency.textContent = '-';

                    return;
                }

                const name = option.dataset.currencyName || '';
                const symbol = option.dataset.currencySymbol || '';
                reviewCurrency.textContent = `${option.value} — ${name} (${symbol})`;
            }

            function setInstallProgress(value) {
                installProgressValue = Math.max(8, Math.min(value, 94));
                const percent = `${installProgressValue}%`;

                installProgressFill.style.width = percent;
                installProgressPercent.textContent = percent;
            }

            function setInstallStage(stage) {
                installProgressSteps.forEach((step, index) => {
                    step.classList.toggle('is-complete', index < stage);
                    step.classList.toggle('is-active', index === stage);
                });

                installProgressLabel.textContent = installStageLabels[stage] || @json(__('Finalizing setup'));
            }

            function startInstallProgress() {
                let stage = 0;
                let tick = 0;

                form.classList.add('is-installing');
                installProgress.setAttribute('aria-hidden', 'false');
                setInstallStage(stage);
                setInstallProgress(8);

                return window.setInterval(() => {
                    tick += 1;

                    if (tick % 3 === 0 && stage < installProgressSteps.length - 1) {
                        stage += 1;
                        setInstallStage(stage);
                    }

                    const nextValue = installProgressValue + (stage >= 2 ? 4 : 6);
                    setInstallProgress(nextValue);
                }, 850);
            }

            function iconForStatus(status) {
                if (status === 'success') {
                    return 'OK';
                }

                if (status === 'warning') {
                    return '!';
                }

                return '!';
            }

            function renderDatabaseResult(payload) {
                const status = payload.status || (payload.ok ? 'success' : 'error');
                let checks = Array.isArray(payload.checks) ? payload.checks : [];
                const guidance = Array.isArray(payload.guidance) ? payload.guidance : [];

                if (checks.length === 0 && payload.errors && typeof payload.errors === 'object') {
                    checks = Object.entries(payload.errors).map(([field, messages]) => ({
                        label: field.replaceAll('_', ' '),
                        status: 'error',
                        detail: Array.isArray(messages) ? messages.join(' ') : String(messages)
                    }));
                }

                dbResult.className = `db-result is-visible is-${status}`;
                dbActions.className = status === 'success' ? 'db-actions is-success' : (status === 'error' ? 'db-actions is-error' : 'db-actions');
                dbResult.innerHTML = '';

                const title = document.createElement('p');
                title.className = 'result-title';
                title.textContent = payload.message || @json(__('Database test completed.'));
                dbResult.appendChild(title);

                checks.forEach((check) => {
                    const row = document.createElement('div');
                    row.className = 'result-row';

                    const icon = document.createElement('span');
                    icon.className = `result-icon is-${check.status || status}`;
                    icon.textContent = iconForStatus(check.status || status);

                    const copy = document.createElement('span');
                    const label = document.createElement('span');
                    label.className = 'result-label';
                    label.textContent = check.label || '-';

                    const detail = document.createElement('span');
                    detail.className = 'result-detail';
                    detail.textContent = check.detail || '';

                    copy.appendChild(label);
                    copy.appendChild(detail);
                    row.appendChild(icon);
                    row.appendChild(copy);
                    dbResult.appendChild(row);
                });

                if (guidance.length > 0) {
                    const guide = document.createElement('div');
                    guide.className = 'guide';

                    const guideTitle = document.createElement('p');
                    guideTitle.className = 'guide-title';
                    guideTitle.textContent = @json(__('How to fix it'));

                    const list = document.createElement('ul');
                    guidance.forEach((item) => {
                        const entry = document.createElement('li');
                        entry.textContent = item;
                        list.appendChild(entry);
                    });

                    guide.appendChild(guideTitle);
                    guide.appendChild(list);
                    dbResult.appendChild(guide);
                }

                databaseReady = payload.ok === true;
                databaseMessage = payload.message || @json(__('Test completed'));
                dbStatusText.textContent = databaseReady
                    ? @json(__('Database test passed. You can continue.'))
                    : @json(__('Database needs attention before continuing.'));
                updateReview();
                updateControls();
            }

            async function testDatabase() {
                if (! validateCurrentFields()) {
                    return;
                }

                testDbButton.disabled = true;
                dbActions.className = 'db-actions';
                testDbButton.innerHTML = `<span class="spinner"></span><span>${@json(__('Checking...'))}</span>`;
                dbStatusText.textContent = @json(__('Testing database credentials and permissions...'));

                try {
                    const response = await fetch(@json(route('install.database.test')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });

                    const payload = await response.json();
                    renderDatabaseResult(payload);
                } catch (error) {
                    renderDatabaseResult({
                        ok: false,
                        status: 'error',
                        message: @json(__('Database test request failed. Check the web server and try again.')),
                        checks: []
                    });
                } finally {
                    testDbButton.disabled = false;
                    testDbButton.innerHTML = `<span class="button-icon">?</span><span>${@json(__('Check Database Connection'))}</span>`;
                }
            }

            nextButton.addEventListener('click', () => {
                if (! validateCurrentStep()) {
                    return;
                }

                maxUnlockedStep = Math.max(maxUnlockedStep, currentStep + 1);
                setStep(currentStep + 1);
            });

            prevButton.addEventListener('click', () => {
                setStep(currentStep - 1);
            });

            triggers.forEach((trigger, index) => {
                trigger.addEventListener('click', () => {
                    if (index <= maxUnlockedStep) {
                        setStep(index);
                    }
                });
            });

            dbWatchFields.forEach((field) => {
                field.addEventListener('input', setDatabaseUntested);
                field.addEventListener('change', () => {
                    syncDatabaseFields();
                    setDatabaseUntested();
                });
            });

            reviewSources.forEach((field) => {
                field.addEventListener('input', updateReview);
                field.addEventListener('change', updateReview);
            });

            testDbButton.addEventListener('click', testDatabase);

            form.addEventListener('submit', () => {
                startInstallProgress();
                installing.classList.add('is-visible');
                prevButton.disabled = true;
                nextButton.disabled = true;
                installButton.disabled = true;
                triggers.forEach((trigger) => {
                    trigger.disabled = true;
                });
                installButton.innerHTML = `<span class="spinner"></span><span>${@json(__('Installing...'))}</span>`;
            });

            syncDatabaseFields();

            const installErrorBanner = document.querySelector('[data-install-error-banner]');
            const firstError = document.querySelector('.error');

            if (installErrorBanner) {
                // The install failed mid-run (most likely a permission issue).
                // Keep the user on the Review step so the banner is visible
                // and they can retry after fixing permissions — never dump
                // them on Step 1 with no context.
                maxUnlockedStep = panels.length - 1;
                setStep(panels.length - 1);
                installErrorBanner.scrollIntoView({behavior: 'smooth', block: 'start'});
            } else if (firstError) {
                const errorPanel = firstError.closest('[data-step-panel]');

                if (errorPanel) {
                    const errorStep = Number(errorPanel.dataset.stepPanel);
                    maxUnlockedStep = Math.max(maxUnlockedStep, errorStep);
                    setStep(errorStep);
                }
            } else {
                setStep(0);
            }
        })();

        /* ── Password requirements: live indicators ─────────────────────
           Update the checklist in real time as the buyer types so they
           know exactly what's still missing before they hit "install". */
        (function () {
            const pwInput   = document.querySelector('[data-pw-check]');
            const pwConfirm = document.querySelector('[data-pw-confirm]');
            const rules     = document.querySelectorAll('[data-pw-rule]');
            if (! pwInput || rules.length === 0) {
                return;
            }

            const checks = {
                length:    (v)    => v.length >= 8,
                uppercase: (v)    => /[A-Z]/.test(v),
                lowercase: (v)    => /[a-z]/.test(v),
                number:    (v)    => /\d/.test(v),
                match:     (v, c) => v.length > 0 && v === c,
            };

            function refresh() {
                const v = pwInput.value;
                const c = pwConfirm ? pwConfirm.value : '';
                rules.forEach(function (li) {
                    const key  = li.dataset.pwRule;
                    const test = checks[key];
                    li.classList.toggle('is-met', test ? test(v, c) : false);
                });
            }

            pwInput.addEventListener('input', refresh);
            if (pwConfirm) { pwConfirm.addEventListener('input', refresh); }
            refresh();
        })();
    </script>
</body>
</html>
