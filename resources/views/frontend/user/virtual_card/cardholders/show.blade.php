@php
    use App\Enums\VirtualCard\CardholderType;
    use Illuminate\Support\Str;

    $isBusiness   = $cardholder->card_type instanceof CardholderType
        && $cardholder->card_type === CardholderType::BUSINESS;
    $business     = $cardholder->business;
    $statusColor  = $cardholder->status?->badgeColor() ?? 'secondary';
    $statusPill   = $statusColor === 'success' ? 'green'
                  : ($statusColor === 'warning' ? 'amber'
                  : ($statusColor === 'danger' ? 'red' : 'neutral'));
    $displayName  = $isBusiness && $business ? $business->business_name : $cardholder->full_name;
    $initials     = strtoupper(substr((string) $displayName ?: '?', 0, 1));
    $idDocUrl     = $cardholder->kyc_documents['id_document'] ?? null;
    $owners       = is_array($business?->beneficial_owners) ? $business->beneficial_owners : [];
@endphp
@extends('frontend.layouts.user.index')
@section('title', __('Cardholder Details'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/virtual-card.css?v='.config('app.version')) }}">
@endpush

@section('content')
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('Cardholder Details')"
            :subtitle="__('Review identity, status, and profile details in one place.')"
            icon="fas fa-id-card"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.virtual-card.cardholders.index') }}">
                <i class="fa-solid fa-list"></i> {{ __('All Cardholders') }}
            </a>
            @if($cardholder->status?->isPending())
                <a class="btn btn-light-secondary btn-sm" href="{{ route('user.virtual-card.cardholders.edit', $cardholder) }}">
                    <i class="fa-solid fa-pen-to-square"></i> {{ __('Edit') }}
                </a>
            @endif
        </x-user-feature-header>

        <div class="vc-page vc-ch-show" data-vc-page>

            {{-- 1. Hero card — name + status + at-a-glance pills --}}
            <section class="vc-form-section vc-ch-hero">
                <div class="vc-ch-hero__main">
                    <div class="vc-ch-hero__avatar vc-ch-hero__avatar--{{ $isBusiness ? 'business' : 'personal' }}">
                        @if($isBusiness)
                            <i class="fa-solid fa-building"></i>
                        @else
                            <span>{{ $initials }}</span>
                        @endif
                    </div>
                    <div class="vc-ch-hero__copy">
                        <h2 class="vc-ch-hero__name">{{ $displayName ?: __('Unnamed cardholder') }}</h2>
                        <div class="vc-ch-hero__sub">
                            @if($isBusiness && $business?->trading_name && $business->trading_name !== $displayName)
                                <span class="vc-ch-hero__sub-item">
                                    <i class="fa-solid fa-tag"></i> {{ $business->trading_name }}
                                </span>
                            @endif
                            @if(!$isBusiness && $cardholder->email)
                                <span class="vc-ch-hero__sub-item">
                                    <i class="fa-regular fa-envelope"></i> {{ $cardholder->email }}
                                </span>
                            @endif
                            @if($isBusiness && $business?->contact_email)
                                <span class="vc-ch-hero__sub-item">
                                    <i class="fa-regular fa-envelope"></i> {{ $business->contact_email }}
                                </span>
                            @endif
                        </div>
                        <div class="vc-ch-hero__pills">
                            <span class="vc-pill vc-pill--{{ $isBusiness ? 'amber' : 'blue' }}">{{ $cardholder->card_type?->label() }}</span>
                            <span class="vc-pill vc-pill--{{ $statusPill }}">
                                <span class="vc-pill__dot"></span>{{ $cardholder->status?->label() }}
                            </span>
                            @if($cardholder->kyc_status)
                                <span class="vc-pill">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    @lang('KYC'): {{ $cardholder->kyc_status?->label() ?? $cardholder->kyc_status }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="vc-ch-hero__quick">
                    <div class="vc-ch-hero__quick-item">
                        <div class="vc-ch-hero__quick-label">{{ __('Phone') }}</div>
                        <div class="vc-ch-hero__quick-value">
                            {{ trim(($isBusiness ? ($business?->phone_country_code ?? '') : ($cardholder->phone_country_code ?? '')).' '.($isBusiness ? ($business?->contact_phone ?? '—') : ($cardholder->mobile ?? '—'))) }}
                        </div>
                    </div>
                    <div class="vc-ch-hero__quick-item">
                        <div class="vc-ch-hero__quick-label">{{ __('Country') }}</div>
                        <div class="vc-ch-hero__quick-value">
                            {{ $isBusiness ? ($business?->country ?? '—') : ($cardholder->country ?? '—') }}
                        </div>
                    </div>
                    <div class="vc-ch-hero__quick-item">
                        <div class="vc-ch-hero__quick-label">{{ __('Created') }}</div>
                        <div class="vc-ch-hero__quick-value">{{ optional($cardholder->created_at)->format('d M Y') }}</div>
                    </div>
                </div>
            </section>

            @if(!$isBusiness)
                {{-- 2. Identity --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-user"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Identity') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Legal name, gender and origin.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Title') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->title ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Full Name') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->full_name ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Gender') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->gender?->label() ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Date of Birth') }}</div>
                            <div class="vc-meta__value">{{ optional($cardholder->dob)->format('d M Y') ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Nationality') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->nationality ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Place of Birth') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->place_of_birth ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Relation') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->relation ?: '—' }}</div>
                        </div>
                    </div>
                </section>

                {{-- 3. Contact --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon vc-form-section__icon--green"><i class="fa-solid fa-envelope"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Contact') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Where providers reach the cardholder.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Email') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->email ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Mobile') }}</div>
                            <div class="vc-meta__value mono">{{ trim(($cardholder->phone_country_code ?? '').' '.($cardholder->mobile ?? '—')) }}</div>
                        </div>
                    </div>
                </section>

                {{-- 4. Billing Address --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon vc-form-section__icon--amber"><i class="fa-solid fa-location-dot"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Billing Address') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Used by every provider for AVS verification.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row vc-meta__row--full">
                            <div class="vc-meta__label">{{ __('Street') }}</div>
                            <div class="vc-meta__value">
                                {{ $cardholder->address_line1 ?: '—' }}
                                @if($cardholder->address_line2), {{ $cardholder->address_line2 }}@endif
                            </div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('City') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->city ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('State') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->state ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Postal Code') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->postal_code ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Country') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->country ?: '—' }}</div>
                        </div>
                    </div>
                </section>

                {{-- 5. Government ID --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon"><i class="fa-solid fa-id-card-clip"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Government ID') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Identity verification document for issuance.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('ID Type') }}</div>
                            <div class="vc-meta__value">
                                {{ $cardholder->id_type ? Str::headline(str_replace('_', ' ', $cardholder->id_type)) : '—' }}
                            </div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('ID Number') }}</div>
                            <div class="vc-meta__value mono">{{ $cardholder->id_number ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Issuing Country') }}</div>
                            <div class="vc-meta__value">{{ $cardholder->id_issue_country ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Issue Date') }}</div>
                            <div class="vc-meta__value">{{ optional($cardholder->id_issue_date)->format('d M Y') ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Expiry') }}</div>
                            <div class="vc-meta__value">{{ optional($cardholder->id_expiry)->format('d M Y') ?: '—' }}</div>
                        </div>
                        @if($idDocUrl)
                            <div class="vc-meta__row vc-meta__row--full">
                                <div class="vc-meta__label">{{ __('Document') }}</div>
                                <div class="vc-meta__value">
                                    <a href="{{ asset($idDocUrl) }}" target="_blank" rel="noopener" class="vc-field__doc-link">
                                        <i class="fa-solid fa-file-arrow-down"></i> {{ __('View uploaded ID') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- 6. Tax & Fiscal --}}
                @if($cardholder->tax_id || $cardholder->tax_country)
                    <section class="vc-form-section">
                        <header class="vc-form-section__head">
                            <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-receipt"></i></span>
                            <div class="vc-form-section__copy">
                                <div class="vc-form-section__title">{{ __('Tax & Fiscal') }}</div>
                                <div class="vc-form-section__subtitle">{{ __('SSN / ITIN / PAN / TIN equivalent.') }}</div>
                            </div>
                        </header>
                        <div class="vc-meta">
                            <div class="vc-meta__row">
                                <div class="vc-meta__label">{{ __('Tax ID') }}</div>
                                <div class="vc-meta__value mono">{{ $cardholder->tax_id ?: '—' }}</div>
                            </div>
                            <div class="vc-meta__row">
                                <div class="vc-meta__label">{{ __('Tax Country') }}</div>
                                <div class="vc-meta__value">{{ $cardholder->tax_country ?: '—' }}</div>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- 7. Employment & Source of Funds --}}
                @if($cardholder->occupation || $cardholder->employer || $cardholder->annual_income || $cardholder->source_of_funds)
                    <section class="vc-form-section">
                        <header class="vc-form-section__head">
                            <span class="vc-form-section__icon vc-form-section__icon--green"><i class="fa-solid fa-briefcase"></i></span>
                            <div class="vc-form-section__copy">
                                <div class="vc-form-section__title">{{ __('Employment & Source of Funds') }}</div>
                                <div class="vc-form-section__subtitle">{{ __('AML disclosure required by some providers.') }}</div>
                            </div>
                        </header>
                        <div class="vc-meta">
                            <div class="vc-meta__row">
                                <div class="vc-meta__label">{{ __('Occupation') }}</div>
                                <div class="vc-meta__value">{{ $cardholder->occupation ?: '—' }}</div>
                            </div>
                            <div class="vc-meta__row">
                                <div class="vc-meta__label">{{ __('Employer') }}</div>
                                <div class="vc-meta__value">{{ $cardholder->employer ?: '—' }}</div>
                            </div>
                            <div class="vc-meta__row">
                                <div class="vc-meta__label">{{ __('Annual Income') }}</div>
                                <div class="vc-meta__value mono">
                                    {{ $cardholder->annual_income ? '$'.number_format((float) $cardholder->annual_income, 2) : '—' }}
                                </div>
                            </div>
                            <div class="vc-meta__row">
                                <div class="vc-meta__label">{{ __('Source of Funds') }}</div>
                                <div class="vc-meta__value">
                                    {{ $cardholder->source_of_funds ? Str::headline(str_replace('_', ' ', $cardholder->source_of_funds)) : '—' }}
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- 8. Compliance --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon vc-form-section__icon--amber"><i class="fa-solid fa-shield-halved"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Compliance') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Self-declared status flags.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Politically Exposed Person') }}</div>
                            <div class="vc-meta__value">
                                <span class="vc-pill vc-pill--{{ $cardholder->pep_flag ? 'red' : 'green' }}">
                                    <span class="vc-pill__dot"></span>{{ $cardholder->pep_flag ? __('Yes') : __('No') }}
                                </span>
                            </div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Sanctions') }}</div>
                            <div class="vc-meta__value">
                                <span class="vc-pill vc-pill--{{ $cardholder->sanctions_flag ? 'red' : 'green' }}">
                                    <span class="vc-pill__dot"></span>{{ $cardholder->sanctions_flag ? __('Yes') : __('No') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>
            @else
                {{-- ====== Business cardholder ====== --}}

                {{-- 2. Legal Identity --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-building"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Legal Identity') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Registered entity details.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Legal Name') }}</div>
                            <div class="vc-meta__value">{{ $business?->business_name ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Trading Name') }}</div>
                            <div class="vc-meta__value">{{ $business?->trading_name ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Entity Type') }}</div>
                            <div class="vc-meta__value">
                                {{ $business?->business_type ? Str::headline(str_replace('_', ' ', $business->business_type)) : '—' }}
                            </div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Incorporation Date') }}</div>
                            <div class="vc-meta__value">{{ optional($business?->incorporation_date)->format('d M Y') ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Country of Incorporation') }}</div>
                            <div class="vc-meta__value">{{ $business?->incorporation_country ?: '—' }}</div>
                        </div>
                    </div>
                </section>

                {{-- 3. Tax & Registration --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon"><i class="fa-solid fa-scale-balanced"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Tax & Registration') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Identifiers used by KYB.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Registration Number') }}</div>
                            <div class="vc-meta__value mono">{{ $business?->registration_number ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('TIN / EIN / VAT') }}</div>
                            <div class="vc-meta__value mono">{{ $business?->tin ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Industry') }}</div>
                            <div class="vc-meta__value">{{ $business?->industry ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('MCC Code') }}</div>
                            <div class="vc-meta__value mono">{{ $business?->mcc_code ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row vc-meta__row--full">
                            <div class="vc-meta__label">{{ __('Website') }}</div>
                            <div class="vc-meta__value">
                                @if($business?->website_url)
                                    <a href="{{ $business->website_url }}" target="_blank" rel="noopener" class="vc-field__doc-link">
                                        <i class="fa-solid fa-up-right-from-square"></i> {{ $business->website_url }}
                                    </a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 4. Business Contact --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon vc-form-section__icon--green"><i class="fa-solid fa-headset"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Business Contact') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Point of contact for compliance and disputes.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Email') }}</div>
                            <div class="vc-meta__value">{{ $business?->contact_email ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Phone') }}</div>
                            <div class="vc-meta__value mono">
                                {{ trim(($business?->phone_country_code ?? '').' '.($business?->contact_phone ?? '—')) }}
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 5. Registered Address --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon vc-form-section__icon--amber"><i class="fa-solid fa-location-dot"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Registered Address') }}</div>
                            <div class="vc-form-section__subtitle">{{ __('Address on file with your registrar.') }}</div>
                        </div>
                    </header>
                    <div class="vc-meta">
                        <div class="vc-meta__row vc-meta__row--full">
                            <div class="vc-meta__label">{{ __('Street') }}</div>
                            <div class="vc-meta__value">
                                {{ $business?->address_line1 ?: '—' }}
                                @if($business?->address_line2), {{ $business->address_line2 }}@endif
                            </div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('City') }}</div>
                            <div class="vc-meta__value">{{ $business?->city ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('State') }}</div>
                            <div class="vc-meta__value">{{ $business?->state ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Postal Code') }}</div>
                            <div class="vc-meta__value">{{ $business?->postal_code ?: '—' }}</div>
                        </div>
                        <div class="vc-meta__row">
                            <div class="vc-meta__label">{{ __('Country') }}</div>
                            <div class="vc-meta__value">{{ $business?->country ?: '—' }}</div>
                        </div>
                    </div>
                </section>

                {{-- 6. Beneficial Owners --}}
                @if(! empty($owners))
                    <section class="vc-form-section">
                        <header class="vc-form-section__head">
                            <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-user-tie"></i></span>
                            <div class="vc-form-section__copy">
                                <div class="vc-form-section__title">{{ __('Beneficial Owners') }}</div>
                                <div class="vc-form-section__subtitle">{{ __('Anyone who owns or controls 25% or more of the entity.') }}</div>
                            </div>
                        </header>
                        <div class="vc-ubo-readonly">
                            @foreach($owners as $owner)
                                <div class="vc-ubo-readonly__row">
                                    <div class="vc-ubo-readonly__top">
                                        <div class="vc-ubo-readonly__name">
                                            <i class="fa-solid fa-user"></i> {{ $owner['name'] ?? '—' }}
                                        </div>
                                        <span class="vc-pill vc-pill--blue">{{ ($owner['ownership_pct'] ?? 0) }}%</span>
                                    </div>
                                    <div class="vc-ubo-readonly__meta">
                                        @if(! empty($owner['dob']))
                                            <span class="vc-ubo-readonly__meta-item">
                                                <i class="fa-regular fa-calendar"></i> {{ $owner['dob'] }}
                                            </span>
                                        @endif
                                        @if(! empty($owner['country']))
                                            <span class="vc-ubo-readonly__meta-item">
                                                <i class="fa-solid fa-flag"></i> {{ $owner['country'] }}
                                            </span>
                                        @endif
                                        @if(! empty($owner['id_type']))
                                            <span class="vc-ubo-readonly__meta-item">
                                                <i class="fa-solid fa-id-card"></i>
                                                {{ Str::headline(str_replace('_', ' ', $owner['id_type'])) }}
                                                @if(! empty($owner['id_number']))
                                                    · <span class="mono">{{ $owner['id_number'] }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif

            {{-- Footer meta --}}
            <section class="vc-form-section">
                <header class="vc-form-section__head">
                    <span class="vc-form-section__icon"><i class="fa-regular fa-clock"></i></span>
                    <div class="vc-form-section__copy">
                        <div class="vc-form-section__title">{{ __('Audit') }}</div>
                        <div class="vc-form-section__subtitle">{{ __('When this profile was created and last touched.') }}</div>
                    </div>
                </header>
                <div class="vc-meta">
                    <div class="vc-meta__row">
                        <div class="vc-meta__label">{{ __('Created At') }}</div>
                        <div class="vc-meta__value">{{ optional($cardholder->created_at)->format('d M Y, H:i') ?: '—' }}</div>
                    </div>
                    <div class="vc-meta__row">
                        <div class="vc-meta__label">{{ __('Last Updated') }}</div>
                        <div class="vc-meta__value">{{ optional($cardholder->updated_at)->format('d M Y, H:i') ?: '—' }}</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
