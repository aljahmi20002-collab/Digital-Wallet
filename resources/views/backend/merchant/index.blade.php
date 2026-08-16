@extends('backend.layouts.app')
@section('title', $title)
@section('content')
    <div class="clearfix my-3">
        <div class="fs-3 fw-semibold float-start">
            {{ $title }}
        </div>
    </div>
    <div class="card border-0 mb-4">
        <div class="card-body">

            @include('backend.merchant.partials._filter')

            {{-- Transactions Table --}}
            <div class="table-responsive">
                <table class="table caption-top mb-0">
                    <thead class="table-light fw-semibold text-nowrap">
                    <tr class="align-middle">
                        <th>{{ __('Merchant Info') }}</th>
                        <th>{{ __('User | Merchant ID') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Time') }}</th>
                        @can('merchant-manage')
                             <th>{{ __('Action') }}</th>
                        @endcan
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($merchants as $merchant)
                        @php
                            $statusColor = $merchant->status->color();
                        @endphp
                        <tr class="align-middle">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle shadow-sm me-2" width="36" height="36"
                                         src="{{ asset($merchant->business_logo) }}" alt="User Avatar" loading="lazy">
                                    <div>
                                        <div class="text-nowrap">{{ $merchant->business_name }}</div>
                                        <div class="small text-muted ">{{ $merchant->site_url }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-primary-emphasis fw-bold">
                                    <a href="{{ route('admin.user.manage', $merchant->user->username) }}" class="text-decoration-none">{{ $merchant->user->name }}</a>
                                </div>
                                <div class="small text-muted">{{ $merchant->merchant_key }}</div>
                            </td>
                                <td class="text-nowrap text-uppercase">
                            <span class="badge bg-{{  $statusColor }}">
                                {{ $merchant->status }}
                            </span>
                                </td>
                            <td>
                                <div>{{ $merchant->created_at->format('Y-m-d H:i') }}</div>
                                <div class="small text-muted">{{ $merchant->created_at->diffForHumans() }}</div>
                            </td>
                            @can('merchant-manage')
                                <td>
                                    @php
                                        $isPending = ($merchant->status === \App\Enums\MerchantStatus::PENDING);
                                        $btnText  = $isPending ? __('Review Request') : __('Manage Merchant');
                                        $btnIcon  = $isPending ? 'fa-clipboard-check' : 'fa-gear';
                                    @endphp
                                    <button
                                        type="button"
                                        class="btn btn-primary d-inline-flex align-items-center gap-2 text-nowrap"
                                        data-coreui-toggle="modal"
                                        data-coreui-target="#review-{{ $merchant->id }}"
                                        title="{{ $btnText }}" aria-label="{{ $btnText }}">
                                        <i class="fa-solid {{ $btnIcon }}"></i>
                                        <span class="d-none d-sm-inline">{{ $btnText }}</span>
                                        <span class="d-inline d-sm-none">{{ __('View') }}</span>
                                    </button>

                                    @include('backend.merchant.partials._review_modal')
                                </td>
                            @endcan

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin-not-found
                                    :title="__('No merchants found')"
                                    :message="__('No merchant requests or profiles match the current filters.')"
                                    icon="fa-store"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $merchants->links() }}
            </div>

        </div>
    </div>
@endSection
