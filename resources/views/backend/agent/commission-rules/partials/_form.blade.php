@php
    $selectedOperation = old('operation_type', $rule?->operation_type ?? 'all');
    $selectedCalculation = old('calculation_type', $rule?->calculation_type?->value ?? \App\Enums\AgentCommissionRuleType::PERCENTAGE->value);
    $ruleFormId = $rule?->id ?? 'new';
    $statusChecked = (bool) old('status', $rule?->status ?? true);
    $globalFallbackChecked = (bool) old('applies_globally', $rule?->applies_globally ?? false);
@endphp

<div class="col-12">
    <div class="agent-rule-form">
        <section class="agent-rule-form-section">
            <div class="agent-rule-form-section__head">
                <span class="agent-rule-form-section__icon" aria-hidden="true">
                    <i class="fa-solid fa-sliders"></i>
                </span>
                <div>
                    <h6>{{ __('Rule Identity') }}</h6>
                    <p>{{ __('Name the rule and define when it should be matched.') }}</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <label class="form-label">{{ __('Rule Name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $rule?->name) }}" placeholder="{{ __('Example: Cash-Out Urban Agent Rate') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">{{ __('Operation') }}</label>
                    <select name="operation_type" class="form-select @error('operation_type') is-invalid @enderror">
                        @foreach($operationTypes as $value => $label)
                            <option value="{{ $value }}" @selected($selectedOperation === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('operation_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Currency') }}</label>
                    <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                        <option value="">{{ __('Any Currency') }}</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" @selected((string) old('currency_id', $rule?->currency_id) === (string) $currency->id)>
                                {{ $currency->code }}
                            </option>
                        @endforeach
                    </select>
                    @error('currency_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="agent-rule-form-section">
            <div class="agent-rule-form-section__head">
                <span class="agent-rule-form-section__icon" aria-hidden="true">
                    <i class="fa-solid fa-percent"></i>
                </span>
                <div>
                    <h6>{{ __('Commission Logic') }}</h6>
                    <p>{{ __('Set amount ranges, calculation method, and optional commission caps.') }}</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">{{ __('Calculation') }}</label>
                    <select name="calculation_type" class="form-select @error('calculation_type') is-invalid @enderror">
                        @foreach($calculationTypes as $value => $label)
                            <option value="{{ $value }}" @selected($selectedCalculation === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('calculation_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">{{ __('Min Amount') }}</label>
                    <input type="text" name="min_amount" oninput="this.value = validateDouble(this.value)" class="form-control @error('min_amount') is-invalid @enderror" value="{{ old('min_amount', $rule?->min_amount ?? 0) }}" inputmode="decimal">
                    @error('min_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">{{ __('Max Amount') }}</label>
                    <input type="text" name="max_amount" oninput="this.value = validateDouble(this.value)" class="form-control @error('max_amount') is-invalid @enderror" value="{{ old('max_amount', $rule?->max_amount) }}" placeholder="{{ __('No max') }}" inputmode="decimal">
                    @error('max_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">{{ __('Percentage Rate') }}</label>
                    <div class="input-group">
                        <input type="text" name="percentage_rate" oninput="this.value = validateDouble(this.value)" class="form-control @error('percentage_rate') is-invalid @enderror" value="{{ old('percentage_rate', $rule?->percentage_rate ?? 0) }}" inputmode="decimal">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('percentage_rate')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">{{ __('Fixed Amount') }}</label>
                    <input type="text" name="fixed_amount" oninput="this.value = validateDouble(this.value)" class="form-control @error('fixed_amount') is-invalid @enderror" value="{{ old('fixed_amount', $rule?->fixed_amount ?? 0) }}" inputmode="decimal">
                    @error('fixed_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">{{ __('Min Commission') }}</label>
                    <input type="text" name="min_commission" oninput="this.value = validateDouble(this.value)" class="form-control @error('min_commission') is-invalid @enderror" value="{{ old('min_commission', $rule?->min_commission) }}" placeholder="{{ __('Optional') }}" inputmode="decimal">
                    @error('min_commission')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">{{ __('Max Commission') }}</label>
                    <input type="text" name="max_commission" oninput="this.value = validateDouble(this.value)" class="form-control @error('max_commission') is-invalid @enderror" value="{{ old('max_commission', $rule?->max_commission) }}" placeholder="{{ __('Optional') }}" inputmode="decimal">
                    @error('max_commission')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="agent-rule-form-section">
            <div class="agent-rule-form-section__head">
                <span class="agent-rule-form-section__icon" aria-hidden="true">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
                <div>
                    <h6>{{ __('Availability') }}</h6>
                    <p>{{ __('Control schedule, priority, and whether this rule is active or globally usable.') }}</p>
                </div>
            </div>
            <div class="row g-3 align-items-stretch">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">{{ __('Effective From') }}</label>
                    <input type="datetime-local" name="effective_from" class="form-control @error('effective_from') is-invalid @enderror" value="{{ old('effective_from', $rule?->effective_from?->format('Y-m-d\TH:i')) }}">
                    @error('effective_from')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">{{ __('Effective Until') }}</label>
                    <input type="datetime-local" name="effective_until" class="form-control @error('effective_until') is-invalid @enderror" value="{{ old('effective_until', $rule?->effective_until?->format('Y-m-d\TH:i')) }}">
                    @error('effective_until')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">{{ __('Priority') }}</label>
                    <input type="number" name="priority" min="1" max="999" class="form-control @error('priority') is-invalid @enderror" value="{{ old('priority', $rule?->priority ?? 100) }}">
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <input type="hidden" name="status" value="0">
                    <input type="hidden" name="applies_globally" value="0">
                    <div class="agent-rule-switch-grid">
                        <label class="admin-switch-card" for="rule_status_{{ $ruleFormId }}">
                            <input class="form-check-input coevs-switch admin-switch-card__input" type="checkbox" role="switch" id="rule_status_{{ $ruleFormId }}" name="status" value="1" @checked($statusChecked)>
                            <span class="admin-switch-card__track" aria-hidden="true"></span>
                            <span class="admin-switch-card__copy">
                                <strong>{{ __('Active') }}</strong>
                                <small>{{ __('Allow this rule to be matched by agent operations.') }}</small>
                            </span>
                            <span class="admin-switch-card__meta">
                                <span class="admin-switch-card__state admin-switch-card__state--enabled">{{ __('Enabled') }}</span>
                                <span class="admin-switch-card__state admin-switch-card__state--disabled">{{ __('Disabled') }}</span>
                            </span>
                        </label>

                        <label class="admin-switch-card" for="rule_global_{{ $ruleFormId }}">
                            <input class="form-check-input coevs-switch admin-switch-card__input" type="checkbox" role="switch" id="rule_global_{{ $ruleFormId }}" name="applies_globally" value="1" @checked($globalFallbackChecked)>
                            <span class="admin-switch-card__track" aria-hidden="true"></span>
                            <span class="admin-switch-card__copy">
                                <strong>{{ __('Global Fallback') }}</strong>
                                <small>{{ __('Use this rule even when an agent has no direct assignment.') }}</small>
                            </span>
                            <span class="admin-switch-card__meta">
                                <span class="admin-switch-card__state admin-switch-card__state--enabled">{{ __('On') }}</span>
                                <span class="admin-switch-card__state admin-switch-card__state--disabled">{{ __('Off') }}</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
