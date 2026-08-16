@extends('frontend.layouts.app')

@section('content')
    @push('styles')
        <style>
            .demo-disclosure {
                padding: 140px 0 90px;
                background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
                color: #0f172a;
            }

            .demo-disclosure__container {
                max-width: 960px;
                margin: 0 auto;
                padding: 0 20px;
            }

            .demo-disclosure__eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 5px 12px;
                background: linear-gradient(135deg, #f59e0b, #f97316);
                color: #1a1300;
                font-weight: 800;
                font-size: 11px;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                border-radius: 999px;
                box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.45), 0 6px 16px -8px rgba(245, 158, 11, 0.45);
            }

            .demo-disclosure__title {
                margin: 18px 0 14px;
                font-size: clamp(28px, 4vw, 42px);
                font-weight: 700;
                line-height: 1.18;
                color: #0b1220;
            }

            .demo-disclosure__lede {
                font-size: 17px;
                line-height: 1.6;
                color: #334155;
                max-width: 720px;
            }

            .demo-disclosure__card {
                margin-top: 36px;
                padding: 28px 30px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                box-shadow: 0 30px 60px -40px rgba(15, 23, 42, 0.18);
            }

            .demo-disclosure__card + .demo-disclosure__card {
                margin-top: 22px;
            }

            .demo-disclosure__card-title {
                font-size: 18px;
                font-weight: 700;
                margin: 0 0 14px;
                color: #0b1220;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .demo-disclosure__card-title::before {
                content: '';
                width: 8px;
                height: 8px;
                border-radius: 999px;
                background: linear-gradient(135deg, #f59e0b, #f97316);
                box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.18);
            }

            .demo-disclosure__list {
                margin: 0;
                padding: 0;
                list-style: none;
                display: grid;
                gap: 10px;
            }

            .demo-disclosure__list li {
                position: relative;
                padding-left: 26px;
                line-height: 1.55;
                color: #334155;
            }

            .demo-disclosure__list li::before {
                content: '✓';
                position: absolute;
                left: 0;
                top: 0;
                width: 18px;
                height: 18px;
                border-radius: 5px;
                background: rgba(22, 163, 74, 0.12);
                color: #16a34a;
                font-weight: 700;
                font-size: 11px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .demo-disclosure__list--negative li::before {
                content: '✕';
                background: rgba(239, 68, 68, 0.1);
                color: #dc2626;
            }

            .demo-disclosure__meta {
                margin-top: 36px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
            }

            .demo-disclosure__meta-item {
                padding: 16px 18px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
            }

            .demo-disclosure__meta-label {
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 4px;
            }

            .demo-disclosure__meta-value {
                font-size: 15px;
                font-weight: 600;
                color: #0b1220;
                word-break: break-word;
            }

            .demo-disclosure__meta-value a {
                color: #b45309;
                text-decoration: underline;
                text-underline-offset: 2px;
            }

            .demo-disclosure__meta-value a:hover {
                color: #92400e;
            }

            .demo-disclosure__cta-row {
                margin-top: 36px;
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            .demo-disclosure__cta {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 11px 22px;
                border-radius: 999px;
                font-weight: 600;
                font-size: 14px;
                text-decoration: none;
                transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            }

            .demo-disclosure__cta--primary {
                background: linear-gradient(135deg, #0b1220, #1e293b);
                color: #f8fafc;
                box-shadow: 0 12px 24px -12px rgba(15, 23, 42, 0.45);
            }

            .demo-disclosure__cta--primary:hover {
                color: #fde68a;
                transform: translateY(-1px);
            }

            .demo-disclosure__cta--secondary {
                background: #ffffff;
                color: #0b1220;
                border: 1px solid #cbd5e1;
            }

            .demo-disclosure__cta--secondary:hover {
                background: #f1f5f9;
                color: #0b1220;
            }

            .demo-disclosure__footnote {
                margin-top: 30px;
                padding-top: 22px;
                border-top: 1px dashed #cbd5e1;
                font-size: 12.5px;
                color: #64748b;
                line-height: 1.6;
            }

            .demo-disclosure__status {
                margin-top: 18px;
                padding: 12px 16px;
                border-radius: 10px;
                font-size: 13px;
                line-height: 1.5;
            }

            .demo-disclosure__status--on {
                background: rgba(22, 163, 74, 0.08);
                border: 1px solid rgba(22, 163, 74, 0.25);
                color: #15803d;
            }

            .demo-disclosure__status--off {
                background: rgba(100, 116, 139, 0.08);
                border: 1px solid rgba(100, 116, 139, 0.25);
                color: #475569;
            }

            @media (max-width: 575px) {
                .demo-disclosure {
                    padding: 110px 0 60px;
                }

                .demo-disclosure__card {
                    padding: 22px 20px;
                }
            }
        </style>
    @endpush

    <section class="demo-disclosure">
        <div class="demo-disclosure__container">
            <span class="demo-disclosure__eyebrow">{{ __('Software Demo Disclosure') }}</span>

            <h1 class="demo-disclosure__title">
                {{ __('This site is a software product demo, not a financial service.') }}
            </h1>

            <p class="demo-disclosure__lede">
                {!! __('This domain hosts a publicly accessible demonstration of the :product software product, operated by the software vendor :vendor solely so that prospective business customers can evaluate the product before purchase. No real financial activity takes place here.', [
                    'product' => '<strong>'.e($productName).'</strong>',
                    'vendor'  => $vendorUrl !== ''
                        ? '<a href="'.e($vendorUrl).'" target="_blank" rel="noopener noreferrer"><strong>'.e($vendorName).'</strong></a>'
                        : '<strong>'.e($vendorName).'</strong>',
                ]) !!}
            </p>

            @if($isDemo)
                <div class="demo-disclosure__status demo-disclosure__status--on">
                    <strong>{{ __('Demo mode is currently active.') }}</strong>
                    {{ __('All accounts, balances, transactions, and payment-gateway credentials shown on this site are seeded test data.') }}
                </div>
            @else
                <div class="demo-disclosure__status demo-disclosure__status--off">
                    <strong>{{ __('Demo mode is currently disabled on this installation.') }}</strong>
                    {{ __('This disclosure URL is retained so external trust-and-safety scanners and previously notified reviewers can always resolve an authoritative statement about the domain.') }}
                </div>
            @endif

            <div class="demo-disclosure__card">
                <h2 class="demo-disclosure__card-title">{{ __('What this site is') }}</h2>
                <ul class="demo-disclosure__list">
                    <li>{{ __('A public, evaluation-only demonstration installation of a commercial Laravel-based digital-wallet software product.') }}</li>
                    <li>{{ __('A vendor-operated showcase used by prospective B2B customers to assess the user interface, admin panel, and modules before purchasing a license.') }}</li>
                    <li>{{ __('A read-only sandbox seeded with fictitious accounts, balances, and transactions for demonstration only.') }}</li>
                </ul>
            </div>

            <div class="demo-disclosure__card">
                <h2 class="demo-disclosure__card-title">{{ __('What this site is NOT') }}</h2>
                <ul class="demo-disclosure__list demo-disclosure__list--negative">
                    <li>{{ __('Not a regulated or licensed financial service.') }}</li>
                    <li>{{ __('Not a cryptocurrency investment platform; no investment returns, ROI, profit-sharing, or interest are offered, promised, or paid.') }}</li>
                    <li>{{ __('Not a deposit-taking entity; no real customer funds are accepted, held, or transmitted on this domain.') }}</li>
                    <li>{{ __('Not affiliated with any celebrity, news outlet, exchange, or third-party brand. Any displayed logo or partner reference belongs solely to its respective owner and is used only as illustrative demo content.') }}</li>
                </ul>
            </div>

            <div class="demo-disclosure__card">
                <h2 class="demo-disclosure__card-title">{{ __('Vendor & product information') }}</h2>

                <div class="demo-disclosure__meta">
                    <div class="demo-disclosure__meta-item">
                        <div class="demo-disclosure__meta-label">{{ __('Software vendor') }}</div>
                        <div class="demo-disclosure__meta-value">
                            @if($vendorUrl !== '')
                                <a href="{{ $vendorUrl }}" target="_blank" rel="noopener noreferrer">{{ $vendorName }}</a>
                            @else
                                {{ $vendorName }}
                            @endif
                        </div>
                    </div>

                    <div class="demo-disclosure__meta-item">
                        <div class="demo-disclosure__meta-label">{{ __('Product name') }}</div>
                        <div class="demo-disclosure__meta-value">{{ $productName }}</div>
                    </div>

                    @if($salesUrl !== '')
                        <div class="demo-disclosure__meta-item">
                            <div class="demo-disclosure__meta-label">{{ __('Product sales page') }}</div>
                            <div class="demo-disclosure__meta-value">
                                <a href="{{ $salesUrl }}" target="_blank" rel="noopener noreferrer">{{ __('View on marketplace') }}</a>
                            </div>
                        </div>
                    @endif

                    @if($supportEmail !== '')
                        <div class="demo-disclosure__meta-item">
                            <div class="demo-disclosure__meta-label">{{ __('Vendor contact') }}</div>
                            <div class="demo-disclosure__meta-value">
                                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="demo-disclosure__cta-row">
                @if($salesUrl !== '')
                    <a href="{{ $salesUrl }}" class="demo-disclosure__cta demo-disclosure__cta--primary" target="_blank" rel="noopener noreferrer">
                        {{ __('Visit the product page') }} &rarr;
                    </a>
                @endif
                @if($supportEmail !== '')
                    <a href="mailto:{{ $supportEmail }}" class="demo-disclosure__cta demo-disclosure__cta--secondary">
                        {{ __('Contact the vendor') }}
                    </a>
                @endif
            </div>

            <p class="demo-disclosure__footnote">
                {{ __('Trust-and-safety contacts: if you are a hosting provider, brand-protection service, or law-enforcement representative reviewing this domain, please contact the vendor at the address above so we can promptly respond with any clarifications, codebase access, or documentation you require.') }}
            </p>
        </div>
    </section>

    @push('scripts')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type'    => 'WebPage',
                'name'     => 'Software Demo Disclosure',
                'url'      => $canonicalUrl,
                'description' => 'Authoritative disclosure stating this domain is a software product demo, not a financial service or cryptocurrency investment platform.',
                'publisher' => array_filter([
                    '@type' => 'Organization',
                    'name'  => $vendorName,
                    'url'   => $vendorUrl ?: null,
                ]),
                'about' => array_filter([
                    '@type' => 'SoftwareApplication',
                    'name'  => $productName,
                    'url'   => $salesUrl ?: null,
                ]),
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
@endsection
