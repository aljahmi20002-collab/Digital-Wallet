@php
    /**
     * Portal switcher used at the bottom of every auth page.
     *
     * @var string  $current   Active portal — 'user' | 'merchant' | 'agent'
     * @var string  $page      Auth page key used to build routes —
     *                         'login' | 'register' | 'password.request'
     * @var ?string $heading   Optional heading shown above the switcher
     */
    $heading ??= __('Switch portal');
    $agentEnabled = (bool) setting('agent_program_enabled', true);

    $portals = [
        'user' => [
            'route' => 'user.'.$page,
            'icon'  => 'fa-duotone fa-user',
            'label' => __('User'),
            'show'  => true,
        ],
        'merchant' => [
            'route' => 'merchant.'.$page,
            'icon'  => 'fa-duotone fa-store',
            'label' => __('Merchant'),
            'show'  => true,
        ],
        'agent' => [
            'route' => 'agent.'.$page,
            'icon'  => 'fa-duotone fa-user-tie',
            'label' => __('Agent'),
            // Hide the Agent tile entirely when the program is disabled
            // so users don't tap into a 404. Note: when admin disables
            // the program, the agent auth pages 404 anyway, so the user
            // can never land on a page where $current === 'agent'.
            'show'  => $agentEnabled,
        ],
    ];
@endphp

<div class="auth-divider"></div>
<p class="auth-helper-text">{{ $heading }}</p>
<nav class="auth-portal-switch" aria-label="{{ $heading }}">
    @foreach($portals as $key => $portal)
        @if($portal['show'])
            <a href="{{ route($portal['route']) }}"
               class="portal-tile is-{{ $key }}{{ $current === $key ? ' is-active' : '' }}"
               @if($current === $key) aria-current="page" @endif>
                <i class="{{ $portal['icon'] }}"></i>
                <span>{{ $portal['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
