@extends('frontend.layouts.user.index')

@section('title', __('Wallet Earn Plans'))

@push('styles')
	<link rel="stylesheet" href="{{ asset('frontend/css/wallet-earn.css?v=' . config('app.version')) }}">
@endpush

@section('content')
	<div class="user-dashboard wallet-earn-page wallet-earn-plans-v2">
		<div class="row">
			<div class="col-12">
				<div class="card single-form-card">
					<x-user-feature-header
						:title="__('Wallet Earn Plans')"
						:subtitle="__('Choose a supported earning plan and stake from an eligible wallet.')"
						icon="fas fa-chart-line"
					>
						<a href="{{ route('user.wallet-earn.stakes') }}" class="btn btn-light-success btn-sm">
							<i class="fas fa-layer-group"></i> {{ __('My Stakes') }}
						</a>
					</x-user-feature-header>
					
					<div class="card-body">
						@if($plans->isEmpty())
							<x-user-not-found
								class="mt-3"
								:title="__('No earning plans available')"
								:message="__('Please check back after an admin publishes Wallet Earn plans for your wallets.')"
								icon="fa-chart-line"
							/>
						@else
							<div class="we-plans-grid-v2 mt-2">
								@foreach($plans as $i => $plan)
									@php
										$dec = (int) setting('site_decimal', 2);
										$eligibleWallets = $wallets->filter(fn ($w) => $plan->supportsCurrency((int) $w->currency_id));
										$isFeatured = $plan->is_featured;
										$badgeLabel = $plan->planBadgeLabel();
										$currencyLabel = $plan->currency_id ? $plan->currency->code : __('All Wallets');
										$rateDisplay = $plan->profit_type === \App\Enums\WalletEarnProfitType::Percentage
											? number_format((float) $plan->profit_rate, 2) . '%'
											: number_format((float) $plan->profit_rate, $dec) . ($plan->currency_id ? ' ' . $plan->currency->code : '');

										$durationHours = match($plan->duration_unit) {
											'hours'  => $plan->duration_value,
											'months' => $plan->duration_value * 24 * 30,
											default  => $plan->duration_value * 24,
										};

										$planData = [
											'id'             => $plan->id,
											'name'           => $plan->name,
											'rate'           => (float) $plan->profit_rate,
											'rate_display'   => $rateDisplay,
											'profit_type'    => $plan->profit_type->value,
											'rate_label'     => $plan->profit_type->label(),
											'min'            => (float) $plan->minimum_amount,
											'max'            => $plan->maximum_amount ? (float) $plan->maximum_amount : null,
											'currency_id'    => $plan->currency_id,
											'currency_code'  => $plan->currency_id ? $plan->currency->code : null,
											'duration_value' => $plan->duration_value,
											'duration_unit'  => $plan->duration_unit,
											'duration_label' => $plan->durationLabel(),
											'payout'         => $plan->payout_frequency->label(),
											'payout_value'   => $plan->payout_frequency->value,
											'auto_approve'   => $plan->auto_approve,
										];
									@endphp
									<article
										class="we-plan-card-v2 {{ $isFeatured ? 'we-plan-card-v2--featured' : '' }}"
										style="animation: we-fadeUp .45s {{ $i * 70 }}ms ease both"
									>
										{{-- Badge --}}
										@if($badgeLabel)
											<div class="we-plan-card-v2__badge">
                                                <span class="we-plan-badge {{ $isFeatured ? 'we-plan-badge--amber' : 'we-plan-badge--blue' }}">
                                                    {{ $badgeLabel }}
                                                </span>
											</div>
										@endif
										
										{{-- Top padding area --}}
										<div class="we-plan-card-v2__top" style="{{ $badgeLabel ? 'padding-top: 22px;' : '' }}">
											{{-- Header: icon + name + tagline --}}
											<div class="we-plan-card-v2__header">
												<div class="we-plan-card-v2__icon">
													@if($plan->icon)
														<img src="{{ asset($plan->icon) }}" alt="{{ $plan->name }}" style="width:22px;height:22px;object-fit:contain;" loading="lazy">
													@else
														<x-icon name="trending-up" height="18" width="18"/>
													@endif
												</div>
												<div class="we-plan-card-v2__title-wrap">
													<div class="we-plan-card-v2__name">{{ $plan->name }}</div>
													<div class="we-plan-card-v2__tagline">
														{{ $plan->description ?: __('Earn scheduled rewards while your principal stays locked.') }}
													</div>
												</div>
											</div>
											
											{{-- Reward Rate --}}
											<div class="we-plan-card-v2__rate">
												<span class="we-plan-card-v2__rate-value">{{ number_format((float) $plan->profit_rate, 2) }}</span>
												@if($plan->profit_type === \App\Enums\WalletEarnProfitType::Percentage)
													<span class="we-plan-card-v2__rate-pct">%</span>
												@endif
												<span class="we-plan-card-v2__rate-label">{{ $plan->profit_type->label() }}</span>
											</div>
											
											{{-- Pills --}}
											<div class="we-plan-card-v2__pills">
												<span class="we-plan-card-v2__pill">{{ $currencyLabel }}</span>
												<span class="we-plan-card-v2__pill we-plan-card-v2__pill--muted">{{ $plan->durationLabel() }}</span>
												<span class="we-plan-card-v2__pill we-plan-card-v2__pill--muted">{{ $plan->payout_frequency->label() }}</span>
											</div>
										</div>
										
										{{-- Divider --}}
										<div class="we-plan-card-v2__divider"></div>
										
										{{-- Body: details + bullets --}}
										<div class="we-plan-card-v2__body">
											<div class="we-plan-card-v2__details">
												<div class="we-plan-card-v2__detail">
													<i class="fas fa-lock"></i>
													<span class="we-plan-card-v2__detail-val">{{ $plan->amountRangeLabel() }}</span>
													@if($plan->currency_id)
														<span class="we-plan-card-v2__detail-sub">{{ $plan->currency->code }}</span>
													@endif
												</div>
												<div class="we-plan-card-v2__detail">
													<i class="fas fa-sync"></i>
													<span class="we-plan-card-v2__detail-val">{{ $plan->payout_frequency->label() }}</span>
													<span class="we-plan-card-v2__detail-sub">{{ __('payout') }}</span>
												</div>
												<div class="we-plan-card-v2__detail">
													<i class="fas fa-shield-alt"></i>
													<span class="we-plan-card-v2__detail-val">{{ $plan->return_principal ? __('Returned') : __('Not returned') }}</span>
													<span class="we-plan-card-v2__detail-sub">{{ __('principal') }}</span>
												</div>
												<div class="we-plan-card-v2__detail">
													<i class="fas fa-bolt"></i>
													<span class="we-plan-card-v2__detail-val">{{ $plan->auto_approve ? __('Auto') : __('Manual') }}</span>
													<span class="we-plan-card-v2__detail-sub">{{ __('approval') }}</span>
												</div>
											</div>
											
											<div class="we-plan-card-v2__bullets">
												<div class="we-plan-card-v2__bullet">
													<span class="we-plan-card-v2__bullet-check"><i class="fas fa-check"></i></span>
													{{ $plan->currency_id ? $plan->currency->code . ' ' . __('only') : __('All currencies') }}
												</div>
												<div class="we-plan-card-v2__bullet">
													<span class="we-plan-card-v2__bullet-check"><i class="fas fa-check"></i></span>
													{{ $plan->auto_approve ? __('Auto-approved') : __('Manual review') }}
												</div>
												<div class="we-plan-card-v2__bullet">
													<span class="we-plan-card-v2__bullet-check"><i class="fas fa-check"></i></span>
													{{ $plan->return_principal ? __('Principal returned') : __('Principal locked') }}
												</div>
											</div>
										</div>
										
										{{-- CTA --}}
										<div class="we-plan-card-v2__cta">
											@if($eligibleWallets->isEmpty())
												<button type="button" class="btn we-plan-card-v2__btn" disabled>
													<i class="fas fa-wallet"></i> {{ __('No Eligible Wallet') }}
												</button>
											@else
												<button
													type="button"
													class="btn we-plan-card-v2__btn"
													data-bs-toggle="modal"
													data-bs-target="#wePlanStakeModal"
													data-plan="{{ json_encode($planData) }}"
												>
													{{ __('Stake Now') }} <i class="fas fa-arrow-right"></i>
												</button>
											@endif
										</div>
									</article>
								@endforeach
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
	
	{{-- Stake Modal --}}
	<div class="modal fade we-stake-modal" id="wePlanStakeModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div>
						<h5 class="modal-title" id="weModalTitle">{{ __('Stake') }}</h5>
						<div class="we-modal-subtitle" id="weModalSubtitle"></div>
					</div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				
				{{-- Step 1: Form --}}
				<div id="weStepForm" class="we-stake-step active">
					<div class="modal-body">
						<div class="mb-3">
							<label class="we-field-label">
								<i class="fas fa-wallet"></i> {{ __('Wallet') }}
							</label>
							<select id="weWalletSelect" class="form-select">
								<option value="">{{ __('Choose a wallet…') }}</option>
							</select>
							<div class="we-field-balance mt-1 d-none" id="weWalletBalance"></div>
							<div class="we-field-error d-none" id="weWalletError"></div>
						</div>
						<div class="mb-3">
							<div class="d-flex justify-content-between align-items-center mb-1">
								<label class="we-field-label mb-0">{{ __('Amount') }}</label>
								<button type="button" class="we-use-max-btn d-none" id="weUseMaxBtn">
									{{ __('Use Max') }}
								</button>
							</div>
							<div class="position-relative">
								<input type="number" id="weAmountInput" class="form-control we-amount-input"
								       placeholder="{{ __('Enter amount') }}" step="any" min="0">
								<span class="we-amount-currency" id="weAmountCurrency"></span>
							</div>
							<div class="we-field-hint mt-1" id="weAmountHint"></div>
							<div class="we-field-error d-none" id="weAmountError"></div>
						</div>
						<div class="we-stake-estimate d-none" id="weEstimate">
							<div>
								<div class="we-stake-estimate__label" id="weEstimateLabel"></div>
								<div class="we-stake-estimate__profit" id="weEstimateProfit"></div>
							</div>
							<div class="text-end">
								<div class="we-stake-estimate__label">{{ __('Total returned') }}</div>
								<div class="fw-bold" id="weEstimateTotal" style="font-size:14px;color:#1e293b"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer border-0 pt-0">
						<button type="button" class="btn btn-base w-100 we-continue-btn" id="weContinueBtn">
							{{ __('Continue') }} <i class="fas fa-arrow-right ms-1"></i>
						</button>
					</div>
				</div>
				
				{{-- Step 2: Confirm --}}
				<div id="weStepConfirm" class="we-stake-step">
					<form id="weStakeForm" method="POST" action="{{ route('user.wallet-earn.store') }}" onsubmit="disableSubmitButton(this, '{{ __('Processing...') }}')">
						@csrf
						<input type="hidden" name="plan_id" id="weFormPlanId">
						<input type="hidden" name="wallet_id" id="weFormWalletId">
						<input type="hidden" name="amount" id="weFormAmount">
						<div class="modal-body">
							<div class="we-stake-summary">
								<div class="we-stake-summary__grid" id="weSummaryGrid"></div>
								<div class="we-stake-summary__profit" id="weSummaryProfit"></div>
							</div>
							<div class="we-stake-manual-notice d-none" id="weManualNotice">
								<i class="fas fa-info-circle mt-1 flex-shrink-0" style="color:#f59e0b"></i>
								<span>{{ __('Manual approval required — up to 24 hours.') }}</span>
							</div>
						</div>
						<div class="modal-footer border-0 pt-0 d-flex gap-2">
							<button type="button" class="btn btn-outline-secondary flex-grow-1" id="weBackBtn">
								<i class="fas fa-arrow-left me-1"></i> {{ __('Back') }}
							</button>
							<button type="submit" class="btn btn-base flex-grow-1">
								<i class="fas fa-lock me-1"></i> {{ __('Confirm & Stake') }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	@php
		$walletsJson = $wallets->map(function ($w) {
			return [
				'id'          => $w->id,
				'label'       => $w->currency->code . ' — ' . number_format((float) $w->balance, (int) setting('site_decimal', 2)),
				'currency_id' => $w->currency_id,
				'currency'    => $w->currency->code,
				'balance'     => (float) $w->balance,
			];
		})->values();
	@endphp
	<script>
    'use strict';
        (function () {
            const WALLETS = @json($walletsJson);

            let activePlan = null;

            function calcTotalPayouts(dv, du, freq) {
                const dh = du === 'hours' ? dv : du === 'months' ? dv * 24 * 30 : dv * 24;
                if (freq === 'daily') {
                    return Math.max(1, Math.ceil(dh / 24));
                }
                if (freq === 'weekly') {
                    return Math.max(1, Math.ceil(dh / (24 * 7)));
                }
                if (freq === 'monthly') {
                    return Math.max(1, Math.ceil(dh / (24 * 30)));
                }
                return 1;
            }

            function calcProfit(amount, plan) {
                const n = parseFloat(amount) || 0;
                if (n <= 0) {
                    return null;
                }
                const perPayout = plan.profit_type === 'fixed' ? plan.rate : n * (plan.rate / 100);
                const payouts = calcTotalPayouts(plan.duration_value, plan.duration_unit, plan.payout_value);
                const profit = perPayout * payouts;
                return {profit: profit.toFixed(6), total: (n + profit).toFixed(6)};
            }

            function fmt(val) {
                const n = parseFloat(val);
                if (isNaN(n)) {
                    return '—';
                }
                return n % 1 === 0 ? n.toLocaleString() : parseFloat(n.toFixed(8)).toLocaleString(undefined, {maximumFractionDigits: 8});
            }

            function setVisible(el, visible) {
                if (el) {
                    el.classList.toggle('d-none', !visible);
                }
            }

            function setVisibleById(id, visible) {
                setVisible(document.getElementById(id), visible);
            }

            function setStep(step) {
                document.getElementById('weStepForm').classList.toggle('active', step === 'form');
                document.getElementById('weStepConfirm').classList.toggle('active', step === 'confirm');
            }

            function resetModal() {
                document.getElementById('weWalletSelect').value = '';
                document.getElementById('weAmountInput').value = '';
                setVisibleById('weWalletBalance', false);
                setVisibleById('weUseMaxBtn', false);
                document.getElementById('weAmountCurrency').textContent = '';
                setVisibleById('weEstimate', false);
                setVisibleById('weWalletError', false);
                setVisibleById('weAmountError', false);
                document.getElementById('weAmountHint').textContent = '';
                setStep('form');
            }

            function populateWallets(plan) {
                const sel = document.getElementById('weWalletSelect');
                sel.innerHTML = '<option value="">{{ __('Choose a wallet…') }}</option>';
                WALLETS.forEach(function (w) {
                    if (plan.currency_id !== null && w.currency_id !== plan.currency_id) {
                        return;
                    }
                    const opt = document.createElement('option');
                    opt.value = w.id;
                    opt.textContent = w.label;
                    sel.appendChild(opt);
                });
            }

            function updateEstimate() {
                if (!activePlan) {
                    return;
                }
                const amount = document.getElementById('weAmountInput').value;
                const est = calcProfit(amount, activePlan);
                const cur = document.getElementById('weAmountCurrency').textContent;
                const el = document.getElementById('weEstimate');
                if (est && parseFloat(amount) > 0) {
                    document.getElementById('weEstimateLabel').textContent = '{{ __('Profit after') }} ' + activePlan.duration_label;
                    document.getElementById('weEstimateProfit').textContent = '+' + fmt(est.profit) + ' ' + cur;
                    document.getElementById('weEstimateTotal').textContent = fmt(est.total) + ' ' + cur;
                    setVisible(el, true);
                } else {
                    setVisible(el, false);
                }
            }

            document.getElementById('wePlanStakeModal').addEventListener('show.bs.modal', function (e) {
                const btn = e.relatedTarget;
                if (!btn) {
                    return;
                }
                activePlan = JSON.parse(btn.getAttribute('data-plan'));
                resetModal();

                document.getElementById('weModalTitle').textContent = '{{ __('Stake') }} — ' + activePlan.name;
                document.getElementById('weModalSubtitle').textContent = activePlan.rate_display + ' ' + activePlan.rate_label + ' · ' + activePlan.duration_label;

                populateWallets(activePlan);

                const hint = document.getElementById('weAmountHint');
                hint.textContent = '{{ __('Min') }} ' + activePlan.min + (activePlan.max ? ' · {{ __('Max') }} ' + activePlan.max : '') + (activePlan.currency_code ? ' ' + activePlan.currency_code : '');
            });

            document.getElementById('weWalletSelect').addEventListener('change', function () {
                const wid = parseInt(this.value);
                const wall = WALLETS.find(function (w) {
                    return w.id === wid;
                });
                const balEl = document.getElementById('weWalletBalance');
                const curEl = document.getElementById('weAmountCurrency');
                const maxBtn = document.getElementById('weUseMaxBtn');

                if (wall) {
                    balEl.innerHTML = '<i class="fas fa-check text-success me-1"></i>{{ __('Balance') }}: <strong>' + fmt(wall.balance) + ' ' + wall.currency + '</strong>';
                    setVisible(balEl, true);
                    curEl.textContent = wall.currency;
                    setVisible(maxBtn, true);
                } else {
                    setVisible(balEl, false);
                    curEl.textContent = '';
                    setVisible(maxBtn, false);
                }
                setVisibleById('weWalletError', false);
                updateEstimate();
            });

            document.getElementById('weAmountInput').addEventListener('input', function () {
                setVisibleById('weAmountError', false);
                updateEstimate();
            });

            document.getElementById('weUseMaxBtn').addEventListener('click', function () {
                const wid = parseInt(document.getElementById('weWalletSelect').value);
                const wall = WALLETS.find(function (w) {
                    return w.id === wid;
                });
                if (wall) {
                    document.getElementById('weAmountInput').value = wall.balance;
                    updateEstimate();
                }
            });

            document.getElementById('weContinueBtn').addEventListener('click', function () {
                const wid = parseInt(document.getElementById('weWalletSelect').value) || 0;
                const amount = parseFloat(document.getElementById('weAmountInput').value) || 0;
                const wall = WALLETS.find(function (w) {
                    return w.id === wid;
                });
                let valid = true;

                setVisibleById('weWalletError', false);
                setVisibleById('weAmountError', false);

                if (!wid) {
                    document.getElementById('weWalletError').textContent = '{{ __('Select a wallet.') }}';
                    setVisibleById('weWalletError', true);
                    valid = false;
                }

                if (!amount || amount <= 0) {
                    document.getElementById('weAmountError').textContent = '{{ __('Enter a valid amount.') }}';
                    setVisibleById('weAmountError', true);
                    valid = false;
                } else if (amount < activePlan.min) {
                    document.getElementById('weAmountError').textContent = '{{ __('Min is') }} ' + activePlan.min + (activePlan.currency_code ? ' ' + activePlan.currency_code : '');
                    setVisibleById('weAmountError', true);
                    valid = false;
                } else if (activePlan.max && amount > activePlan.max) {
                    document.getElementById('weAmountError').textContent = '{{ __('Max is') }} ' + activePlan.max + (activePlan.currency_code ? ' ' + activePlan.currency_code : '');
                    setVisibleById('weAmountError', true);
                    valid = false;
                } else if (wall && amount > wall.balance) {
                    document.getElementById('weAmountError').textContent = '{{ __('Insufficient balance.') }}';
                    setVisibleById('weAmountError', true);
                    valid = false;
                }

                if (!valid) {
                    return;
                }

                const cur = wall ? wall.currency : (activePlan.currency_code || '');
                const est = calcProfit(amount, activePlan);

                document.getElementById('weFormPlanId').value = activePlan.id;
                document.getElementById('weFormWalletId').value = wid;
                document.getElementById('weFormAmount').value = amount;

                const rows = [
                    ['{{ __('Plan') }}', activePlan.name],
                    ['{{ __('Rate') }}', activePlan.rate_display + ' ' + activePlan.rate_label],
                    ['{{ __('Amount') }}', fmt(amount) + ' ' + cur],
                    ['{{ __('Duration') }}', activePlan.duration_label],
                    ['{{ __('Wallet') }}', wall ? wall.label : '—'],
                    ['{{ __('Payout') }}', activePlan.payout],
                ];

                const grid = document.getElementById('weSummaryGrid');
                grid.innerHTML = rows.map(function (r) {
                    return '<div class="we-stake-summary__item"><span>' + r[0] + '</span><strong>' + r[1] + '</strong></div>';
                }).join('');

                const profitEl = document.getElementById('weSummaryProfit');
                if (est) {
                    profitEl.innerHTML =
                        '<span class="we-stake-summary__profit-label">{{ __('Estimated profit') }}</span>' +
                        '<span class="we-stake-summary__profit-value">+' + fmt(est.profit) + ' ' + cur + '</span>';
                    setVisible(profitEl, true);
                } else {
                    setVisible(profitEl, false);
                }

                setVisibleById('weManualNotice', !activePlan.auto_approve);

                setStep('confirm');
            });

            document.getElementById('weBackBtn').addEventListener('click', function () {
                setStep('form');
            });
        })();
	</script>
@endpush
