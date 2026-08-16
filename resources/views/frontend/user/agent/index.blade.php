@extends('frontend.layouts.user.index')
@section('title', __('Agent Services'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('content')
    @php
        $agentAccount = $agents->first();
        $hasAgentAccount = $agentAccount !== null;
        $approvedAgents = $dashboard['approvedAgents'] ?? collect();
        $wallets = $dashboard['wallets'] ?? collect();
        $stats = $dashboard['stats'] ?? [];
        $recentOperations = $dashboard['recentOperations'] ?? collect();
        $recentCommissions = $dashboard['recentCommissions'] ?? collect();
        $pendingQrCashOuts = $dashboard['pendingQrCashOuts'] ?? collect();
        $recentCashOutPreview = $pendingQrCashOuts->take(3);
        $primaryApprovedAgent = $approvedAgents->first();
        $supportedCurrencyIds = $primaryApprovedAgent?->supportedCurrencyIds() ?? [];
        $operationWallets = $primaryApprovedAgent
            ? $wallets->whereIn('currency_id', $supportedCurrencyIds)->values()
            : collect();
        $canProcessOperations = $approvedAgents->isNotEmpty() && $operationWallets->isNotEmpty();
        $requestedTab = (string) request('tab', 'overview');
        $requestedTab = match ($requestedTab) {
            'cash-out' => 'cash-out-requests',
            'my-qr' => 'counter-cashout',
            'settings' => 'overview',
            default => $requestedTab,
        };
        $availableTabs = ['overview', 'cash-in', 'counter-cashout', 'cash-out-requests', 'transactions'];
        $activeTab = in_array($requestedTab, $availableTabs, true) ? $requestedTab : 'overview';
        $agentTabs = [
            'overview' => ['label' => __('Overview'), 'icon' => 'fa-home'],
            'cash-in' => ['label' => __('Cash-In'), 'icon' => 'fa-wallet'],
            'counter-cashout' => ['label' => __('QR Cash-Out'), 'icon' => 'fa-qrcode'],
            'cash-out-requests' => ['label' => __('Cash-Out Queue'), 'icon' => 'fa-list-check'],
            'transactions' => ['label' => __('Transactions'), 'icon' => 'fa-receipt'],
        ];

        $statusMeta = match ($agentAccount?->status) {
            \App\Enums\AgentStatus::APPROVED => [
                'tone' => 'success',
                'icon' => 'fa-circle-check',
                'eyebrow' => __('Ready to operate'),
                'message' => __('Your agent account is approved. QR cash-out and counter services are active.'),
            ],
            \App\Enums\AgentStatus::PENDING => [
                'tone' => 'info',
                'icon' => 'fa-hourglass-half',
                'eyebrow' => __('Under review'),
                'message' => __('Your application is waiting for admin review. Operation tools unlock after approval.'),
            ],
            \App\Enums\AgentStatus::DISABLED => [
                'tone' => 'warning',
                'icon' => 'fa-ban',
                'eyebrow' => __('Temporarily disabled'),
                'message' => __('This agent account is paused by admin. New cash operations are not available.'),
            ],
            \App\Enums\AgentStatus::REJECTED => [
                'tone' => 'danger',
                'icon' => 'fa-circle-xmark',
                'eyebrow' => __('Application rejected'),
                'message' => __('This application was not approved. Contact support if you need a review.'),
            ],
            default => [
                'tone' => 'neutral',
                'icon' => 'fa-user-tie',
                'eyebrow' => __('Agent account'),
                'message' => __('Apply for your agent account to start counter services.'),
            ],
        };
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card single-form-card agent-service-card">
                <x-user-feature-header
                    :title="__('Agent Services')"
                    :subtitle="__('Manage cash-in, QR cash-out, handover queue, and agent transactions.')"
                    icon="fas fa-user-tie"
                >
                    @if(! $hasAgentAccount)
                        <a class="btn btn-light-agent btn-sm" href="{{ route('user.agent.create') }}">
                            <i class="fas fa-plus-circle"></i> {{ __('Apply for Agent') }}
                        </a>
                    @elseif(! $agentAccount->isActionLocked())
                        <a class="btn btn-counter-account-edit btn-sm" href="{{ route('user.agent.edit', $agentAccount->id) }}">
                            <i class="fas fa-edit"></i> {{ __('Edit Counter Account') }}
                        </a>
                    @endif
                </x-user-feature-header>

                <div class="card-body">
                    @if($hasAgentAccount)
                        <details class="agent-command-panel agent-command-panel--collapsible agent-command-panel--{{ $statusMeta['tone'] }} mb-3">
                            <summary class="agent-command-panel__summary">
                                <div class="agent-command-panel__identity">
                                    <img src="{{ asset($agentAccount->logo) }}" alt="{{ $agentAccount->agent_name }}" class="agent-command-panel__logo" loading="lazy">
                                    <div class="min-w-0">
                                        <span class="agent-command-panel__eyebrow">
                                            <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
                                            {{ $statusMeta['eyebrow'] }}
                                        </span>
                                        <h2>{{ $agentAccount->agent_name }}</h2>
                                        <p>{{ $statusMeta['message'] }}</p>
                                    </div>
                                </div>
                                <span class="agent-command-panel__toggle">
                                    <span class="agent-command-panel__toggle-label agent-command-panel__toggle-label--closed">{{ __('Details') }}</span>
                                    <span class="agent-command-panel__toggle-label agent-command-panel__toggle-label--open">{{ __('Hide') }}</span>
                                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                </span>
                            </summary>

                            <div class="agent-command-panel__facts">
                                <div>
                                    <span>{{ __('Agent Code') }}</span>
                                    <strong>{{ $agentAccount->agent_code }}</strong>
                                </div>
                                <div>
                                    <span>{{ __('Currencies') }}</span>
                                    <strong>{{ $agentAccount->supportedCurrencies->pluck('code')->implode(', ') ?: $agentAccount->currency?->code }}</strong>
                                </div>
                                <div>
                                    <span>{{ __('QR Cash-Out') }}</span>
                                    <strong>{{ (int) ($stats['pending_qr_cash_out'] ?? 0) }} {{ __('pending') }}</strong>
                                </div>
                                <div>
                                    <span>{{ __('Status') }}</span>
                                    <strong>{{ $agentAccount->status->label() }}</strong>
                                </div>
                            </div>
                        </details>
                    @else
                        <div class="agent-command-panel agent-command-panel--neutral mb-3">
                            <div class="agent-command-panel__identity">
                                <span class="agent-command-panel__empty-icon"><i class="fa-solid fa-user-tie"></i></span>
                                <div class="min-w-0">
                                    <span class="agent-command-panel__eyebrow">{{ __('Agent account') }}</span>
                                    <h2>{{ __('Apply once, operate after approval') }}</h2>
                                    <p>{{ __('Your counter services are prepared for one account that can support multiple wallet currencies after approval.') }}</p>
                                </div>
                            </div>
                            <a class="btn btn-agent" href="{{ route('user.agent.create') }}">
                                <i class="fa-solid fa-plus me-1"></i>{{ __('Apply for Agent') }}
                            </a>
                        </div>
                    @endif

                    <nav class="agent-tool-tabs" aria-label="{{ __('Counter services') }}">
                        @foreach($agentTabs as $key => $tab)
                            <a href="{{ route('user.agent.index', ['tab' => $key]) }}" class="agent-tool-tab @if($activeTab === $key) is-active @endif">
                                <i class="fa-solid {{ $tab['icon'] }}"></i>
                                <span>{{ $tab['label'] }}</span>
                                @if($key === 'cash-out-requests' && (int) ($stats['pending_qr_cash_out'] ?? 0) > 0)
                                    <strong>{{ (int) $stats['pending_qr_cash_out'] }}</strong>
                                @endif
                            </a>
                        @endforeach
                    </nav>

                    <div class="agent-tool-pane">
                        @if($activeTab === 'overview')
                            <div class="agent-console agent-console--overview">
                                <div class="agent-overview-grid">
                                    <section class="agent-overview-panel agent-overview-panel--snapshot">
                                        <div class="agent-overview-panel__head">
                                            <span class="agent-overview-panel__icon">
                                                <i class="fas fa-chart-line" aria-hidden="true"></i>
                                            </span>
                                            <div>
                                                <h3>{{ __("Today's Counter Snapshot") }}</h3>
                                                <p>{{ __('Completed cash-in, cash-out, commission, and served customers for today only.') }}</p>
                                            </div>
                                            <a href="{{ route('user.agent.index', ['tab' => 'transactions']) }}" class="agent-overview-link">
                                                <i class="fa-solid fa-receipt"></i>{{ __('Ledger') }}
                                            </a>
                                        </div>

                                        <div class="agent-overview-kpis">
                                            <div class="agent-overview-kpi">
                                                <span>{{ __('Cash-In Today') }}</span>
                                                <strong>{{ formatCurrency((float) ($stats['today_cash_in'] ?? 0)) }}</strong>
                                            </div>
                                            <div class="agent-overview-kpi">
                                                <span>{{ __('Cash-Out Today') }}</span>
                                                <strong>{{ formatCurrency((float) ($stats['today_cash_out'] ?? 0)) }}</strong>
                                            </div>
                                            <div class="agent-overview-kpi">
                                                <span>{{ __('Commission Today') }}</span>
                                                <strong>{{ formatCurrency((float) ($stats['today_commission'] ?? 0)) }}</strong>
                                            </div>
                                            <div class="agent-overview-kpi">
                                                <span>{{ __('Customers Served') }}</span>
                                                <strong>{{ (int) ($stats['today_customers'] ?? 0) }}</strong>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="agent-overview-panel agent-overview-panel--requests">
                                        <div class="agent-overview-panel__head">
                                            <span class="agent-overview-panel__icon">
                                                <i class="fa-solid fa-list-check"></i>
                                            </span>
                                            <div>
                                                <h3>{{ __('Recent Cash-Out Queue') }}</h3>
                                                <p>{{ __('Latest QR requests waiting for cash handover.') }}</p>
                                            </div>
                                            <a href="{{ route('user.agent.index', ['tab' => 'cash-out-requests']) }}" class="agent-overview-link">
                                                <i class="fa-solid fa-arrow-right"></i>{{ __('Open Queue') }}
                                            </a>
                                        </div>

                                        <div class="agent-overview-request-list">
                                            @forelse($recentCashOutPreview as $cashOutRequest)
                                                <div class="agent-overview-request-row">
                                                    <span class="agent-overview-request-row__icon">
                                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                                    </span>
                                                    <div class="agent-overview-request-row__main">
                                                        <strong>{{ $cashOutRequest->customer?->name ?? __('Customer') }}</strong>
                                                        <span>{{ $cashOutRequest->reference }} &middot; {{ $cashOutRequest->created_at?->diffForHumans() }}</span>
                                                    </div>
                                                    <div class="agent-overview-request-row__amount">
                                                        <strong>{{ getSymbol($cashOutRequest->currency?->code) }}{{ number_format($cashOutRequest->amount, (int) setting('site_decimal', 2)) }}</strong>
                                                        <span>{{ $cashOutRequest->currency?->code }}</span>
                                                    </div>
                                                </div>
                                            @empty
                                                <x-user-not-found
                                                    :title="__('No recent cash-out requests')"
                                                    :message="__('New QR cash-out requests that need cash handover will appear here.')"
                                                    icon="fa-qrcode"
                                                />
                                            @endforelse
                                        </div>
                                    </section>
                                </div>
                            </div>
                        @endif

                        @if($activeTab === 'cash-in')
                            @if($approvedAgents->isNotEmpty())
                                <div class="agent-action-panel agent-action-panel--cash-in">
                                    <div class="agent-action-panel__head">
                                        <span class="agent-action-icon"><i class="fa-solid fa-wallet"></i></span>
                                        <div>
                                            <h3>{{ __('Cash-In') }}</h3>
                                                    <p>{{ __('Customer gives cash to your counter; you top up their wallet balance.') }}</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('user.agent.cash-in') }}" method="POST" class="agent-action-form">
                                        @csrf
                                        @include('frontend.user.agent.partials._operation_fields', [
                                            'formId' => 'cash_in',
                                            'agents' => $approvedAgents,
                                            'wallets' => $operationWallets,
                                        ])
                                        <button type="submit" class="btn btn-agent w-100 mt-3" @disabled(! $canProcessOperations)>
                                            <i class="fa-solid fa-check me-1"></i>{{ __('Complete Cash-In') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="agent-pending-callout agent-pending-callout--{{ $statusMeta['tone'] }}">
                                    <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
                                    <div>
                                        <strong>{{ $statusMeta['eyebrow'] }}</strong>
                                        <span>{{ $statusMeta['message'] }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($activeTab === 'counter-cashout')
                            @if($primaryApprovedAgent)
                                @php
                                    $qrPosterFilename = \Illuminate\Support\Str::slug($primaryApprovedAgent->agent_code ?: $primaryApprovedAgent->agent_name).'-cash-out-qr-poster.svg';
                                    $qrPosterCurrencies = $primaryApprovedAgent->supportedCurrencies->pluck('code')->implode(', ') ?: $primaryApprovedAgent->currency?->code;
                                    $qrPosterInstruction = __('Scan this QR, confirm the amount with Wallet PIN, then show the reference and collect cash.');
                                    $regenerateQrModalId = 'regenerateAgentQrModal'.$primaryApprovedAgent->id;
                                @endphp
                                <section class="agent-print-qr-card agent-print-qr-card--merged">
                                    <div
                                        id="agentPublicQrPoster"
                                        class="agent-qr-poster"
                                        data-agent-name="{{ $primaryApprovedAgent->agent_name }}"
                                        data-agent-code="{{ $primaryApprovedAgent->agent_code }}"
                                        data-agent-currencies="{{ $qrPosterCurrencies }}"
                                        data-agent-instruction="{{ $qrPosterInstruction }}"
                                    >
                                        <div class="agent-qr-poster__top">
                                            <span>{{ __('Counter Cash-Out QR') }}</span>
                                            <strong>{{ __('Scan Here') }}</strong>
                                        </div>

                                        <div class="agent-qr-poster__code">
                                            {!! $primaryApprovedAgent->qrCashOutSvg(292) !!}
                                        </div>

                                        <div class="agent-qr-poster__identity">
                                            <strong>{{ $primaryApprovedAgent->agent_name }}</strong>
                                            <span>{{ $primaryApprovedAgent->agent_code }} &middot; {{ $qrPosterCurrencies }}</span>
                                        </div>

                                        <div class="agent-qr-poster__steps">
                                            <span><b>1</b>{{ __('Scan QR') }}</span>
                                            <span><b>2</b>{{ __('Confirm amount') }}</span>
                                            <span><b>3</b>{{ __('Collect cash') }}</span>
                                        </div>

                                        <div class="agent-qr-poster__assurance">
                                            <strong>{{ __('Wallet PIN Required') }}</strong>
                                            <span>{{ __('Reference appears after confirmation') }}</span>
                                        </div>
                                    </div>

                                    <div class="agent-print-qr-card__details">
                                        <span>{{ __('Counter Cash-Out QR') }}</span>
                                        <h3>{{ __('Print once and keep it visible at the counter') }}</h3>
                                        <p>{{ __('Customer scans this QR, confirms the amount with Wallet PIN, then you pay cash after matching the reference.') }}</p>
                                        <div class="agent-qr-poster-info">
                                            <div>
                                                <span>{{ __('Counter') }}</span>
                                                <strong>{{ $primaryApprovedAgent->agent_code }}</strong>
                                            </div>
                                            <div>
                                                <span>{{ __('Wallets') }}</span>
                                                <strong>{{ $qrPosterCurrencies }}</strong>
                                            </div>
                                            <div>
                                                <span>{{ __('Security') }}</span>
                                                <strong>{{ __('PIN + Reference') }}</strong>
                                            </div>
                                        </div>
                                        <div class="agent-print-qr-card__actions">
                                            <button type="button" class="btn btn-agent" onclick="printAgentQrPoster('agentPublicQrPoster')">
                                                <i class="fa-solid fa-print me-1"></i>{{ __('Print QR Poster') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-agent" onclick="downloadAgentQrPosterSvg('agentPublicQrPoster', '{{ $qrPosterFilename }}')">
                                                <i class="fa-solid fa-download me-1"></i>{{ __('Download QR Poster') }}
                                            </button>
                                            <button type="button" class="btn btn-light-agent" data-bs-toggle="modal" data-bs-target="#{{ $regenerateQrModalId }}">
                                                <i class="fas fa-sync-alt me-1" aria-hidden="true"></i>{{ __('Regenerate QR') }}
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <div class="modal fade agent-qr-regenerate-modal" id="{{ $regenerateQrModalId }}" tabindex="-1" aria-labelledby="{{ $regenerateQrModalId }}Label" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('user.agent.regenerate-qr', $primaryApprovedAgent) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <div class="agent-qr-regenerate-modal__title">
                                                        <span><i class="fas fa-exclamation-circle" aria-hidden="true"></i></span>
                                                        <div>
                                                            <strong id="{{ $regenerateQrModalId }}Label">{{ __('Regenerate counter QR?') }}</strong>
                                                            <small>{{ __('This changes the secure QR token for your counter cash-out page.') }}</small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="agent-qr-regenerate-warning">
                                                        <strong>{{ __('Important before you continue') }}</strong>
                                                        <span>{{ __('After regeneration, every previously printed QR poster and downloaded QR file will stop working. Customers who scan the old QR will not be able to start cash-out from that code.') }}</span>
                                                    </div>
                                                    <div class="agent-qr-regenerate-checklist">
                                                        <div>
                                                            <i class="fas fa-print" aria-hidden="true"></i>
                                                            <span>{{ __('Print the new QR poster and replace the old counter copy.') }}</span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-qrcode" aria-hidden="true"></i>
                                                            <span>{{ __('Remove old QR stickers, saved SVG files, and shared images.') }}</span>
                                                        </div>
                                                        <div>
                                                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                                                            <span>{{ __('Only regenerate when the old QR is lost, exposed, damaged, or needs rotation for security.') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-agent" data-bs-dismiss="modal">{{ __('Keep Current QR') }}</button>
                                                    <button type="submit" class="btn btn-agent">
                                                        <i class="fas fa-sync-alt me-1" aria-hidden="true"></i>{{ __('Regenerate and Replace QR') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="agent-action-panel agent-action-panel--cash-out mt-3">
                                    <div class="agent-action-panel__head">
                                        <span class="agent-action-icon"><i class="fa-solid fa-keyboard"></i></span>
                                        <div>
                                            <h3>{{ __('Assisted Cash-Out') }}</h3>
                                            <p>{{ __('Send an OTP to the customer, then enter the code to complete counter cash-out.') }}</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('user.agent.cash-out') }}" method="POST" class="agent-action-form">
                                        @csrf
                                        @include('frontend.user.agent.partials._operation_fields', [
                                            'formId' => 'cash_out',
                                            'agents' => $approvedAgents,
                                            'wallets' => $operationWallets,
                                            'requiresCustomerOtp' => true,
                                        ])
                                        <div class="agent-otp-actions">
                                            <button type="submit" class="btn btn-light-agent" formaction="{{ route('user.agent.cash-out.otp') }}" formnovalidate @disabled(! $canProcessOperations)>
                                                <i class="fa-solid fa-paper-plane me-1"></i>{{ __('Send Customer OTP') }}
                                            </button>
                                            <button type="submit" class="btn btn-agent" @disabled(! $canProcessOperations)>
                                                <i class="fa-solid fa-check me-1"></i>{{ __('Complete Assisted Cash-Out') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="agent-pending-callout agent-pending-callout--{{ $statusMeta['tone'] }}">
                                    <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
                                    <div>
                                        <strong>{{ $statusMeta['eyebrow'] }}</strong>
                                        <span>{{ $statusMeta['message'] }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($activeTab === 'cash-out-requests')
                            @if($primaryApprovedAgent)
                                <div class="agent-console">
                                    @include('frontend.user.agent.partials._pending_qr_cash_outs', ['pendingQrCashOuts' => $pendingQrCashOuts])
                                </div>
                            @else
                                <div class="agent-pending-callout agent-pending-callout--{{ $statusMeta['tone'] }}">
                                    <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
                                    <div>
                                        <strong>{{ $statusMeta['eyebrow'] }}</strong>
                                        <span>{{ $statusMeta['message'] }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($activeTab === 'transactions')
                            <div class="agent-ledger-grid">
                                <div class="agent-ledger-panel">
                                    <div class="agent-ledger-panel__head">
                                        <h3>{{ __('Operation Ledger') }}</h3>
                                        <span>{{ $recentOperations->count() }}</span>
                                    </div>
                                    @forelse($recentOperations as $operation)
                                        <div class="agent-ledger-row">
                                            <div class="agent-ledger-row__icon bg-{{ $operation->type->color() }}">
                                                <i class="fa-solid {{ $operation->type->icon() }}"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $operation->type->label() }}</strong>
                                                <span>{{ $operation->customer?->name }} &middot; {{ $operation->reference }}</span>
                                            </div>
                                            <div class="text-end">
                                                <strong>{{ getSymbol($operation->currency?->code) }}{{ number_format($operation->amount, (int) setting('site_decimal', 2)) }}</strong>
                                                <span>{{ $operation->created_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <x-user-not-found
                                            :title="__('No operations yet')"
                                            :message="__('Completed cash-in and cash-out records will appear here.')"
                                            icon="fa-receipt"
                                        />
                                    @endforelse
                                </div>

                                <div class="agent-ledger-panel">
                                    <div class="agent-ledger-panel__head">
                                        <h3>{{ __('Commission Ledger') }}</h3>
                                        <span>{{ $recentCommissions->count() }}</span>
                                    </div>
                                    @forelse($recentCommissions as $commission)
                                        @php
                                            $commissionData = $commission->trx_data['commission'] ?? [];
                                            $commissionRate = $commissionData['percentage_rate'] ?? null;
                                            $commissionSource = str_replace('_', ' ', (string) ($commissionData['source'] ?? 'commission'));
                                        @endphp
                                        <div class="agent-ledger-row">
                                            <div class="agent-ledger-row__icon bg-success">
                                                <i class="fa-solid fa-percent"></i>
                                            </div>
                                            <div>
                                                <strong>
                                                    @if($commissionRate !== null && (float) $commissionRate > 0)
                                                        {{ number_format((float) $commissionRate, 2) }}%
                                                    @else
                                                        {{ __('Fixed') }}
                                                    @endif
                                                </strong>
                                                <span>{{ title($commissionSource) }} &middot; {{ $commission->trx_data['agent_reference'] ?? $commission->trx_reference }}</span>
                                            </div>
                                            <div class="text-end">
                                                <strong>{{ getSymbol($commission->payable_currency ?? $commission->currency) }}{{ number_format($commission->amount, (int) setting('site_decimal', 2)) }}</strong>
                                                <span>{{ $commission->created_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <x-user-not-found
                                            :title="__('No commission yet')"
                                            :message="__('Commission entries are created after successful agent operations.')"
                                            icon="fa-percent"
                                        />
                                    @endforelse
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('general._qr_code')
    <script>
        "use strict";

        const agentQrPosterLabels = {{ \Illuminate\Support\Js::from([
            'counterCashOutQr' => __('Counter Cash-Out QR'),
            'scanToRequest' => __('Scan to request cash-out'),
            'agentCounter' => __('Agent Counter'),
            'agentCode' => __('Agent Code'),
            'supportedWallets' => __('Supported Wallets'),
            'howItWorks' => __('How it works'),
            'scanQr' => __('Scan QR'),
            'confirmAmount' => __('Confirm amount with Wallet PIN'),
            'collectCash' => __('Show reference and collect cash'),
            'walletPinRequired' => __('Wallet PIN required'),
            'referenceAfterConfirm' => __('Reference appears after confirmation'),
            'verifiedCounter' => __('Verified counter poster'),
            'approvedCounterPoint' => __('Approved counter payment point'),
            'officialCashDesk' => __('Official cash desk'),
            'printTitle' => __('Print Counter Cash-Out QR'),
        ]) }};

        function printAgentQrPoster(elementId) {
            const poster = document.getElementById(elementId);

            if (! poster) {
                return;
            }

            const printWindow = window.open('', '_blank', 'width=920,height=760');

            if (! printWindow) {
                window.print();
                return;
            }

            printWindow.document.open();
            printWindow.document.write(`
                <!doctype html>
                <html>
                    <head>
                        <title>${agentQrPosterLabels.printTitle}</title>
                        <style>
                            * { box-sizing: border-box; }
                            body {
                                min-height: 100vh;
                                display: grid;
                                place-items: center;
                                margin: 0;
                                padding: 28px;
                                color: #111827;
                                background: #f5f7ff;
                                font-family: Arial, sans-serif;
                            }
                            .agent-qr-poster {
                                width: min(100%, 430px);
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                gap: 14px;
                                padding: 22px;
                                border: 1px solid #dcd7ff;
                                border-radius: 18px;
                                background: #ffffff;
                                box-shadow: none;
                                text-align: center;
                            }
                            .agent-qr-poster__top {
                                width: 100%;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                gap: 12px;
                                padding-bottom: 12px;
                                border-bottom: 1px solid #ecebff;
                            }
                            .agent-qr-poster__top span {
                                color: #5f3bd7;
                                font-size: 11px;
                                font-weight: 800;
                                text-transform: uppercase;
                            }
                            .agent-qr-poster__top strong {
                                color: #111827;
                                font-size: 14px;
                                font-weight: 800;
                            }
                            .agent-qr-poster__code {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                width: 318px;
                                height: 318px;
                                padding: 12px;
                                border: 1px dashed #d4ccff;
                                border-radius: 16px;
                                background: #ffffff;
                            }
                            .agent-qr-poster__code svg {
                                width: 100%;
                                height: auto;
                            }
                            .agent-qr-poster__identity strong,
                            .agent-qr-poster__identity span,
                            .agent-qr-poster__assurance strong,
                            .agent-qr-poster__assurance span {
                                display: block;
                            }
                            .agent-qr-poster__identity strong {
                                color: #111827;
                                font-size: 19px;
                                font-weight: 800;
                                line-height: 1.25;
                            }
                            .agent-qr-poster__identity span {
                                color: #667085;
                                font-size: 12px;
                                margin-top: 4px;
                            }
                            .agent-qr-poster__steps {
                                width: 100%;
                                display: grid;
                                grid-template-columns: repeat(3, 1fr);
                                gap: 7px;
                            }
                            .agent-qr-poster__steps span {
                                min-height: 42px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 5px;
                                padding: 6px;
                                border: 1px solid #ecebff;
                                border-radius: 12px;
                                color: #667085;
                                background: #f8f8ff;
                                font-size: 11px;
                                font-weight: 800;
                            }
                            .agent-qr-poster__steps b {
                                width: 19px;
                                height: 19px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                flex: 0 0 19px;
                                border-radius: 50%;
                                color: #ffffff;
                                background: #5f3bd7;
                                font-size: 10px;
                            }
                            .agent-qr-poster__assurance {
                                width: 100%;
                                padding: 12px 14px;
                                border: 1px solid #ecebff;
                                border-radius: 12px;
                                background: linear-gradient(135deg, #f8f8ff 0%, #ffffff 100%);
                            }
                            .agent-qr-poster__assurance strong {
                                color: #5f3bd7;
                                font-size: 12px;
                                font-weight: 800;
                                text-transform: uppercase;
                            }
                            .agent-qr-poster__assurance span {
                                color: #667085;
                                font-size: 11px;
                                font-weight: 700;
                                margin-top: 4px;
                            }
                            @page { size: A4; margin: 14mm; }
                            @media print {
                                body { padding: 0; background: #ffffff; }
                                .agent-qr-poster { box-shadow: none; }
                            }
                        </style>
                    </head>
                    <body>
                        ${poster.outerHTML}
                        <script>
                            window.addEventListener('load', function () {
                                setTimeout(function () {
                                    window.focus();
                                    window.print();
                                }, 200);
                            });
                            window.addEventListener('afterprint', function () {
                                setTimeout(function () { window.close(); }, 100);
                            });
                        <\/script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }

        function downloadAgentQrPosterSvg(elementId, filename) {
            const poster = document.getElementById(elementId);
            const qrSvg = poster ? poster.querySelector('.agent-qr-poster__code svg') : null;

            if (! qrSvg) {
                return;
            }

            const svgMarkup = buildAgentQrPosterSvg(poster, qrSvg);
            const blob = new Blob([svgMarkup], { type: 'image/svg+xml;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename || 'counter-cash-out-qr-poster.svg';
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 300);
        }

        function buildAgentQrPosterSvg(poster, qrSvg) {
            const qrViewBox = qrSvg.getAttribute('viewBox') || `0 0 ${qrSvg.getAttribute('width') || 292} ${qrSvg.getAttribute('height') || 292}`;
            const qrInner = qrSvg.innerHTML;
            const agentName = poster.dataset.agentName || '';
            const agentCode = poster.dataset.agentCode || '';
            const currencies = poster.dataset.agentCurrencies || '';
            const instruction = poster.dataset.agentInstruction || '';
            const compactAgentName = truncateAgentQrText(agentName, 28);
            const compactSummary = truncateAgentQrText(`${agentQrPosterLabels.agentCode} ${agentCode} - ${currencies}`, 48);

            return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="760" height="980" viewBox="0 0 760 980" role="img" aria-label="${escapeAgentQrXml(agentQrPosterLabels.counterCashOutQr)}">
    <defs>
        <linearGradient id="agentQrAccent" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0%" stop-color="#5f3bd7"/>
            <stop offset="100%" stop-color="#21b8d7"/>
        </linearGradient>
        <linearGradient id="agentQrSoft" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0%" stop-color="#fbfaff"/>
            <stop offset="100%" stop-color="#f4fbff"/>
        </linearGradient>
    </defs>
    <rect width="760" height="980" fill="#f7f8fc"/>
    <rect x="36" y="32" width="688" height="916" rx="26" fill="#ffffff" stroke="#dcd8ff"/>

    <rect x="60" y="58" width="640" height="116" rx="18" fill="url(#agentQrSoft)" stroke="#ecebff"/>
    <rect x="86" y="88" width="48" height="48" rx="14" fill="url(#agentQrAccent)"/>
    <rect x="102" y="103" width="8" height="8" fill="#ffffff"/>
    <rect x="116" y="103" width="8" height="8" fill="#ffffff"/>
    <rect x="102" y="117" width="8" height="8" fill="#ffffff"/>
    <rect x="116" y="117" width="8" height="8" fill="#ffffff"/>
    <text x="154" y="92" fill="#5f3bd7" font-family="Arial, sans-serif" font-size="11" font-weight="800" letter-spacing="0">${escapeAgentQrXml(agentQrPosterLabels.counterCashOutQr).toUpperCase()}</text>
    <text x="154" y="122" fill="#111827" font-family="Arial, sans-serif" font-size="24" font-weight="800">${escapeAgentQrXml(compactAgentName)}</text>
    <text x="154" y="149" fill="#667085" font-family="Arial, sans-serif" font-size="14" font-weight="700">${escapeAgentQrXml(agentQrPosterLabels.approvedCounterPoint)}</text>
    <rect x="574" y="88" width="102" height="38" rx="19" fill="#ffffff" stroke="#dcd7ff"/>
    <text x="625" y="112" text-anchor="middle" fill="#111827" font-family="Arial, sans-serif" font-size="11" font-weight="800">SCAN HERE</text>

    <rect x="126" y="210" width="508" height="508" rx="26" fill="#fbfbff" stroke="#d8d4ff" stroke-width="2"/>
    <rect x="164" y="248" width="432" height="432" rx="22" fill="#ffffff"/>
    <svg x="196" y="280" width="368" height="368" viewBox="${escapeAgentQrXml(qrViewBox)}">${qrInner}</svg>

    <rect x="126" y="742" width="508" height="62" rx="16" fill="#ffffff" stroke="#ecebff"/>
    <text x="154" y="767" fill="#5f3bd7" font-family="Arial, sans-serif" font-size="10" font-weight="800" letter-spacing="0">${escapeAgentQrXml(agentQrPosterLabels.officialCashDesk).toUpperCase()}</text>
    <text x="154" y="790" fill="#111827" font-family="Arial, sans-serif" font-size="15" font-weight="800">${escapeAgentQrXml(compactSummary)}</text>

    <rect x="80" y="824" width="182" height="58" rx="14" fill="#f8f8ff" stroke="#ecebff"/>
    <text x="108" y="849" fill="#5f3bd7" font-family="Arial, sans-serif" font-size="10" font-weight="800">1</text>
    <text x="136" y="849" fill="#111827" font-family="Arial, sans-serif" font-size="12" font-weight="800">${escapeAgentQrXml(agentQrPosterLabels.scanQr)}</text>
    <text x="136" y="869" fill="#667085" font-family="Arial, sans-serif" font-size="10" font-weight="700">Start request</text>

    <rect x="289" y="824" width="182" height="58" rx="14" fill="#f8f8ff" stroke="#ecebff"/>
    <text x="317" y="849" fill="#5f3bd7" font-family="Arial, sans-serif" font-size="10" font-weight="800">2</text>
    <text x="345" y="849" fill="#111827" font-family="Arial, sans-serif" font-size="12" font-weight="800">Confirm</text>
    <text x="345" y="869" fill="#667085" font-family="Arial, sans-serif" font-size="10" font-weight="700">Amount + PIN</text>

    <rect x="498" y="824" width="182" height="58" rx="14" fill="#f8f8ff" stroke="#ecebff"/>
    <text x="526" y="849" fill="#5f3bd7" font-family="Arial, sans-serif" font-size="10" font-weight="800">3</text>
    <text x="554" y="849" fill="#111827" font-family="Arial, sans-serif" font-size="12" font-weight="800">Collect cash</text>
    <text x="554" y="869" fill="#667085" font-family="Arial, sans-serif" font-size="10" font-weight="700">Show reference</text>

    <text x="380" y="920" text-anchor="middle" fill="#5f3bd7" font-family="Arial, sans-serif" font-size="12" font-weight="800">${escapeAgentQrXml(agentQrPosterLabels.walletPinRequired)} - ${escapeAgentQrXml(agentQrPosterLabels.referenceAfterConfirm)}</text>
</svg>`;
        }

        function truncateAgentQrText(text, maxChars) {
            const value = String(text || '').trim();

            if (value.length <= maxChars) {
                return value;
            }

            return `${value.slice(0, Math.max(0, maxChars - 3)).trim()}...`;
        }

        function agentQrSvgTextLines(text, x, y, size, lineHeight, maxChars, fill, weight) {
            return wrapAgentQrText(text, maxChars)
                .map((line, index) => `<text x="${x}" y="${y + (index * lineHeight)}" fill="${fill}" font-family="Arial, sans-serif" font-size="${size}" font-weight="${weight}">${escapeAgentQrXml(line)}</text>`)
                .join('');
        }

        function wrapAgentQrText(text, maxChars) {
            const words = String(text || '').split(/\s+/).filter(Boolean);
            const lines = [];
            let line = '';

            words.forEach((word) => {
                const next = line ? `${line} ${word}` : word;

                if (next.length > maxChars && line) {
                    lines.push(line);
                    line = word;
                    return;
                }

                line = next;
            });

            if (line) {
                lines.push(line);
            }

            return lines.slice(0, 3);
        }

        function escapeAgentQrXml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&apos;');
        }
    </script>
@endpush
