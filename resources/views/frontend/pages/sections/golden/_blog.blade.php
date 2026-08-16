@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow    = $data['eyebrow'][$locale]     ?? $data['subheading'][$locale] ?? '';
	$heading    = $data['heading'][$locale]     ?? '';
	$buttonText = $data['button_text'][$locale] ?? __('View All');
	$buttonUrl  = $data['button_url']           ?? route('blog.index');

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));

	$blogs = \App\Models\Blog::activeCached()->take(3);
	$lead  = $blogs->first();
	$rest  = $blogs->skip(1)->values();
@endphp

<section class="gdk-section" id="blog">
	<div class="gdk-container">
		<div class="gdk-blog__head gdk-reveal">
			<div>
				@if($eyebrow)
					<div class="gdk-eyebrow">{{ $eyebrow }}</div>
				@endif
				@if($heading)
					<h2 class="gdk-h-section gdk-section-head__title">{!! $renderedHeading !!}</h2>
				@endif
			</div>
			<a href="{{ $buttonUrl }}" class="gdk-link">{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i></a>
		</div>

		@if($blogs->isEmpty())
			<x-user-not-found
				:title="__('No dispatches yet')"
				:message="__('Our editorial team is preparing the first letter. Check back soon.')"
				icon="fa-feather"
				:action-url="route('home')"
				:action-label="__('Return to Home')"
				action-icon="fa-arrow-left"
			/>
		@else
			<div class="gdk-blog__grid">
				@if($lead)
					<article class="gdk-article gdk-reveal">
						<div class="gdk-article__cover" style="--gdk-img: url('{{ asset($lead->thumbnail) }}')">
							<div class="gdk-article__chip">
								<strong>{{ $lead->created_at->format('d') }}</strong>
								<small>{{ strtoupper($lead->created_at->format('M · y')) }}</small>
							</div>
						</div>
						<div class="gdk-article__body">
							@if($lead->category)
								<div class="gdk-article__cat">{{ $lead->category->name_text }}</div>
							@endif
							<h3 class="gdk-article__title">
								<a href="{{ route('blog.details', $lead->slug) }}" class="gdk-article__link">
									{{ $lead->title_text }}
								</a>
							</h3>
							<div class="gdk-article__meta">
								<span><i class="fa-regular fa-user"></i>{{ $lead->author->name ?? __('Editor') }}</span>
								<span><i class="fa-regular fa-clock"></i>{{ $lead->read_time ?? __(':n min', ['n' => 5]) }}</span>
							</div>
						</div>
					</article>
				@endif

				<div class="gdk-blog__side">
					@foreach($rest as $blog)
						<article class="gdk-article gdk-article--mini gdk-reveal">
							<div class="gdk-article__cover" style="--gdk-img: url('{{ asset($blog->thumbnail) }}')">
								<div class="gdk-article__chip">
									<strong>{{ $blog->created_at->format('d') }}</strong>
									<small>{{ strtoupper($blog->created_at->format('M · y')) }}</small>
								</div>
							</div>
							<div class="gdk-article__body">
								@if($blog->category)
									<div class="gdk-article__cat">{{ $blog->category->name_text }}</div>
								@endif
								<h3 class="gdk-article__title">
									<a href="{{ route('blog.details', $blog->slug) }}" class="gdk-article__link">
										{{ Str::limit($blog->title_text, 70) }}
									</a>
								</h3>
								<div class="gdk-article__meta">
									<span><i class="fa-regular fa-clock"></i>{{ $blog->read_time ?? __(':n min', ['n' => 4]) }}</span>
								</div>
							</div>
						</article>
					@endforeach
				</div>
			</div>
		@endif
	</div>
</section>
