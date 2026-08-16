@php
    $routeName = Route::currentRouteName();
    $route = route($routeName);

    $statusFilter = !in_array($routeName, ['admin.user.active', 'admin.user.suspended']);
    $kycFilter = $routeName !== 'admin.user.kyc-unverified';
    $emailFilter = $routeName !== 'admin.user.unverified';
@endphp

<div class="admin-users-filterbar">
    <form action="{{ $route }}" method="GET" class="admin-users-filters row g-2 g-xl-3 align-items-end">
        {{-- Role Filter --}}
        <div class="admin-users-filter col-12 col-md-6 col-xl-auto">
            <x-form.select
                name="role"
                :label="__('Account Role')"
                class="form-select pe-5"
                :options="\App\Enums\UserRole::options()"
                :selected="request('role', 'all')"
            />
        </div>

        {{-- Status Filter --}}
        @if($statusFilter)
            <div class="admin-users-filter col-12 col-md-6 col-xl-auto">
                <x-form.select
                    name="status"
                    :label="__('Account Status')"
                    class="form-select pe-5"
                    :options="\App\Enums\UserStatus::options()"
                    :selected="request('status', 'all')"
                />
            </div>
        @endif

        {{-- KYC Filter --}}
        @if($kycFilter)
            <div class="admin-users-filter col-12 col-md-6 col-xl-auto">
                <x-form.select
                    name="kyc_status"
                    :label="__('KYC Status')"
                    class="form-select pe-5"
                    :options="App\Enums\KycStatus::options()"
                    :selected="request('kyc_status', 'all')"
                />
            </div>
        @endif

        {{-- Email Filter --}}
        @if($emailFilter)
            <div class="admin-users-filter col-12 col-md-6 col-xl-auto">
                <x-form.select
                    name="email_verified"
                    :label="__('Email Status')"
                    class="form-select pe-5"
                    :options="[
                        '0' => __('Unverified'),
                        '1' => __('Verified'),
                    ]"
                    :selected="request('email_verified', 'all')"
                />
            </div>
        @endif

        {{-- Search Input --}}
        <div class="admin-users-filter admin-users-filter--search col-12 col-xl">
            <label class="visually-hidden" for="admin-users-search">{{ __('Search') }}</label>
            <div class="admin-users-search">
                <i class="fa-solid fa-magnifying-glass admin-users-search__icon" aria-hidden="true"></i>
                <input type="text"
                       id="admin-users-search"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control admin-users-search__input"
                       placeholder="{{ __('Name, username, phone or email') }}"
                       aria-label="{{ __('Search') }}">
                <button type="submit" class="admin-users-search__submit" aria-label="{{ __('Search') }}">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
                @if(request()->query())
                    <a href="{{ $route }}" class="admin-users-search__reset">
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        <span>{{ __('Reset') }}</span>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>
