@extends('backend.p2p.layout')
@php
    $countryOptions = collect(getCountries())
        ->filter(fn ($country) => ! empty($country['code']))
        ->sortBy(fn ($country) => (string) ($country['name'] ?? ''))
        ->values();
@endphp

@section('title', __('P2P Payment Methods'))

@section('p2p_title')
    {{ __('Payment Methods') }}
@endsection

@section('p2p_icon', 'payment')

@section('p2p_action')
    @can('p2p-method-manage')
        <a href="#p2p_add_method_modal" data-coreui-toggle="modal" class="fb-btn fb-btn--primary fb-btn--sm">
            <x-icon name="add" height="14" width="14"/>
            <span>@lang('Add method')</span>
        </a>
    @endcan
@endsection

@php
    $methodsTotal    = method_exists($methods, 'total') ? (int) $methods->total() : $methods->count();
    $methodItems     = collect($methods->items() ?? $methods);
    $activeMethods   = $methodItems->where('status', true)->count();
    $inactiveMethods = max(0, $methodsTotal - $activeMethods);

    $logoVariant = function ($name) {
        $n = strtolower((string) $name);
        if (str_contains($n, 'bank') || str_contains($n, 'wire') || str_contains($n, 'transfer')) {
            return 'pm-card__logo--info';
        }
        if (str_contains($n, 'mobile') || str_contains($n, 'wallet') || str_contains($n, 'pay')) {
            return 'pm-card__logo--success';
        }
        if (str_contains($n, 'card') || str_contains($n, 'visa') || str_contains($n, 'master')) {
            return '';
        }
        if (str_contains($n, 'cash')) {
            return 'pm-card__logo--info';
        }
        if (str_contains($n, 'crypto') || str_contains($n, 'usdt') || str_contains($n, 'btc')) {
            return 'pm-card__logo--warning';
        }

        return 'pm-card__logo--neutral';
    };
@endphp

