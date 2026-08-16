@php
    use App\Enums\KycStatus;

    $kycSubmission = auth()->user()->kycSubmission;
    $kycStatus = $kycSubmission?->status ?? null;

    $state = match ($kycStatus) {
        KycStatus::PENDING => 'pending',
        KycStatus::APPROVED => 'approved',
        KycStatus::REJECTED => 'rejected',
        default => 'new',
    };

    $stateLabel = match ($state) {
        'pending' => __('Under Review'),
        'approved' => __('Verified'),
        'rejected' => __('Rejected'),
        default => __('Not Started'),
    };
    $stateIcon = match ($state) {
        'pending' => 'fas fa-hourglass-half',
        'approved' => 'fas fa-circle-check',
        'rejected' => 'fas fa-circle-exclamation',
        default => 'fas fa-id-card',
    };
    $stateTitle = match ($state) {
        'pending' => __('KYC review in progress'),
        'approved' => __('KYC verification complete'),
        'rejected' => __('KYC needs attention'),
        default => __('Start identity verification'),
    };
    $stateSubtitle = match ($state) {
        'pending' => __('Your KYC details are with our review team. We will update this page after the decision.'),
        'approved' => __('Your identity verification is complete. No further KYC action is required right now.'),
        'rejected' => __('Review the feedback, update your documents, and resubmit your identity details.'),
        default => __('Submit your identity details and documents to complete KYC verification.'),
    };

    $canSubmit = ! $kycSubmission || $kycStatus === KycStatus::REJECTED;
    $submittedAt = $kycSubmission?->created_at;
    $reviewedAt = $kycSubmission?->updated_at?->gt($kycSubmission->created_at) ? $kycSubmission->updated_at : null;
@endphp
@extends('frontend.user.setting.index')
@section('title', __('KYC Verification'))

