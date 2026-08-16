@php
	/**
	 * Golden portal-switch row.
	 *
	 * @var string $current  one of: 'user' | 'merchant' | 'agent'
	 * @var string $page     'login' | 'register' | 'forgot' (drives target routes)
	 */
	$current = $current ?? 'user';
	$page    = $page    ?? 'login';

	$routes = [
		'login'    => ['user.login',             'merchant.login',             'agent.login'],
		'register' => ['user.register',          'merchant.register',          'agent.register'],
		'forgot'   => ['user.password.request',  'merchant.password.request',  'agent.password.request'],
	];

	$portals = [
		'user'     => ['label' => __('User'),     'route' => $routes[$page][0] ?? 'user.login'],
		'merchant' => ['label' => __('Merchant'), 'route' => $routes[$page][1] ?? 'merchant.login'],
		'agent'    => ['label' => __('Agent'),    'route' => $routes[$page][2] ?? 'agent.login'],
	];
@endphp

<div class="portal-switch">
	<span>{{ __('Portal:') }}</span>
	@foreach($portals as $key => $portal)
		@if($key === $current)
			<span class="portal-pill is-active">{{ $portal['label'] }}</span>
		@elseif(Route::has($portal['route']))
			<a href="{{ route($portal['route']) }}" class="portal-pill">{{ $portal['label'] }}</a>
		@endif
	@endforeach
</div>
