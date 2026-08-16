<section class="ud-transactions ud-transactions--recent mb-3">
    <header class="ud-transactions__head">
        <div class="ud-transactions__intro">
            <h3 class="ud-transactions__title">{{ __('Recent Transactions') }}</h3>
            <p class="ud-transactions__subtitle">{{ __('Latest wallet activity and status updates.') }}</p>
        </div>
        <a href="{{ route('user.transaction.index') }}" class="ud-transactions__link">
            <span>{{ __('View all') }}</span>
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </header>

    <div class="ud-transactions__list">
        @forelse($transactions as $transaction)
            @php
                $transactionTypeClass = $transaction->trx_type->kebabCase();
                $icon = $transaction->trx_type->icon();
                $amountColor = $transaction->amount_flow->color($transaction->status);
                $amountSign = $transaction->amount_flow->sign($transaction->status);
            @endphp

            <div class="ud-trx-item" role="button" data-bs-toggle="modal"
                 data-bs-target="#transactionModal{{ $transaction->id }}">
                <span class="ud-trx-item__icon {{ $transactionTypeClass }}" aria-hidden="true">
                    <x-icon name="{{ $icon }}" height="20" width="20"/>
                </span>

                <div class="ud-trx-item__body">
                    <div class="ud-trx-item__primary">
                        <span class="ud-trx-item__title">{{ $transaction->description }}</span>
                        <span class="ud-trx-item__amount {{ $amountColor }}">
                            {{ $amountSign.number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                        </span>
                    </div>

                    <div class="ud-trx-item__secondary">
                        <div class="ud-trx-item__tags">
                            <span class="ud-trx-chip ud-trx-chip--type {{ $transactionTypeClass }}">
                                {{ title($transaction->trx_type->value) }}
                            </span>
                            <span class="ud-trx-chip ud-trx-chip--status bg-{{ $transaction->status->color() }}">
                                {{ strtoupper($transaction->status->value) }}
                            </span>
                        </div>
                        <div class="ud-trx-item__meta">
                            <span>{{ __('TRX') }}: {{ strtoupper($transaction->trx_id) }}</span>
                            <time datetime="{{ $transaction->created_at->toIso8601String() }}">
                                {{ $transaction->created_at->format('d M Y, h:i A') }}
                            </time>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transaction Modal --}}
            @include('frontend.user.transaction.partials._details_modal', ['transaction' => $transaction, 'transactionTypeClass' => $transactionTypeClass])
        @empty
            <x-user-not-found
                class="ud-transactions__empty"
                :title="__('No recent transactions')"
                :message="__('New deposits, withdrawals, transfers, and wallet activity will appear here.')"
                :eyebrow="__('Transaction stream')"
                icon="fa-receipt"
            />
        @endforelse
    </div>
</section>
