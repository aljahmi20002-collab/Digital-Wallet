@php
	$authUser = auth()->user();
	$initials = strtoupper(substr((string) $authUser?->first_name, 0, 1).substr((string) $authUser?->last_name, 0, 1));
	$firstName = title($authUser?->first_name ?: ($authUser?->username ?: __('User')));

	$hour = now()->hour;
	$greeting = match (true) {
		$hour < 12 => __('Good morning'),
		$hour < 18 => __('Good afternoon'),
		default => __('Good evening'),
	};
@endphp

<section class="ud-hero">
	<div class="ud-hero__left">
		@if($authUser && ! empty($authUser->avatar))
			<div class="ud-hero__avatar ud-hero__avatar--img">
				<img src="{{ asset($authUser->avatar_alt) }}" alt="{{ $firstName }}" loading="lazy">
			</div>
		@else
			<div class="ud-hero__avatar">{{ $initials !== '' ? $initials : 'U' }}</div>
		@endif
		
		<div class="ud-hero__copy">
			<span class="ud-hero__eyebrow">{{ $greeting }}</span>
			<h2 class="ud-hero__title">{{ __('Welcome back, :name', ['name' => $firstName]) }}</h2>
			<p class="ud-hero__subtitle">{{ __("Here's how your wallets are doing today.") }}</p>
		</div>
	</div>
	
	<div class="ud-hero__actions">
        <span class="ud-hero__date">
            <i class="fas fa-calendar-day" aria-hidden="true"></i>
            {{ now()->translatedFormat('l, j F Y') }}
        </span>
		<a href="{{ route('user.wallet.index') }}" class="ud-hero__btn ud-hero__btn--ghost">
			<i class="fas fa-wallet"></i> @lang('Wallets')
		</a>
		<a href="{{ route('user.transaction.index') }}" class="ud-hero__btn ud-hero__btn--primary">
			<i class="fas fa-exchange-alt"></i> @lang('Transactions')
		</a>
	</div>
</section>