@section('user_setting_content')

    <section class="kyc-verify-hero kyc-verify-hero--{{ $state }} mb-4">
        <div class="kyc-verify-hero__icon" aria-hidden="true">
            <i class="{{ $stateIcon }}"></i>
        </div>
        <div class="kyc-verify-hero__copy">
            <span class="kyc-verify-hero__eyebrow">{{ __('Identity Verification') }}</span>
            <h5 class="kyc-verify-hero__title">{{ $stateTitle }}</h5>
            <p class="kyc-verify-hero__subtitle">{{ $stateSubtitle }}</p>
        </div>
        <span class="kyc-verify-hero__badge kyc-verify-hero__badge--{{ $state }}">
            <i class="{{ $stateIcon }}" aria-hidden="true"></i>
            {{ $stateLabel }}
        </span>

        <div class="kyc-verify-hero__meta">
            <div class="kyc-verify-hero__meta-item">
                <span>{{ __('Current Status') }}</span>
                <strong>{{ $stateLabel }}</strong>
            </div>
            <div class="kyc-verify-hero__meta-item">
                <span>{{ __('Submitted') }}</span>
                <strong>{{ $submittedAt ? $submittedAt->diffForHumans() : __('Not submitted') }}</strong>
            </div>
            <div class="kyc-verify-hero__meta-item">
                <span>{{ __('Last Update') }}</span>
                <strong>{{ $reviewedAt ? $reviewedAt->diffForHumans() : __('Awaiting review') }}</strong>
            </div>
        </div>
    </section>

    {{-- Status panel with submission timeline --}}
    @if($kycSubmission)
        <section class="kyc-verify-status kyc-verify-status--{{ $state }} kyc-verify-status--timeline mb-30" aria-live="polite">
            <header class="kyc-verify-timeline-head">
                <div>
                    <span class="kyc-verify-timeline-head__eyebrow">{{ __('Verification History') }}</span>
                    <h6 class="kyc-verify-timeline-head__title">{{ __('Review timeline') }}</h6>
                </div>
                <span class="kyc-verify-header__badge kyc-verify-header__badge--{{ $state }}">
                    <span class="kyc-verify-header__badge-dot" aria-hidden="true"></span>
                    {{ $stateLabel }}
                </span>
            </header>

            <ol class="kyc-verify-timeline">
                <li class="kyc-verify-timeline__step is-done">
                    <span class="kyc-verify-timeline__dot" aria-hidden="true"></span>
                    <div class="kyc-verify-timeline__body">
                        <span class="kyc-verify-timeline__label">{{ __('Submitted') }}</span>
                        <small class="kyc-verify-timeline__meta">{{ $kycSubmission->created_at?->diffForHumans() }}</small>
                    </div>
                </li>
                <li class="kyc-verify-timeline__step {{ $state === 'pending' ? 'is-active' : 'is-done' }}">
                    <span class="kyc-verify-timeline__dot" aria-hidden="true"></span>
                    <div class="kyc-verify-timeline__body">
                        <span class="kyc-verify-timeline__label">{{ __('Under Review') }}</span>
                        <small class="kyc-verify-timeline__meta">
                            {{ $state === 'pending' ? __('In progress') : __('Completed') }}
                        </small>
                    </div>
                </li>
                <li class="kyc-verify-timeline__step {{ in_array($state, ['approved', 'rejected'], true) ? 'is-done is-' . $state : '' }}">
                    <span class="kyc-verify-timeline__dot" aria-hidden="true"></span>
                    <div class="kyc-verify-timeline__body">
                        <span class="kyc-verify-timeline__label">
                            @switch($state)
                                @case('approved') {{ __('Approved') }} @break
                                @case('rejected') {{ __('Rejected') }} @break
                                @default {{ __('Decision') }}
                            @endswitch
                        </span>
                        <small class="kyc-verify-timeline__meta">
                            {{ $reviewedAt ? $reviewedAt->diffForHumans() : __('Awaiting') }}
                        </small>
                    </div>
                </li>
            </ol>
        </section>
    @endif

    {{-- Submission form --}}
    @if($canSubmit)
        <section class="kyc-verify-form-card">
            <header class="kyc-verify-form-card__header">
                <div class="kyc-verify-form-card__heading">
                    <h6 class="kyc-verify-form-card__title">
                        {{ $kycStatus === KycStatus::REJECTED ? __('Resubmit your verification') : __('Start your verification') }}
                    </h6>
                    <p class="kyc-verify-form-card__subtitle">
                        {{ __('Provide accurate information to avoid delays in review.') }}
                    </p>
                </div>
                <span class="kyc-verify-form-card__hint">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    {{ __('Your data is encrypted and private.') }}
                </span>
            </header>

            <form id="kyc-verify-form" action="{{ route('user.settings.kyc.submit') }}" method="POST"
                  enctype="multipart/form-data" class="kyc-verify-form-card__body">
                @csrf

                <div class="kyc-verify-field">
                    <label for="template-select" class="kyc-verify-field__label">
                        <span class="kyc-verify-field__step">1</span>
                        {{ __('Verification Type') }}
                    </label>
                    <p class="kyc-verify-field__hint">
                        {{ __('Choose the document type that matches what you have on hand.') }}
                    </p>
                    <div class="single-select-inner style-border">
                        <select class="form-select" name="template_id" id="template-select" required>
                            <option disabled selected>{{ __('Select Type') }}</option>
                            @foreach($kycTemplates as $kycTemplate)
                                <option value="{{ $kycTemplate->id }}">{{ $kycTemplate->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="kyc-verify-field">
                    <label class="kyc-verify-field__label">
                        <span class="kyc-verify-field__step">2</span>
                        {{ __('Required Information') }}
                    </label>
                    <p class="kyc-verify-field__hint">
                        {{ __('Fill in every required field and upload clear, legible documents.') }}
                    </p>
                    <div id="template-details" class="kyc-verify-template-details">
                        <div class="kyc-verify-template-empty" data-kyc-empty>
                            <i class="fas fa-file-lines" aria-hidden="true"></i>
                            <span>{{ __('Select a verification type above to load the required fields.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="kyc-verify-field">
                    <label for="kyc-note" class="kyc-verify-field__label">
                        <span class="kyc-verify-field__step">3</span>
                        {{ __('Additional Notes') }}
                        <span class="kyc-verify-field__optional">{{ __('Optional') }}</span>
                    </label>
                    <p class="kyc-verify-field__hint">
                        {{ __('Anything the reviewer should know about your submission.') }}
                    </p>
                    <div class="single-input-inner style-border">
                        <textarea class="rounded" name="note" id="kyc-note" rows="4"
                                  placeholder="{{ __('e.g. name on document differs slightly due to spelling.') }}"></textarea>
                    </div>
                </div>

                <footer class="kyc-verify-form-card__footer">
                    <p class="kyc-verify-form-card__legal">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        {{ __('By submitting, you confirm the information provided is accurate and belongs to you.') }}
                    </p>
                    <button type="submit" class="btn btn-primary kyc-verify-form-card__submit">
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>
                        <span>{{ __('Submit for Review') }}</span>
                    </button>
                </footer>
            </form>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        "use strict";

        (function () {
            const $select  = $('#template-select');
            const $details = $('#template-details');
            const urlTemplate = @json(route('user.settings.kyc.template.details', ':id'));

            const emptyState = `
                <div class="kyc-verify-template-empty" data-kyc-empty>
                    <i class="fas fa-file-lines" aria-hidden="true"></i>
                    <span>{{ __('Select a verification type above to load the required fields.') }}</span>
                </div>
            `;

            const loadingState = `
                <div class="kyc-verify-template-loading">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                    <span>{{ __('Loading required fields...') }}</span>
                </div>
            `;

            $select.on('change', function () {
                const templateId = $(this).val();

                if (!templateId) {
                    $details.html(emptyState);
                    return;
                }

                $details.html(loadingState);

                $.get(urlTemplate.replace(':id', templateId))
                    .done(response => $details.html(response))
                    .fail(() => {
                        $details.html(`
                            <div class="kyc-verify-template-empty kyc-verify-template-empty--error">
                                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                                <span>{{ __('Unable to load fields. Please try again.') }}</span>
                            </div>
                        `);
                    });
            });
        })();
    </script>
@endpush
