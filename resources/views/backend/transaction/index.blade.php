@extends('backend.layouts.app')
@section('title', __('Transactions'))
@section('content')
    <div class="clearfix my-3">
        <div class="fs-3 fw-semibold float-start">
            {{ __(':type Transactions', ['type' => request('type') == 'all' ? 'All' : title(request('type'))]) }}
        </div>
    </div>

    <div class="card border-0 mb-4">
        <div class="card-body">
            @include('backend.transaction.partials._filter')

            <div class="table-responsive">
                <table class="table border mb-0">
                    <thead class="table-light fw-semibold">
                    <tr class="align-middle text-nowrap">
                        <th>{{ __('User | TXN ID') }}</th>
                        <th>{{ __('Amount | Type') }}</th>
                        <th>{{ __('Description | Provider') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Time') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $transaction)
                        @php
                            $avatarData = getUserAvatarDetails($transaction->user->first_name, $transaction->user->last_name);
                            $color = $transaction->status->color();
                            $amountColor = $transaction->amount_flow->color($transaction->status);
                            $amountSign = $transaction->amount_flow->sign($transaction->status);
                        @endphp
                        <tr class="align-middle">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md me-2">
                                        @isset($transaction->user->avatar)
                                            <img class="avatar-img" src="{{ asset($transaction->user->avatar) }}" height="40" alt="User Avatar" loading="lazy">
                                        @else
                                            <div class="avatar avatar-md {{ $avatarData['class'] }} text-white">
                                                {{ $avatarData['initials'] }}
                                            </div>
                                        @endisset
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.user.manage', $transaction->user->username) }}" class="text-decoration-none">
                                            {{ $transaction->user->name }}
                                        </a>
                                        <div class="small text-muted text-uppercase">{{ strtoupper($transaction->trx_id) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="{{ $amountColor }} fw-bold">
                                    {{ $amountSign . $transaction->amount . ' ' . $transaction->currency }}
                                </div>
                                <div class="small text-muted">
                                    {{ __('Fee: :fee | Type :type', ['fee' => getSymbol($transaction->currency) . $transaction->fee, 'type' => $transaction->trx_type->label()]) }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $transaction->description }}</div>
                                <div class="small text-muted">{{ $transaction->provider .' - '.$transaction->processing_type->label() }}</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $color }} text-uppercase">{{ $transaction->status->label() }}</span>
                            </td>
                            <td>
                                <div>{{ $transaction->created_at->format('Y-m-d H:i') }}</div>
                                <div class="small text-muted">{{ $transaction->created_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin-not-found
                                    :title="__('No transactions found')"
                                    :message="__('Transactions matching the current filters will appear here.')"
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
    </div>
@endsection
