@php
	use App\Enums\BillingCycle;
	use App\Models\SubscriptionPlan;

	$locale = $locale ?? app()->getLocale();

	$eyebrow     = $data['eyebrow'][$locale]     ?? $data['subheading'][$locale] ?? __('Membership Tiers');
	$heading     = $data['heading'][$locale]     ?? __('Choose Your __Caliber.__');
	$description = $data['description'][$locale] ?? '';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));

	$plans = SubscriptionPlan::query()->where('status', true)->orderBy('sort_order')->with(['features','prices'])->get();
	$featured = $plans->firstWhere('is_featured', true);
	$others   = $plans->where('is_featured', false)->values();
	if ($featured && $others->count() >= 2) {
		$ordered = [$others[0], $featured, $others[1]];
	} elseif ($featured && $others->count() === 1) {
		$ordered = [$others[0], $featured];
	} else {
		$ordered = $plans->all();
	}

	$allPrices = $plans->flatMap->prices;
	$maxHalfYearlyDiscount = $allPrices->where('billing_cycle', BillingCycle::HalfYearly)->max('discount');
	$maxYearlyDiscount     = $allPrices->where('billing_cycle', BillingCycle::Yearly)->max('discount');

	$currencyCode = siteCurrency('code');
@endphp

<section class="gdk-section gdk-section--elev" id="plans">
	<div class="gdk-container">
		<div class="gdk-sub__head gdk-reveal">
			<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			<h2 class="gdk-h-section">{!! $renderedHeading !!}</h2>
			@if($description)
				<p class="gdk-lead gdk-lead--centered">{!! nl2br(e($description)) !!}</p>
			@endif

			<div class="gdk-sub__toggle-wrap">
				<div class="gdk-segmented" role="tablist" id="planToggle">
					<span class="gdk-segmented__pill" id="planPill"></span>
					<button class="gdk-segmented__btn is-active" data-mult="1" data-disc="">{{ __('Monthly') }}</button>
					<button class="gdk-segmented__btn" data-mult="0.9" data-disc="-{{ $maxHalfYearlyDiscount ?: 10 }}%">
						<span>{{ __('6 Months') }}</span>
						<small>-{{ $maxHalfYearlyDiscount ?: 10 }}%</small>
					</button>
					<button class="gdk-segmented__btn" data-mult="0.8" data-disc="-{{ $maxYearlyDiscount ?: 20 }}%">
						<span>{{ __('Annual') }}</span>
						<small>-{{ $maxYearlyDiscount ?: 20 }}%</small>
					</button>
				</div>
			</div>
		</div>

		<div class="gdk-sub__grid">
			@foreach($ordered as $plan)
				@php
					$isFeatured = $plan->is_featured;
					$prices = $plan->prices->keyBy(fn($p) => $p->billing_cycle instanceof BillingCycle ? $p->billing_cycle->value : (string) $p->billing_cycle);
					$monthlyAmt = (float) (($prices['monthly'] ?? null)?->price ?? 0);

					$planIcon = match(true) {
						$monthlyAmt <= 0 => 'fa-solid fa-seedling',
						$isFeatured      => 'fa-solid fa-crown',
						default          => 'fa-solid fa-gem',
					};

					if ($monthlyAmt <= 0) {
						$badgeText = $plan->plan_badge ?: __('Free Forever');
					} elseif ($isFeatured) {
						$badgeText = $plan->plan_badge ?: __('Most Popular');
					} else {
						$badgeText = $plan->plan_badge;
					}
				@endphp
				<article class="gdk-tier {{ $isFeatured ? 'gdk-tier--pop' : '' }} gdk-reveal">
					@if($isFeatured && $badgeText)
						<span class="gdk-tier__badge"><i class="fa-solid fa-crown"></i>{{ $badgeText }}</span>
					@endif

					<div class="gdk-tier__head">
						<span class="gdk-iconring {{ $isFeatured ? 'gdk-iconring--filled' : '' }}"><i class="{{ $planIcon }}"></i></span>
						<div>
							<h3 class="gdk-tier__name">{{ $plan->name }}</h3>
						</div>
					</div>
					<p class="gdk-tier__tag">{{ $plan->description ?: __('Access to core platform features.') }}</p>

					<div class="gdk-tier__price">
						<span class="gdk-tier__cur">{{ $currencyCode }}</span>
						<span class="gdk-tier__amt" data-base="{{ number_format($monthlyAmt, 0, '.', '') }}">{{ number_format($monthlyAmt, 0, '.', '') }}</span>
						<span class="gdk-tier__per">/{{ __('mo') }}</span>
					</div>
					<div class="gdk-tier__billed"><i class="fa-solid fa-circle-info"></i> {{ __('Billed monthly') }}</div>

					<a href="{{ route('user.subscription.plans') }}" class="gdk-btn {{ $isFeatured ? 'gdk-btn--filled' : '' }} gdk-tier__cta">
						{{ $plan->trial_days > 0 ? __('Start :d-Day Free Trial', ['d' => $plan->trial_days]) : __('Begin Application') }}
					</a>

					<div class="gdk-tier__div"></div>
					<div class="gdk-tier__inc-lbl">{{ __("What's Included") }}</div>
					<ul class="gdk-tier__list">
						@foreach($plan->features as $feature)
							<li>
								<i class="fa-solid fa-check"></i>
								<span>{{ $feature->feature_label }}</span>
								@if(!$feature->isToggle())
									<span class="pillets">{{ $feature->isUnlimited() ? __('Unlimited') : $feature->feature_value }}</span>
								@endif
							</li>
						@endforeach
					</ul>
				</article>
			@endforeach
		</div>
	</div>
</section>
