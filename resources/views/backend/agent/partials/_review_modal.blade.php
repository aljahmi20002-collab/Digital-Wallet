@php
    $statusAlerts = [
        \App\Enums\AgentStatus::APPROVED->value => [
            'class' => 'success',
            'icon' => 'fa-circle-check',
            'text' => __('This agent is approved and can process cash operations.'),
        ],
        \App\Enums\AgentStatus::DISABLED->value => [
            'class' => 'warning',
            'icon' => 'fa-triangle-exclamation',
            'text' => __('This agent is disabled. Enable it before allowing new operations.'),
        ],
        \App\Enums\AgentStatus::PENDING->value => [
            'class' => 'info',
            'icon' => 'fa-hourglass-half',
            'text' => __('Review the profile, select commission rules, then approve when ready.'),
        ],
        \App\Enums\AgentStatus::REJECTED->value => [
            'class' => 'danger',
            'icon' => 'fa-circle-xmark',
            'text' => __('This agent request has been rejected.'),
        ],
    ];

    $alert = $statusAlerts[$agent->status->value] ?? $statusAlerts[\App\Enums\AgentStatus::PENDING->value];
    $assignedRules = $agent->commissionRuleAssignments->keyBy('agent_commission_rule_id');
    $availableRules = $commissionRules ?? collect();
    $operationOptions = ['all' => __('All Operations')] + \App\Enums\AgentOperationType::options();
    $description = trim((string) ($agent->description ?? ''));
    $supportedCurrencyCodes = $agent->supportedCurrencies->pluck('code')->implode(', ') ?: $agent->currency?->code;
@endphp

