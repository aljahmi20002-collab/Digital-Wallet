@php
	use App\Models\WalletEarnPlan;

	$locale = $locale ?? app()->getLocale();

	$eyebrow     = $data['eyebrow'][$locale]     ?? $data['subheading'][$locale] ?? __('Grow Your Capital');
	$heading     = $data['heading'][$locale]     ?? __('Wallet Earn __Programmes.__');
	$description = $data['description'][$locale] ?? __('Three curated yield instruments, each calibrated to a different temperament.');

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));

	$plans = WalletEarnPlan::query()->active()->orderBy('sort_order')->with('currency')->get();
@endphp

<section class="gdk-section" id="earn">
	<div class="gdk-container">
		<div class="gdk-earn__head gdk-reveal">
			<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			<h2 class="gdk-h-section">{!! $renderedHeading !!}</h2>
			<p class="gdk-lead gdk-lead--centered">{!! nl2br(e($description)) !!}</p>
		</div>

		@if($plans->isEmpty())
			<p class="gdk-empty">{{ __('No earning plans available at the moment.') }}</p>
		@else
			<div class="gdk-earn__grid">
				@foreach($plans as $plan)
					@php
						$isFeatured = $plan->is_featured;
						$badge      = $plan->planBadgeLabel();
						$currency   = $plan->currency_id ? $plan->currency->code : __('All Wallets');
					@endphp
					<article class="gdk-plan {{ $isFeatured ? 'gdk-plan--featured' : '' }} gdk-reveal">
						@if($isFeatured && $badge)
							<span class="gdk-plan__badge"><i class="fa-solid fa-crown"></i>{{ $badge }}</span>
						@endif

						<div class="gdk-plan__head">
							<span class="gdk-iconring {{ $isFeatured ? 'gdk-iconring--filled' : '' }}">
								<i class="fa-solid fa-vault"></i>
							</span>
							<div>
								<h3 class="gdk-plan__name">{{ $plan->name }}</h3>
								<span class="gdk-plan__tag">{{ $plan->description ?: __('Earn scheduled rewards while your principal stays locked.') }}</span>
							</div>
						</div>

						<div class="gdk-plan__rate">{{ number_format((float) $plan->profit_rate, 2) }}<span class="pct">%</span></div>
						<div class="gdk-plan__rate-rule"></div>
						<div class="gdk-plan__rate-lbl">{{ $plan->profit_type->label() }}</div>

						<div class="gdk-plan__pills">
							<span class="gdk-pill {{ $isFeatured ? 'gdk-pill--gold' : '' }}">{{ $currency }}</span>
							<span class="gdk-pill {{ $isFeatured ? 'gdk-pill--gold' : '' }}">{{ $plan->durationLabel() }}</span>
							<span class="gdk-pill {{ $isFeatured ? 'gdk-pill--gold' : '' }}">{{ $plan->payout_frequency->label() }}</span>
						</div>

						<div class="gdk-plan__details">
							<div class="gdk-plan__det">
								<i class="fa-solid fa-lock"></i>
								<div>
									<small>{{ __('Range') }}</small>
									<span>{{ $plan->amountRangeLabel() }}</span>
								</div>
							</div>
							<div class="gdk-plan__det">
								<i class="fa-solid fa-rotate"></i>
								<div>
									<small>{{ __('Payout') }}</small>
									<span>{{ $plan->payout_frequency->label() }}</span>
								</div>
							</div>
							<div class="gdk-plan__det">
								<i class="fa-solid fa-shield-halved"></i>
								<div>
									<small>{{ __('Principal') }}</small>
									<span>{{ $plan->return_principal ? __('Returned') : __('Not returned') }}</span>
								</div>
							</div>
							<div class="gdk-plan__det">
								<i class="fa-solid fa-bolt"></i>
								<div>
									<small>{{ __('Approval') }}</small>
									<span>{{ $plan->auto_approve ? __('Auto') : __('Manual') }}</span>
								</div>
							</div>
						</div>

						<ul class="gdk-plan__bullets">
							<li><i class="fa-solid fa-check"></i>{{ $plan->currency_id ? $plan->currency->code.' '.__('only') : __('All currencies') }}</li>
							<li><i class="fa-solid fa-check"></i>{{ $plan->auto_approve ? __('Auto-approved') : __('Manual review') }}</li>
							<li><i class="fa-solid fa-check"></i>{{ $plan->return_principal ? __('Principal returned') : __('Principal locked') }}</li>
						</ul>

						<a href="{{ route('user.wallet-earn.plans') }}" class="gdk-btn {{ $isFeatured ? 'gdk-btn--filled' : '' }} gdk-plan__cta">
							{{ __('Begin Earning') }} <i class="fa-solid fa-arrow-right"></i>
						</a>
					</article>
				@endforeach
			</div>
		@endif
	</div>
</section>
