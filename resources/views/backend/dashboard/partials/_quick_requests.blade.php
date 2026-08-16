<div class="dashboard-queue mb-4">
    @php
        $items        = collect($quickRequests ?? []);
        $pendingTotal = $items->sum('count');
        $activeQueues = $items->filter(fn (array $item): bool => (int) ($item['count'] ?? 0) > 0)->count();
        $moduleCount  = $items->count();
        $requestMeta  = [
            'deposit'    => ['title' => __('Deposits'),      'subtitle' => __('Manual deposits awaiting review'),       'status' => __('Needs review'),      'hint' => __('Review deposits')],
            'withdraw'   => ['title' => __('Withdrawals'),   'subtitle' => __('Manual withdrawals awaiting approval'),  'status' => __('Needs review'),      'hint' => __('Review withdrawals')],
            'kyc'        => ['title' => __('KYC'),           'subtitle' => __('Identity verifications awaiting approval'),'status' => __('Verification due'), 'hint' => __('Review KYC')],
            'merchant'   => ['title' => __('Merchants'),     'subtitle' => __('Merchant applications awaiting review'),  'status' => __('Approval due'),     'hint' => __('Review merchants')],
            'cardholder' => ['title' => __('Cardholders'),   'subtitle' => __('Cardholder profiles awaiting review'),   'status' => __('Profile review'),   'hint' => __('Review cardholders')],
            'vc_request' => ['title' => __('Virtual Cards'), 'subtitle' => __('Card requests awaiting issuance'),        'status' => __('Issuance due'),     'hint' => __('Review cards')],
        ];
    @endphp

    <div class="dashboard-queue__panel">
        <div class="dashboard-queue__layout">
            {{-- Queue grid --}}
            <div class="dashboard-queue__main">
                <div class="dashboard-queue__notice">
                    <span class="dashboard-queue__notice-icon" aria-hidden="true">
                        <i class="fa-solid fa-list-check"></i>
                    </span>

                    <div class="dashboard-queue__notice-copy">
                        <span class="dashboard-queue__notice-eyebrow">@lang('Operations Queue')</span>
                        <h3>@lang('Priority Queue')</h3>
                        <p>@lang('Review high-priority approvals first. Active queues stay highlighted for faster action.')</p>
                    </div>

                    <div class="dashboard-queue__notice-metrics" aria-label="{{ __('Queue summary') }}">
                        <div class="dashboard-queue__notice-card dashboard-queue__notice-card--primary">
                            <i class="fa-solid fa-inbox" aria-hidden="true"></i>
                            <strong>{{ number_format($pendingTotal) }}</strong>
                            <span>@lang('Total Pending')</span>
                        </div>
                        <div class="dashboard-queue__notice-card dashboard-queue__notice-card--warning">
                            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                            <strong>{{ number_format($activeQueues) }}</strong>
                            <span>@lang('Urgent Items')</span>
                        </div>
                        <div class="dashboard-queue__notice-card dashboard-queue__notice-card--violet">
                            <i class="fa-solid fa-grip" aria-hidden="true"></i>
                            <strong>{{ number_format($moduleCount) }}</strong>
                            <span>@lang('Modules Waiting')</span>
                        </div>
                    </div>

                    <span class="dashboard-queue__updated">
                        @lang('Updated just now')
                        <span aria-hidden="true"></span>
                    </span>
                </div>

                <div class="dashboard-queue__body">
                    @if($items->isNotEmpty())
                        <div class="dashboard-queue__grid">
                            @foreach($items as $item)
                                @php
                                    $meta    = $requestMeta[$item['key']] ?? ['title' => $item['title'], 'subtitle' => __('Pending review queue'), 'status' => __('Needs review'), 'hint' => __('Review queue')];
                                    $count   = (int) ($item['count'] ?? 0);
                                    $isActive = $count > 0;
                                    $accent  = $item['color'] ?? '';
                                    $itemCls = trim($accent . ' ' . ($isActive ? 'is-active' : ''));
                                @endphp
                                <a href="{{ $item['link'] }}" class="dashboard-queue__item {{ $itemCls }}" title="{{ $meta['title'] }}" aria-label="{{ __('Review :title queue', ['title' => $meta['title']]) }}">
                                    <div class="dashboard-queue__item-top">
                                        <span class="dashboard-queue__pill {{ $isActive ? 'dashboard-queue__pill--live' : '' }}">
                                            <i class="{{ $isActive ? 'fa-solid fa-bolt' : 'fa-solid fa-check' }}"></i>
                                            {{ $isActive ? $meta['status'] : __('Clear') }}
                                        </span>
                                        <span class="dashboard-queue__count">{{ number_format($count) }}</span>
                                    </div>

                                    <div class="dashboard-queue__item-body">
                                        <div class="dashboard-queue__icon">
                                            <i class="{{ $item['icon'] }}"></i>
                                        </div>
                                        <div class="dashboard-queue__text">
                                            <p class="dashboard-queue__title">{{ $meta['title'] }}</p>
                                            <p class="dashboard-queue__description">{{ $meta['subtitle'] }}</p>
                                        </div>
                                    </div>

                                    <div class="dashboard-queue__footer">
                                        <span class="dashboard-queue__hint">{{ $meta['hint'] }}</span>
                                        <span class="dashboard-queue__cta" aria-hidden="true">
                                            <span>{{ __('Review') }}</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <x-admin-not-found
                            :title="__('No priority requests found')"
                            :message="__('Pending admin queues will appear here as soon as new actions need review.')"
                            icon="fa-clipboard-check"
                            class="dashboard-empty-state h-100"
                        />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
