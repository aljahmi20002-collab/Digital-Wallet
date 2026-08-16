@php
    $avatarData = getUserAvatarDetails($user->first_name, $user->last_name);
    $roleLabel = $user->role?->title() ?? '--';
    $statusLabel = $user->status?->label() ?? '--';
    $registeredAt = optional($user->created_at)->format('M d, Y');
@endphp

<section class="user-mgmt-hero">
    <div class="user-mgmt-hero__cover">
        <a href="{{ route('admin.user.active') }}" class="user-mgmt-hero__back">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            {{ __('Back to Users') }}
        </a>
    </div>

    <div class="user-mgmt-hero__body">
        <div class="user-mgmt-hero__avatar-wrap">
            @if($user->avatar)
                <img class="user-mgmt-hero__avatar"
                     src="{{ asset($user->avatar) }}"
                     alt="{{ $user->name }}" loading="lazy">
            @else
                <span class="user-mgmt-hero__avatar-fallback {{ $avatarData['class'] }}">
                    {{ $avatarData['initials'] }}
                </span>
            @endif
        </div>

        <div class="user-mgmt-hero__identity">
            <h4 class="user-mgmt-hero__name">{{ $user->first_name . ' ' . $user->last_name }}</h4>
            <div class="user-mgmt-hero__sub">
                <span><i class="fa-solid fa-at"></i>{{ $user->username }}</span>
                @if($user->email)
                    <span><i class="fa-regular fa-envelope"></i>{{ maskSensitive($user->email) }}</span>
                @endif
                @if($user->country)
                    <span><i class="fa-solid fa-location-dot"></i>{{ $user->country }}</span>
                @endif
            </div>
            <div class="user-mgmt-hero__chips">
                <span class="um-chip um-chip--info">
                    <i class="fa-solid fa-shield-halved"></i> {{ $roleLabel }}
                </span>
                <span class="um-chip um-chip--success">
                    <i class="fa-solid fa-circle-check"></i> {{ $statusLabel }}
                </span>
                @if($registeredAt)
                    <span class="um-chip um-chip--neutral">
                        <i class="fa-regular fa-calendar"></i> {{ __('Joined :date', ['date' => $registeredAt]) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="user-mgmt-hero__actions">
            @can('custom-notify-users')
                <button type="button" class="user-mgmt-action user-mgmt-action--notify notify-user"
                        data-coreui-toggle="tooltip" title="{{ __('Notify User') }}">
                    <i class="fa fa-bell" aria-hidden="true"></i>
                </button>
            @endcan

            @can('user-balance-manage')
                <button type="button" class="user-mgmt-action user-mgmt-action--funds add-money"
                        data-coreui-toggle="tooltip" title="{{ __('Manage Funds') }}">
                    <i class="fa fa-wallet" aria-hidden="true"></i>
                </button>
            @endcan

            @can('user-login-as')
                {{--
                    target="_blank" is only added when demo mode is off. In
                    demo mode the DemoMode middleware short-circuits this
                    route with a notifyEvs() toast — that toast lives in
                    the session flash of the request that hit the route,
                    so it must redirect back into the SAME tab the admin
                    is looking at, otherwise the toast renders in a brand
                    new tab the admin never sees.
                --}}
                <a href="{{ route('admin.user.login', $user->id) }}"
                   @unless((bool) config('app.demo', false)) target="_blank" @endunless
                   class="user-mgmt-action user-mgmt-action--login"
                   data-coreui-toggle="tooltip" title="{{ __('Login as User') }}">
                    <i class="fa fa-user-shield" aria-hidden="true"></i>
                </a>
            @endcan

            @can('user-manage')
                @if($user->isUser())
                    <button type="button" class="user-mgmt-action user-mgmt-action--convert"
                            data-coreui-toggle="modal" data-coreui-target="#convertToMerchantModal"
                            data-user-id="{{ $user->id }}" data-username="{{ $user->username }}"
                            data-fullname="{{ $user->first_name . ' ' . $user->last_name }}"
                            data-user-email="{{ $user->email }}"
                            title="{{ __('Convert to Merchant') }}">
                        <i class="fas fa-exchange-alt"></i> {{ __('Convert') }}
                    </button>
                @endif
            @endcan

            @can('user-delete')
                <button type="button" class="user-mgmt-action user-mgmt-action--danger"
                        data-coreui-toggle="modal" data-coreui-target="#deleteUserModal"
                        data-user-id="{{ $user->id }}" data-username="{{ $user->username }}"
                        data-fullname="{{ $user->first_name . ' ' . $user->last_name }}"
                        data-is-merchant="{{ $user->isMerchant() }}"
                        title="{{ __('Delete User') }}">
                    <i class="fa fa-user-times"></i>
                </button>
            @endcan
        </div>
    </div>
</section>
