{{-- Dynamic content passthrough — admin-authored HTML wrapped in the golden section frame --}}
@php $locale = $locale ?? app()->getLocale(); @endphp

<section class="gdk-section">
	<div class="gdk-container">
		<div class="gdk-card gdk-dynamic-card">
			{!! $component->content_data['content'][$locale] ?? ($data['content'][$locale] ?? '') !!}
		</div>
	</div>
</section>
