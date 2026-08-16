@php
	$siteName  = setting('site_title', 'DigiKash');
	$backLabel = $backLabel ?? __('Back to Home');
	$backUrl   = $backUrl   ?? route('home');
@endphp

<div class="form-top">
	<a href="{{ route('home') }}" class="logo" aria-label="{{ $siteName }}">
		<span class="logo__mark" aria-hidden="true"></span>
		<span class="logo__type">{!! preg_replace('/^(.{4,5})/u', '$1<em>', e($siteName)) !!}</em></span>
	</a>
	<a href="{{ $backUrl }}" class="back-link">
		<i class="fa-solid fa-arrow-left"></i> {{ $backLabel }}
	</a>
</div>