<div class="modal fade" id="agent-review-{{ $agent->id }}" tabindex="-1" aria-labelledby="agent-review-label-{{ $agent->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl agent-review-dialog">
        <div class="modal-content agent-review-modal">
            <div class="modal-header agent-review-header">
                <div class="agent-review-identity min-w-0">
                    <img src="{{ asset($agent->logo) }}" alt="{{ $agent->agent_name }}" class="agent-modal-logo" loading="lazy">
                    <div class="min-w-0">
                        <div class="agent-review-eyebrow">{{ __('Agent Review') }}</div>
                        <h5 class="modal-title text-truncate" id="agent-review-label-{{ $agent->id }}">{{ $agent->agent_name }}</h5>
                        <div class="agent-review-contact text-truncate">{{ $agent->user?->email ?? $agent->user?->phone }}</div>
                    </div>
                </div>
                <div class="agent-review-header__actions">
                    <span class="badge bg-{{ $agent->status->color() }} text-uppercase agent-review-status">{{ $agent->status->label() }}</span>
                    <button type="button" class="btn-close btn-close-white agent-review-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
            </div>

            <form action="{{ route('admin.agent.request-action') }}" method="post">
                @csrf
                <input type="hidden" name="agent_id" value="{{ $agent->id }}">

                <div class="modal-body agent-modal-body">
                    <div class="alert alert-{{ $alert['class'] }} agent-review-alert" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid {{ $alert['icon'] }} flex-shrink-0"></i>
                            <div>{{ $alert['text'] }}</div>
                        </div>
                    </div>

                    <div class="agent-review-summary mb-3">
                        <div>
                            <span>{{ __('Code') }}</span>
                            <strong>{{ $agent->agent_code }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Applicant') }}</span>
                            <a href="{{ route('admin.user.manage', $agent->user->username) }}" target="_blank" rel="noopener">{{ $agent->user->name }}</a>
                        </div>
                        <div>
                            <span>{{ __('Currencies') }}</span>
                            <strong>{{ $supportedCurrencyCodes }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Phone') }}</span>
                            <strong>{{ $agent->user?->phone ?: __('Not provided') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Country') }}</span>
                            <strong>{{ $agent->user?->country ?: __('Not provided') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Requested') }}</span>
                            <strong>{{ $agent->created_at?->format('Y-m-d H:i') }}</strong>
                        </div>
                    </div>

                    @if($description !== '')
                        <div class="agent-review-section mb-3">
                            <div class="agent-review-section__title">
                                <span><i class="fa-solid fa-align-left"></i></span>
                                <strong>{{ __('Agent Note') }}</strong>
                            </div>
                            <div class="text-muted">{!! nl2br(e($description)) !!}</div>
                        </div>
                    @endif

                    @if($agent->status !== \App\Enums\AgentStatus::REJECTED)
                        <div class="agent-review-section mb-3">
                            <div class="agent-review-section__head">
                                <div class="agent-review-section__title">
                                    <span><i class="fa-solid fa-percent"></i></span>
                                    <div>
                                        <strong>{{ __('Commission Setup') }}</strong>
                                        <small>{{ __('Select reusable commission rules before approving this agent.') }}</small>
                                    </div>
                                </div>
                                <div class="agent-fallback-rate">
                                    <div class="agent-fallback-rate__copy">
                                        <label class="form-label fw-semibold mb-0" for="agent_commission_{{ $agent->id }}">{{ __('Fallback Rate') }}</label>
                                        <div class="form-text">{{ __('Used if no rule matches.') }}</div>
                                    </div>
                                    <div class="input-group agent-fallback-rate__control">
                                        <input type="text" oninput="this.value = validateDouble(this.value)"
                                               class="form-control @error('commission') is-invalid @enderror"
                                               id="agent_commission_{{ $agent->id }}"
                                               name="commission"
                                               placeholder="0.00" inputmode="decimal" autocomplete="off"
                                               value="{{ old('commission', $agent->commission) }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    @error('commission')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="agent-commission-layout">
                                <div class="agent-commission-rules">
                                    <div class="agent-commission-rules__head">
                                        <div>
                                            <label class="form-label fw-semibold mb-0">{{ __('Assigned Rules') }}</label>
                                            <div class="small text-muted">{{ __('Choose rules and operation scope before approving.') }}</div>
                                        </div>
                                        <a href="{{ route('admin.agent.commission-rules.index') }}" class="btn btn-sm btn-outline-primary agent-manage-rules">
                                            <i class="fa-solid fa-plus me-1"></i><span>{{ __('Manage Rules') }}</span>
                                        </a>
                                    </div>

                                    @if($availableRules->isNotEmpty())
                                        <div class="table-responsive agent-rule-assignment-table">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Use') }}</th>
                                                    <th>{{ __('Rule') }}</th>
                                                    <th>{{ __('Operation') }}</th>
                                                    <th>{{ __('Priority') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($availableRules as $rule)
                                                    @php
                                                        $assignment = $assignedRules->get($rule->id);
                                                        $assignmentOperation = old("commission_rules.{$rule->id}.operation_type", $assignment?->operation_type ?? $rule->operation_type);
                                                        $assignmentPriority = old("commission_rules.{$rule->id}.priority", $assignment?->priority ?? $rule->priority);
                                                        $ruleOperationLabel = $rule->operation_type === 'all'
                                                            ? __('Any operation')
                                                            : (\App\Enums\AgentOperationType::tryFrom($rule->operation_type)?->label() ?? $rule->operation_type);
                                                    @endphp
                                                    <tr>
                                                        <td class="agent-rule-use-cell">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="commission_rules[{{ $rule->id }}][enabled]"
                                                                   value="1"
                                                                   @checked((bool) old("commission_rules.{$rule->id}.enabled", $assignment !== null))>
                                                        </td>
                                                        <td class="agent-rule-name-cell">
                                                            <div class="fw-semibold">{{ $rule->name }}</div>
                                                            <div class="small text-muted">
                                                                {{ $rule->currency?->code ?? __('Any currency') }}
                                                                &middot;
                                                                {{ $ruleOperationLabel }}
                                                                &middot;
                                                                {{ $rule->applies_globally ? __('Global fallback') : __('Assignment only') }}
                                                                &middot;
                                                                @if($rule->calculation_type === \App\Enums\AgentCommissionRuleType::PERCENTAGE)
                                                                    {{ number_format((float) $rule->percentage_rate, 2) }}%
                                                                @else
                                                                    {{ number_format((float) $rule->fixed_amount, (int) setting('site_decimal', 2)) }} {{ __('fixed') }}
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="agent-rule-control-cell">
                                                            <select name="commission_rules[{{ $rule->id }}][operation_type]" class="form-select form-select-sm agent-rule-operation">
                                                                @foreach($operationOptions as $value => $label)
                                                                    <option value="{{ $value }}" @selected($assignmentOperation === $value)>{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="agent-rule-priority-cell">
                                                            <input type="number" name="commission_rules[{{ $rule->id }}][priority]" min="1" max="999" class="form-control form-control-sm agent-rule-priority" value="{{ $assignmentPriority }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <x-admin-not-found
                                            :title="__('No commission rules created')"
                                            :message="__('Create reusable rules first, then assign them while approving agents.')"
                                            icon="fa-percent"
                                            :action-url="route('admin.agent.commission-rules.index')"
                                            :action-label="__('Create Rules')"
                                            action-icon="fa-plus"
                                        />
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer agent-review-footer">
                    <div class="agent-review-actions">
                        @if($agent->status === \App\Enums\AgentStatus::PENDING)
                            <button type="submit" value="approve" name="action" class="btn btn-success text-white">
                                <i class="fa-solid fa-check me-2"></i>{{ __('Approve Agent') }}
                            </button>
                            <button type="submit" value="reject" name="action" class="btn btn-danger text-white">
                                <i class="fa-solid fa-times me-2"></i>{{ __('Reject') }}
                            </button>
                        @elseif($agent->status === \App\Enums\AgentStatus::APPROVED)
                            <button type="submit" value="approve" name="action" class="btn btn-primary">
                                <i class="fa-solid fa-rotate me-2"></i>{{ __('Update Setup') }}
                            </button>
                            <button type="submit" value="disable" name="action" class="btn btn-outline-danger">
                                <i class="fa-solid fa-ban me-2"></i>{{ __('Disable Agent') }}
                            </button>
                        @elseif($agent->status === \App\Enums\AgentStatus::DISABLED)
                            <button type="submit" value="enable" name="action" class="btn btn-success text-white">
                                <i class="fa-solid fa-power-off me-2"></i>{{ __('Enable Agent') }}
                            </button>
                            <button type="submit" value="approve" name="action" class="btn btn-outline-primary">
                                <i class="fa-solid fa-rotate me-2"></i>{{ __('Update Setup') }}
                            </button>
                        @else
                            <div class="text-muted small w-100 text-center">{{ __('This agent is rejected. No further actions available.') }}</div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
