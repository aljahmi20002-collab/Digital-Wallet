<section class="agent-pending-qr-panel">
    <div class="agent-ledger-panel__head">
        <h3>{{ __('QR Cash-Out Requests') }}</h3>
        <span>{{ $pendingQrCashOuts->count() }}</span>
    </div>

    @forelse($pendingQrCashOuts as $operation)
        <div class="agent-pending-qr-row">
            <div class="agent-pending-qr-row__main">
                <span class="agent-ledger-row__icon bg-warning">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
                <div>
                    <strong>{{ $operation->customer?->name }}</strong>
                    <span>{{ $operation->reference }} &middot; {{ $operation->created_at?->diffForHumans() }}</span>
                </div>
            </div>
            <div class="agent-pending-qr-row__amount">
                <strong>{{ getSymbol($operation->currency?->code) }}{{ number_format($operation->amount, (int) setting('site_decimal', 2)) }}</strong>
                <span>{{ $operation->currency?->code }}</span>
            </div>
            <form action="{{ route('user.agent.cash-out.mark-paid', $operation) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-agent btn-sm">
                    <i class="fa-solid fa-check me-1"></i>{{ __('Mark Cash Paid') }}
                </button>
            </form>
        </div>
    @empty
        <x-user-not-found
            :title="__('No QR cash-out waiting')"
            :message="__('Customer QR cash-out requests that need cash handover will appear here.')"
            icon="fa-qrcode"
        />
    @endforelse
</section>
