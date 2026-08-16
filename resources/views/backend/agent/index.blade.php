@extends('backend.layouts.app')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/agent-admin.css?v=' . config('app.version')) }}">
@endpush
@section('content')
    <div class="clearfix my-3">
        <div class="fs-3 fw-semibold float-start">
            {{ $title }}
        </div>
        @can('agent-commission-rules-manage')
            <a href="{{ route('admin.agent.commission-rules.index') }}" class="btn btn-outline-primary float-end">
                <i class="fa-solid fa-percent me-1"></i>{{ __('Commission Rules') }}
            </a>
        @endcan
    </div>
    <div class="card border-0 mb-4">
        <div class="card-body">

            @include('backend.agent.partials._filter')

            {{-- Agents Table --}}
            <div class="table-responsive">
                <table class="table caption-top mb-0">
                    <thead class="table-light fw-semibold text-nowrap">
                    <tr class="align-middle">
                        <th>{{ __('Agent Info') }}</th>
                        <th>{{ __('User | Agent Code') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Performance') }}</th>
                        <th>{{ __('Time') }}</th>
                        @can('agent-manage')
                            <th>{{ __('Action') }}</th>
                        @endcan
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($agents as $agent)
                        @php
                            $statusColor = $agent->status->color();
                        @endphp
                        <tr class="align-middle">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle shadow-sm me-2" width="36" height="36"
                                         src="{{ asset($agent->logo) }}" alt="Agent Logo" loading="lazy">
                                    <div>
                                        <div class="text-nowrap">{{ $agent->agent_name }}</div>
                                        <div class="small text-muted">{{ $agent->user?->email ?? $agent->user?->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-primary-emphasis fw-bold">
                                    <a href="{{ route('admin.user.manage', $agent->user->username) }}" class="text-decoration-none">{{ $agent->user->name }}</a>
                                </div>
                                <div class="small text-muted">{{ $agent->agent_code }}</div>
                            </td>
                            <td class="text-nowrap text-uppercase">
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $agent->status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-nowrap">
                                    {{ __('Volume') }}: {{ getSymbol($agent->currency?->code) }}{{ number_format((float) ($agent->completed_volume ?? 0), (int) setting('site_decimal', 2)) }}
                                </div>
                                <div class="small text-muted text-nowrap">
                                    {{ __('Commission') }}: {{ getSymbol($agent->currency?->code) }}{{ number_format((float) ($agent->earned_commission ?? 0), (int) setting('site_decimal', 2)) }}
                                </div>
                                <div class="small text-muted text-nowrap">
                                    {{ __('Currencies') }}: {{ $agent->supportedCurrencies->pluck('code')->implode(', ') ?: $agent->currency?->code }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $agent->created_at->format('Y-m-d H:i') }}</div>
                                <div class="small text-muted">{{ $agent->created_at->diffForHumans() }}</div>
                            </td>
                            @can('agent-manage')
                                <td>
                                    @php
                                        $isPending = ($agent->status === \App\Enums\AgentStatus::PENDING);
                                        $btnText  = $isPending ? __('Review Request') : __('Manage Agent');
                                        $btnIcon  = $isPending ? 'fa-clipboard-check' : 'fa-gear';
                                    @endphp
                                    <button
                                        type="button"
                                        class="btn btn-primary d-inline-flex align-items-center gap-2 text-nowrap agent-review-trigger"
                                        data-coreui-toggle="modal"
                                        data-coreui-target="#agent-review-{{ $agent->id }}"
                                        title="{{ $btnText }}" aria-label="{{ $btnText }}">
                                        <i class="fa-solid {{ $btnIcon }}"></i>
                                        <span class="d-none d-sm-inline">{{ $btnText }}</span>
                                        <span class="d-inline d-sm-none">{{ __('View') }}</span>
                                    </button>

                                    @include('backend.agent.partials._review_modal')
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth('admin')->user()?->can('agent-manage') ? 6 : 5 }}">
                                <x-admin-not-found
                                    :title="__('No agents found')"
                                    :message="__('No agent requests or profiles match the current filters.')"
                                    icon="fa-user-tie"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $agents->links() }}
            </div>

        </div>
    </div>
@endsection
