@php
	$gdkSupportEmail = setting('support_email');
	$gdkSupportPhone = setting('support_phone');
@endphp

<div class="gdk-topstrip">
	<div class="gdk-container">
		<div class="gdk-topstrip__inner">
			<div class="gdk-topstrip__left">
				@if($gdkSupportEmail)
					<a href="mailto:{{ $gdkSupportEmail }}"><i class="fa-regular fa-envelope"></i>{{ $gdkSupportEmail }}</a>
				@endif
				@if($gdkSupportPhone)
					<a href="tel:{{ $gdkSupportPhone }}"><i class="fa-solid fa-phone"></i>{{ $gdkSupportPhone }}</a>
				@endif
			</div>
			<div class="gdk-topstrip__right">
				@include('frontend.layouts.golden.partials._language_switcher')

				@if(isset($socials) && $socials->isNotEmpty())
					<div class="gdk-socials">
						<span class="gdk-socials__label">{{ __('Follow') }}</span>
						@foreach($socials as $social)
							<a href="{{ $social->url }}" target="{{ $social->target }}" aria-label="{{ $social->name ?? '' }}">
								<i class="{{ $social->icon_class }}"></i>
							</a>
						@endforeach
					</div>
				@endif
			</div>
		</div>
	</div>
</div>
