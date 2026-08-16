@extends('backend.user.manage')
@section('user_manage_content')
    @php
        use App\Enums\TrxStatus;
        use App\Enums\TrxType;
    @endphp
    <div class="card-body px-0">
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('admin.user.manage', ['username' => $user->username, 'param' => 'transactions']) }}" method="GET" class="row g-2 g-md-3">
                <div class="col-md-6 col-xl-auto">
                    <label for="reportrange" class="form-label small">{{ __('Date Range') }}</label>
                    <div class="input-group">
                        <input type="hidden" name="daterange" value="{{ request('daterange') }}">
                        <div id="reportrange" class="form-control d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-calendar-days"></i>
                                <span class="text-nowrap flex-grow-1"></span>
                            </div>
                            <x-icon name="angle-down" class="text-muted flex-shrink-0"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-auto">
                    <label for="search" class="form-label small">{{ __('Search') }}</label>
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search...') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table border mb-0">
                <thead class="fw-semibold">
                <tr class="align-middle text-nowrap">
                    <th>{{ __('Description | Provider') }}</th>
                    <th>{{ __('Trx Info') }}</th>
                    <th>{{ __('Amount | Type') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $color = $transaction->status->color();
                        $amountColor = $transaction->amount_flow->color($transaction->status);
                        $amountSign = $transaction->amount_flow->sign($transaction->status);
                    @endphp
                    <tr class="align-middle">
                        <td>
                            <div>{{ $transaction->description }}</div>
                            <div class="small text-muted">{{ $transaction->provider .' - '.$transaction->processing_type->label() }}</div>
                        </td>
                        <td>
                            <div>{{ $transaction->trx_id }}</div>
                            <span class="text-muted small">
                                {{ $transaction->created_at->format('Y-m-d') }}
                                <span class="badge small bg-{{ $color }} text-uppercase">{{ $transaction->status->label() }}</span>
                            </span>
                        </td>
                        <td>
                            <div class="{{ $amountColor }} fw-bold">
                                {{ $amountSign . $transaction->amount . ' ' . $transaction->currency }}
                            </div>
                            <div class="small text-muted">
                                {{ __('Fee: :fee | Type :type', ['fee' => getSymbol($transaction->currency) . $transaction->fee, 'type' => $transaction->trx_type->label()]) }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <x-admin-not-found
                                :title="__('No transactions found')"
                                :message="__('This user has no transactions matching the current filters.')"
                                icon="fa-receipt"
                            />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
