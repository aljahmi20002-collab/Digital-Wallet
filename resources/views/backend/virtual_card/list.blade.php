@extends('backend.virtual_card.index')
@section('title', __('Virtual Cards'))

@section('virtual_card_header')
    <div class="vc-admin-hero my-3">
        <div>
            <span class="vc-admin-hero__eyebrow">{{ __('Card Inventory') }}</span>
            <h3>{{ __('All Virtual Cards') }}</h3>
            <p>{{ __('Monitor issued cards, assigned users, providers, wallets, and card lifecycle state.') }}</p>
        </div>
        <div class="vc-admin-hero__stats">
            <div>
                <span>{{ __('Cards') }}</span>
                <strong>{{ $cards->total() }}</strong>
            </div>
            <div>
                <span>{{ __('Providers') }}</span>
                <strong>{{ $providers->count() }}</strong>
            </div>
        </div>
    </div>
@endsection

@section('virtual_card_content')
    <div class="card-body vc-admin-board">
        <div class="vc-admin-toolbar">
            <form action="{{ route('admin.virtual-card.list') }}" method="GET" class="row g-2 g-md-3">
                <div class="col-md-6 col-xl-auto">
                    <x-form.select name="status" :options="$statuses" :selected="request('status')" :includeBlank="true"/>
                </div>
                <div class="col-md-6 col-xl-auto">
                    <x-form.select name="provider_id" :options="$providers->pluck('name', 'id')->toArray()" :selected="request('provider_id')" :includeBlank="true"/>
                </div>
                <div class="col-md-6 col-xl-auto">
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search by card, user, email...') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive vc-admin-table">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Card') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Provider') }}</th>
                        <th>{{ __('Wallet') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Issued') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cards as $card)
                    @php
                        $statusVal = $card->status?->value ?? 'inactive';
                        $isActive  = $statusVal === \App\Enums\VirtualCard\VirtualCardStatus::Active->value;
                        $isFrozen  = in_array($statusVal, [
                            \App\Enums\VirtualCard\VirtualCardStatus::Inactive->value,
                            \App\Enums\VirtualCard\VirtualCardStatus::Blocked->value,
                        ], true);
                    @endphp
                    <tr>
                        <td>
                            <span class="vc-admin-chip vc-admin-chip--success">**** {{ $card->last4 }}</span>
                            <small class="d-block text-muted mt-1">{{ $card->name_on_card }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $card->user->first_name }} {{ $card->user->last_name }}</div>
                            <small class="text-muted">{{ $card->user->email }}</small>
                        </td>
                        <td>{{ $card->provider->name ?? '-' }}</td>
                        <td><span class="vc-admin-chip">{{ $card->wallet->currency->code ?? '-' }}</span></td>
                        <td><span class="badge bg-{{ $card->status->badgeColor() }}">{{ $card->status->label() }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $card->created_at->format('Y-m-d') }}</div>
                            <small class="text-muted">{{ $card->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="text-end">
                            @if($isActive || $isFrozen)
                                @can('virtual-card-action')
                                    <form action="{{ route('admin.virtual-card.update-status') }}" method="POST" class="d-inline" data-vc-confirm="{{ $isActive ? __('Inactivate this card? Authorisations will be declined until reactivated.') : __('Reactivate this card?') }}">
                                        @csrf
                                        <input type="hidden" name="card_id" value="{{ $card->id }}">
                                        <input type="hidden" name="status" value="{{ $isActive ? 'inactive' : 'active' }}">
                                        <button type="submit" class="btn btn-sm {{ $isActive ? 'btn-warning' : 'btn-success' }}">
                                            <i class="fa-solid {{ $isActive ? 'fa-snowflake' : 'fa-circle-play' }} me-1"></i>
                                            {{ $isActive ? __('Inactivate') : __('Activate') }}
                                        </button>
                                    </form>
                                @endcan
                            @else
                                <span class="badge bg-secondary">{{ __('No action available') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-admin-not-found
                                :title="__('No cards found')"
                                :message="__('Issued virtual cards matching the current filters will appear here.')"
                                icon="fa-credit-card"
                            />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($cards->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $cards->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