@section('p2p_content')
    <div class="p2p-refresh">
        <section class="fb-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">@lang('Configured rails')</span>
                    <h5>@lang('Available payment rails')</h5>
                </div>
                <div class="fb-card__meta">
                    <span class="fb-pill fb-pill--neutral">
                        <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
                        <span>{{ number_format($methodsTotal) }} {{ trans_choice('total|total', $methodsTotal) }}</span>
                    </span>
                    @if($activeMethods > 0)
                        <span class="fb-pill fb-pill--success">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <span>{{ number_format($activeMethods) }} @lang('active')</span>
                        </span>
                    @endif
                    @if($inactiveMethods > 0)
                        <span class="fb-pill fb-pill--neutral">
                            <i class="fa-solid fa-circle-minus" aria-hidden="true"></i>
                            <span>{{ number_format($inactiveMethods) }} @lang('inactive')</span>
                        </span>
                    @endif
                </div>
            </div>

            @if($methodsTotal > 0)
                <div class="pm-grid">
                    @foreach($methods as $method)
                        @php
                            $methodPayload = [
                                'id'           => $method->id,
                                'name'         => $method->name,
                                'logo_url'     => $method->logo ? asset('storage/'.$method->logo) : null,
                                'country'      => $method->country,
                                'instructions' => $method->instructions,
                                'status'       => (int) $method->status,
                            ];
                            $isActive = (bool) $method->status;
                            $countryLabel = $method->country ? (getCountryDisplayLabel($method->country) ?? $method->country) : __('All countries');
                        @endphp
                        <div class="pm-card {{ $isActive ? '' : 'is-inactive' }}">
                            <div class="pm-card__head">
                                <div class="pm-card__logo {{ $logoVariant($method->name) }}">
                                    @if(!empty($method->logo))
                                        <img src="{{ asset('storage/'.$method->logo) }}" alt="{{ $method->name }}" loading="lazy">
                                    @else
                                        {{ strtoupper(substr((string) $method->name, 0, 2)) }}
                                    @endif
                                </div>
                                <div class="pm-card__body">
                                    <span class="pm-card__name">{{ title($method->name) }}</span>
                                    <span class="pm-card__country">
                                        <i class="fa-solid {{ $method->country ? 'fa-location-dot' : 'fa-earth-americas' }}" aria-hidden="true"></i>
                                        <span>{{ $countryLabel }}</span>
                                    </span>
                                </div>
                            </div>

                            <div class="pm-card__divider" aria-hidden="true"></div>

                            <div class="pm-card__foot">
                                <span class="pm-card__status {{ $isActive ? '' : 'pm-card__status--off' }}">
                                    {{ $isActive ? __('Active') : __('Inactive') }}
                                </span>
                                @can('p2p-method-manage')
                                    <div class="pm-card__actions">
                                        <button type="button"
                                                class="fb-btn fb-btn--ghost fb-btn--sm p2p-method-manage"
                                                data-method='@json($methodPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP)'
                                                data-update-url="{{ route('admin.p2p.methods.update', $method) }}">
                                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                                            <span>@lang('Manage')</span>
                                        </button>
                                        <a href="javascript:void(0)" class="fb-btn fb-btn--ghost fb-btn--sm fb-btn--icon delete"
                                           data-url="{{ route('admin.p2p.methods.destroy', $method) }}"
                                           aria-label="{{ __('Delete method') }}"
                                           style="color: var(--color-danger);">
                                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($methods->hasPages())
                    <div class="fb-card__footer pa-table__foot">
                        <span>{{ __('Showing :n of :t methods', ['n' => number_format(count($methods->items())), 't' => number_format($methodsTotal)]) }}</span>
                        {{ $methods->links() }}
                    </div>
                @endif
            @else
                <x-admin-not-found
                    :title="__('No payment methods yet')"
                    :message="__('Configure your first payment rail (bKash, Bank transfer, Card, USDT, etc.) to let traders create offers.')"
                    icon="fa-credit-card"
                    @can('p2p-method-manage')
                        :action-url="'#p2p_add_method_modal'"
                        :action-label="__('Add first method')"
                        action-icon="fa-plus"
                    @endcan
                />
            @endif
        </section>
    </div>

    {{-- Create Payment Method --}}
    <div class="modal fade p2p-refresh" id="p2p_add_method_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content pm-modal">
                @php($isCreate = old('form_type') === 'create_method')
                <form method="POST" action="{{ route('admin.p2p.methods.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="form_type" value="create_method">

                    <div class="pm-modal__header">
                        <div class="pm-modal__title">
                            <span class="pm-modal__title-icon"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                            <div>
                                <h5>@lang('Add payment method')</h5>
                                <span class="pm-modal__title-sub">@lang('Configure a new payment rail')</span>
                            </div>
                        </div>
                        <button type="button" class="pm-modal__close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="pm-modal__body">
                        <div class="pm-status is-on" data-pm-status-banner>
                            <div class="pm-status__body">
                                <span class="pm-status__eyebrow">@lang('Status')</span>
                                <span class="pm-status__label" data-pm-status-label>@lang('Active · accepting trades')</span>
                                <span class="pm-status__hint" data-pm-status-hint>@lang('Traders can use this rail to fund or settle orders.')</span>
                            </div>
                            <label class="pm-switch">
                                <input type="hidden" name="status" value="0">
                                <input
                                    type="checkbox"
                                    id="p2p_method_status"
                                    name="status"
                                    value="1"
                                    data-pm-status-input
                                    {{ old('form_type') === 'create_method' ? (old('status', '1') ? 'checked' : '') : 'checked' }}
                                >
                                <span class="pm-switch__track"></span>
                            </label>
                        </div>

                        <section class="pm-section">
                            <div class="pm-section__head">
                                <span class="pm-section__title">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    @lang('Basic details')
                                </span>
                            </div>
                            <div class="pm-section__body">
                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label for="p2p_method_name">@lang('Name') <span class="req" aria-hidden="true">*</span></label>
                                        <input
                                            type="text"
                                            name="name"
                                            id="p2p_method_name"
                                            class="form-control {{ $isCreate && $errors->has('name') ? 'is-invalid' : '' }}"
                                            value="{{ old('form_type') === 'create_method' ? old('name') : '' }}"
                                            placeholder="{{ __('e.g. bKash, Bank Wire') }}"
                                            required
                                        >
                                        @if($isCreate)
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="pm-field">
                                        <label for="p2p_method_country">@lang('Country')</label>
                                        <select
                                            name="country"
                                            id="p2p_method_country"
                                            class="form-select {{ $isCreate && $errors->has('country') ? 'is-invalid' : '' }}"
                                        >
                                            <option value="">@lang('All countries')</option>
                                            @foreach($countryOptions as $country)
                                                <option value="{{ strtoupper((string) $country['code']) }}" @selected(old('form_type') === 'create_method' && old('country') === strtoupper((string) $country['code']))>
                                                    {{ getCountryDisplayLabel((string) $country['code']) ?? title((string) $country['name']) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($isCreate)
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="pm-field" style="grid-column: 1 / -1;">
                                        <label>@lang('Logo')</label>
                                        <label class="pm-logo-zone">
                                            <span class="pm-logo-zone__preview">
                                                <img src="" alt="" class="d-none" loading="lazy">
                                                <i class="fa-solid fa-image" aria-hidden="true"></i>
                                            </span>
                                            <span class="pm-logo-zone__body">
                                                <span class="pm-logo-zone__title" data-default="{{ __('No logo selected') }}">@lang('No logo selected')</span>
                                                <span class="pm-logo-zone__hint" data-default="{{ __('Click or drop an image (max 2 MB, PNG/JPG/SVG)') }}">@lang('Click or drop an image (max 2 MB, PNG/JPG/SVG)')</span>
                                            </span>
                                            <span class="pm-logo-zone__action">@lang('Browse')</span>
                                            <input
                                                type="file"
                                                name="logo"
                                                id="p2p_method_logo"
                                                class="pm-logo-zone__file"
                                                accept="image/*"
                                            >
                                        </label>
                                        @if($isCreate)
                                            @error('logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="pm-field" style="grid-column: 1 / -1;">
                                        <label for="p2p_method_instructions">@lang('Instructions')</label>
                                        <textarea
                                            name="instructions"
                                            id="p2p_method_instructions"
                                            class="form-control {{ $isCreate && $errors->has('instructions') ? 'is-invalid' : '' }}"
                                            rows="2"
                                            placeholder="{{ __('Notes shown to traders when they fund or settle with this method.') }}"
                                        >{{ old('form_type') === 'create_method' ? old('instructions') : '' }}</textarea>
                                        @if($isCreate)
                                            @error('instructions')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-section">
                            <div class="pm-section__head">
                                <span class="pm-section__title">
                                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                                    @lang('Account fields')
                                </span>
                                <span class="pm-section__hint">@lang('What traders fill in')</span>
                            </div>
                            <div class="pm-section__body pm-section__body--flush p2p-method-runtime-anchor"></div>
                        </section>
                    </div>

                    <div class="pm-modal__footer">
                        <button type="button" class="fb-btn fb-btn--ghost fb-btn--sm" data-coreui-dismiss="modal">
                            @lang('Cancel')
                        </button>
                        <button class="fb-btn fb-btn--primary fb-btn--sm" type="submit">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>@lang('Save method')</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Manage Payment Method --}}
    <div class="modal fade p2p-refresh" id="p2p_manage_method_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content pm-modal">
                @php($isUpdate = old('form_type') === 'update_method')
                <form method="POST" action="" id="p2p_manage_method_form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="update_method">
                    <input type="hidden" name="method_id" id="p2p_manage_method_id" value="">

                    <div class="pm-modal__header">
                        <div class="pm-modal__title">
                            <span class="pm-modal__title-icon"><i class="fa-solid fa-credit-card" aria-hidden="true"></i></span>
                            <div>
                                <h5>@lang('Manage payment method')</h5>
                                <span class="pm-modal__title-sub">@lang('Edit rail details and account fields')</span>
                            </div>
                        </div>
                        <button type="button" class="pm-modal__close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="pm-modal__body">
                        <div class="pm-status" data-pm-status-banner>
                            <div class="pm-status__body">
                                <span class="pm-status__eyebrow">@lang('Status')</span>
                                <span class="pm-status__label" data-pm-status-label>@lang('Inactive · hidden from traders')</span>
                                <span class="pm-status__hint" data-pm-status-hint>@lang('No new orders use this rail. Existing offers stay locked.')</span>
                            </div>
                            <label class="pm-switch">
                                <input type="hidden" name="status" value="0">
                                <input
                                    type="checkbox"
                                    id="p2p_manage_status"
                                    name="status"
                                    value="1"
                                    data-pm-status-input
                                >
                                <span class="pm-switch__track"></span>
                            </label>
                        </div>

                        <section class="pm-section">
                            <div class="pm-section__head">
                                <span class="pm-section__title">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    @lang('Basic details')
                                </span>
                            </div>
                            <div class="pm-section__body">
                                <div class="pm-grid-2">
                                    <div class="pm-field">
                                        <label for="p2p_manage_name">@lang('Name') <span class="req" aria-hidden="true">*</span></label>
                                        <input
                                            type="text"
                                            name="name"
                                            id="p2p_manage_name"
                                            class="form-control {{ $isUpdate && $errors->has('name') ? 'is-invalid' : '' }}"
                                            value="{{ old('form_type') === 'update_method' ? old('name') : '' }}"
                                            required
                                        >
                                        @if($isUpdate)
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="pm-field">
                                        <label for="p2p_manage_country">@lang('Country')</label>
                                        <select
                                            name="country"
                                            id="p2p_manage_country"
                                            class="form-select {{ $isUpdate && $errors->has('country') ? 'is-invalid' : '' }}"
                                        >
                                            <option value="">@lang('All countries')</option>
                                            @foreach($countryOptions as $country)
                                                <option value="{{ strtoupper((string) $country['code']) }}" @selected(old('form_type') === 'update_method' && old('country') === strtoupper((string) $country['code']))>
                                                    {{ getCountryDisplayLabel((string) $country['code']) ?? title((string) $country['name']) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($isUpdate)
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="pm-field" style="grid-column: 1 / -1;">
                                        <label>@lang('Logo')</label>
                                        <label class="pm-logo-zone">
                                            <span class="pm-logo-zone__preview">
                                                <img id="p2p_manage_logo_preview" src="" alt="" class="d-none" loading="lazy">
                                                <i id="p2p_manage_logo_fallback" class="fa-solid fa-wallet" aria-hidden="true"></i>
                                            </span>
                                            <span class="pm-logo-zone__body">
                                                <span class="pm-logo-zone__title" data-default="{{ __('No logo uploaded') }}">@lang('No logo uploaded')</span>
                                                <span class="pm-logo-zone__hint" data-default="{{ __('Click or drop an image (max 2 MB)') }}">@lang('Click or drop an image (max 2 MB)')</span>
                                            </span>
                                            <span class="pm-logo-zone__action">@lang('Change')</span>
                                            <input
                                                type="file"
                                                name="logo"
                                                id="p2p_manage_logo"
                                                class="pm-logo-zone__file {{ $isUpdate && $errors->has('logo') ? 'is-invalid' : '' }}"
                                                accept="image/*"
                                            >
                                        </label>
                                        @if($isUpdate)
                                            @error('logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="pm-field" style="grid-column: 1 / -1;">
                                        <label for="p2p_manage_instructions">@lang('Instructions')</label>
                                        <textarea
                                            name="instructions"
                                            id="p2p_manage_instructions"
                                            class="form-control {{ $isUpdate && $errors->has('instructions') ? 'is-invalid' : '' }}"
                                            rows="2"
                                        >{{ old('form_type') === 'update_method' ? old('instructions') : '' }}</textarea>
                                        @if($isUpdate)
                                            @error('instructions')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="pm-section">
                            <div class="pm-section__head">
                                <span class="pm-section__title">
                                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                                    @lang('Account fields')
                                </span>
                                <span class="pm-section__hint">@lang('What traders fill in')</span>
                            </div>
                            <div class="pm-section__body pm-section__body--flush p2p-method-runtime-anchor"></div>
                        </section>
                    </div>

                    <div class="pm-modal__footer">
                        <button type="button" class="fb-btn fb-btn--ghost fb-btn--sm" data-coreui-dismiss="modal">
                            @lang('Cancel')
                        </button>
                        <button class="fb-btn fb-btn--primary fb-btn--sm" type="submit">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>@lang('Save changes')</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('backend.p2p.payment_methods.partials._scripts')
@endpush
