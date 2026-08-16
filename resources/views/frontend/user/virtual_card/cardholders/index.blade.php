@php
    use App\Enums\VirtualCard\CardholderType;
@endphp
@extends('frontend.layouts.user.index')
@section('title', __('Cardholders'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/virtual-card.css?v='.config('app.version')) }}">
@endpush

@section('content')
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('Cardholders')"
            :subtitle="__('Manage approved cardholders and create new records when needed.')"
            icon="fas fa-users"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.virtual-card.index') }}">
                <i class="fa-solid fa-list me-1"></i>{{ __('My Cards') }}
            </a>
            <a class="btn btn-light-success btn-sm" href="{{ route('user.virtual-card.cardholders.create') }}">
                <i class="fa-solid fa-plus-circle"></i> {{ __('Add New') }}
            </a>
        </x-user-feature-header>

        <div class="vc-page" data-vc-page>

            {{-- Stat strip — quick facts about this user's cardholders --}}
            @php
                $totalCount    = $cardholders->total();
                $approvedCount = $cardholders->where('status.value', 'approved')->count();
                $pendingCount  = $cardholders->where('status.value', 'pending')->count();
                $personalCount = $cardholders->filter(fn ($c) => $c->card_type instanceof CardholderType && $c->card_type === CardholderType::PERSONAL)->count();
                $businessCount = $cardholders->filter(fn ($c) => $c->card_type instanceof CardholderType && $c->card_type === CardholderType::BUSINESS)->count();
            @endphp
            <div class="vc-stats vc-stats--compact vc-stats--cardholders">
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Total') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--brand"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="vc-stat__value">{{ $totalCount }}</div>
                    <div class="vc-stat__sub">{{ __('cardholder profiles') }}</div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Approved') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--green"><i class="fa-solid fa-circle-check"></i></div>
                    </div>
                    <div class="vc-stat__value">{{ $approvedCount }}</div>
                    <div class="vc-stat__sub">{{ __('ready to issue') }}</div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Pending') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--amber"><i class="fa-regular fa-clock"></i></div>
                    </div>
                    <div class="vc-stat__value">{{ $pendingCount }}</div>
                    <div class="vc-stat__sub">{{ __('awaiting review') }}</div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Mix') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--violet"><i class="fa-solid fa-shuffle"></i></div>
                    </div>
                    <div class="vc-stat__value">{{ $personalCount }} / {{ $businessCount }}</div>
                    <div class="vc-stat__sub">{{ __(':p personal · :b business', ['p' => $personalCount, 'b' => $businessCount]) }}</div>
                </div>
            </div>

            {{-- Cardholder grid — responsive: table-style desktop / stacked cards mobile --}}
            <div class="vc-ch-grid" data-vc-cardholder-grid>
                @forelse($cardholders as $cardholder)
                    @php
                        $isBusiness   = $cardholder->card_type instanceof CardholderType
                            && $cardholder->card_type === CardholderType::BUSINESS;
                        $displayName  = $isBusiness && $cardholder->business
                            ? $cardholder->business->business_name
                            : $cardholder->full_name;
                        $displayEmail = $isBusiness && $cardholder->business
                            ? $cardholder->business->contact_email
                            : $cardholder->email;
                        $displayPhone = $isBusiness && $cardholder->business
                            ? $cardholder->business->contact_phone
                            : $cardholder->mobile;
                        $displayCountry = $isBusiness && $cardholder->business
                            ? $cardholder->business->country
                            : $cardholder->country;
                        $statusColor  = $cardholder->status?->badgeColor() ?? 'secondary';
                        $statusTone   = match ($statusColor) {
                            'success' => 'green',
                            'warning' => 'amber',
                            'danger' => 'red',
                            'primary', 'info' => 'blue',
                            default => 'neutral',
                        };
                        $initials     = strtoupper(substr($displayName ?? '?', 0, 1));
                    @endphp
                    <article class="vc-ch-card vc-ch-card--premium" data-vc-card-type="{{ $cardholder->card_type?->value }}">
                        <div class="vc-ch-card__head">
                            <div class="vc-ch-card__avatar vc-ch-card__avatar--{{ $isBusiness ? 'business' : 'personal' }}">
                                @if($isBusiness)
                                    <i class="fa-solid fa-building"></i>
                                @else
                                    <span>{{ $initials }}</span>
                                @endif
                            </div>
                            <div class="vc-ch-card__identity">
                                <div class="vc-ch-card__name-row">
                                    <h3 class="vc-ch-card__name">{{ $displayName ?: __('Unnamed cardholder') }}</h3>
                                    <span class="vc-pill vc-pill--{{ $isBusiness ? 'amber' : 'blue' }} vc-pill--soft">
                                        {{ $cardholder->card_type?->label() }}
                                    </span>
                                </div>
                                <div class="vc-ch-card__sub">
                                    @if($displayEmail)
                                        <span class="vc-ch-card__sub-item">
                                            <i class="fa-regular fa-envelope"></i> {{ $displayEmail }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="vc-ch-card__status">
                                <span class="vc-pill vc-pill--{{ $statusTone }}">
                                    <span class="vc-pill__dot"></span>{{ $cardholder->status?->label() }}
                                </span>
                            </div>
                        </div>

                        <div class="vc-ch-card__meta">
                            <div class="vc-ch-card__meta-item">
                                <div class="vc-ch-card__meta-label"><i class="fa-solid fa-phone"></i> {{ __('Phone') }}</div>
                                <div class="vc-ch-card__meta-value">{{ $displayPhone ?: '—' }}</div>
                            </div>
                            <div class="vc-ch-card__meta-item">
                                <div class="vc-ch-card__meta-label"><i class="fa-solid fa-flag"></i> {{ __('Country') }}</div>
                                <div class="vc-ch-card__meta-value">{{ $displayCountry ?: '—' }}</div>
                            </div>
                            @if(!$isBusiness && $cardholder->id_type)
                                <div class="vc-ch-card__meta-item">
                                    <div class="vc-ch-card__meta-label"><i class="fa-solid fa-id-card"></i> {{ __('ID') }}</div>
                                    <div class="vc-ch-card__meta-value">
                                        {{ Str::headline(str_replace('_', ' ', $cardholder->id_type)) }}
                                    </div>
                                </div>
                            @endif
                            @if($isBusiness && $cardholder->business?->incorporation_country)
                                <div class="vc-ch-card__meta-item">
                                    <div class="vc-ch-card__meta-label"><i class="fa-solid fa-scale-balanced"></i> {{ __('Incorporated') }}</div>
                                    <div class="vc-ch-card__meta-value">{{ $cardholder->business->incorporation_country }}</div>
                                </div>
                            @endif
                            <div class="vc-ch-card__meta-item">
                                <div class="vc-ch-card__meta-label"><i class="fa-regular fa-clock"></i> {{ __('Created') }}</div>
                                <div class="vc-ch-card__meta-value">{{ $cardholder->created_at?->diffForHumans() }}</div>
                            </div>
                        </div>

                        <div class="vc-ch-card__actions">
                            <a href="{{ route('user.virtual-card.cardholders.show', $cardholder) }}"
                               class="vc-btn vc-btn--secondary vc-btn--icon"
                               aria-label="{{ __('View') }}"
                               title="{{ __('View') }}">
                                <i class="fa-regular fa-eye"></i>
                                <span class="visually-hidden">{{ __('View') }}</span>
                            </a>
                            @if($cardholder->status?->isPending())
                                <a href="{{ route('user.virtual-card.cardholders.edit', $cardholder) }}"
                                   class="vc-btn vc-btn--secondary vc-btn--icon"
                                   aria-label="{{ __('Edit') }}"
                                   title="{{ __('Edit') }}">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                    <span class="visually-hidden">{{ __('Edit') }}</span>
                                </a>
                                <button type="button"
                                        class="vc-btn vc-btn--danger vc-btn--icon"
                                        aria-label="{{ __('Delete') }}"
                                        title="{{ __('Delete') }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteCardholderModal"
                                        data-cardholder-id="{{ $cardholder->id }}">
                                    <i class="fa-regular fa-trash-can"></i>
                                    <span class="visually-hidden">{{ __('Delete') }}</span>
                                </button>
                            @elseif($cardholder->status?->isRejected())
                                <button type="button"
                                        class="vc-btn vc-btn--danger vc-btn--icon"
                                        aria-label="{{ __('Delete') }}"
                                        title="{{ __('Delete') }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteCardholderModal"
                                        data-cardholder-id="{{ $cardholder->id }}">
                                    <i class="fa-regular fa-trash-can"></i>
                                    <span class="visually-hidden">{{ __('Delete') }}</span>
                                </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <x-user-not-found
                        :title="__('No cardholders yet')"
                        :message="__('Create your first cardholder profile so any provider can issue a virtual card.')"
                        :eyebrow="__('Identity profile ready')"
                        icon="fa-user-plus"
                        :action-url="route('user.virtual-card.cardholders.create')"
                        :action-label="__('Add Cardholder')"
                        action-icon="fa-plus"
                    />
                @endforelse
            </div>

            @if($cardholders->hasPages())
                <div class="vc-pagination">{{ $cardholders->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Delete confirmation modal --}}
    @include('frontend.user.virtual_card.partials._delete_cardholder_modal')
@endsection

@push('scripts')
    <script>
        "use strict";
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('deleteCardholderModal');
            if (!deleteModal) return;

            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button       = event.relatedTarget;
                const cardholderId = button.getAttribute('data-cardholder-id');
                const form         = document.getElementById('deleteCardholderForm');
                if (form && cardholderId) {
                    form.action = "{{ route('user.virtual-card.cardholders.destroy', ':id') }}".replace(':id', cardholderId);
                }
            });
        });
    </script>
@endpush
