@php
	$gdkLanguages       = $languages ?? collect();
	$gdkCurrentLanguage = $gdkLanguages->firstWhere('code', app()->getLocale()) ?? $gdkLanguages->first();
@endphp

@if($gdkLanguages->count() > 0 && $gdkCurrentLanguage)
	<div class="gdk-lang" data-gdk-lang>
		<button type="button" class="gdk-lang__trigger" data-gdk-lang-trigger aria-haspopup="listbox" aria-expanded="false">
			@if($gdkCurrentLanguage->flag)
				<img src="{{ asset($gdkCurrentLanguage->flag) }}" alt="" class="gdk-lang__flag" loading="lazy">
			@endif
			<span class="gdk-lang__code">{{ strtoupper($gdkCurrentLanguage->code) }}</span>
			<i class="fa-solid fa-chevron-down gdk-lang__caret" aria-hidden="true"></i>
		</button>

		<ul class="gdk-lang__menu" role="listbox" aria-label="{{ __('Select language') }}">
			@foreach($gdkLanguages as $language)
				<li role="option" aria-selected="{{ $language->code === $gdkCurrentLanguage->code ? 'true' : 'false' }}">
					<a href="{{ route('locale-set', $language->code) }}"
					   class="gdk-lang__item {{ $language->code === $gdkCurrentLanguage->code ? 'is-active' : '' }}">
						@if($language->flag)
							<img src="{{ asset($language->flag) }}" alt="" class="gdk-lang__flag" loading="lazy">
						@endif
						<span class="gdk-lang__name">{{ $language->name }}</span>
						@if($language->code === $gdkCurrentLanguage->code)
							<i class="fa-solid fa-check gdk-lang__check" aria-hidden="true"></i>
						@endif
					</a>
				</li>
			@endforeach
		</ul>
	</div>
@endif
