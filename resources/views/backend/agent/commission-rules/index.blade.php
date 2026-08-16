@extends('backend.layouts.app')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/agent-admin.css?v=' . config('app.version')) }}">
@endpush
@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 my-3">
        <div>
            <div class="fs-3 fw-semibold">
                {{ $title }}
            </div>
            <div class="text-muted small">
                {{ __('Control cash-in and cash-out commission by operation, amount range, or currency. Assign rules from the agent review modal.') }}
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.agent.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-users me-1"></i>{{ __('Agents') }}
            </a>
            <button type="button" class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#commission-rule-create">
                <i class="fa-solid fa-plus me-1"></i>{{ __('New Rule') }}
            </button>
        </div>
    </div>

    <div class="card border-0 agent-rules-card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="mb-1">{{ __('Commission Rules') }}</h5>
                    <div class="text-muted small">{{ __('Rules are checked by specificity first, then priority.') }}</div>
                </div>
                <span class="badge bg-light text-dark border align-self-start align-self-md-center">
                    {{ trans_choice(':count rule|:count rules', $rules->total(), ['count' => $rules->total()]) }}
                </span>
            </div>

            <div class="table-responsive agent-rules-table">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                    <tr class="text-nowrap">
                        <th>{{ __('Rule') }}</th>
                        <th>{{ __('Scope') }}</th>
                        <th>{{ __('Range') }}</th>
                        <th>{{ __('Commission') }}</th>
                        <th>{{ __('Use') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td>
                                <div class="fw-bold text-break">{{ $rule->name }}</div>
                                <div class="small text-muted">{{ __('Priority') }} {{ $rule->priority }}</div>
                            </td>
                            <td>
                                <div>{{ $operationTypes[$rule->operation_type] ?? title($rule->operation_type) }}</div>
                                <div class="small text-muted">
                                    {{ $rule->currency?->code ?? __('Any currency') }}
                                </div>
                            </td>
                            <td>
                                <div>{{ number_format((float) $rule->min_amount, (int) setting('site_decimal', 2)) }}</div>
                                <div class="small text-muted">
                                    {{ $rule->max_amount ? number_format((float) $rule->max_amount, (int) setting('site_decimal', 2)) : __('No max') }}
                                </div>
                            </td>
                            <td>
                                @if($rule->calculation_type === \App\Enums\AgentCommissionRuleType::PERCENTAGE)
                                    <div class="fw-bold">{{ number_format((float) $rule->percentage_rate, 2) }}%</div>
                                @else
                                    <div class="fw-bold">{{ number_format((float) $rule->fixed_amount, (int) setting('site_decimal', 2)) }}</div>
                                @endif
                                <div class="small text-muted">{{ $rule->calculation_type->label() }}</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $rule->status ? 'success' : 'secondary' }}">
                                    {{ $rule->status ? __('Active') : __('Inactive') }}
                                </span>
                                <div class="small text-muted mt-1">
                                    {{ $rule->applies_globally ? __('Global fallback') : __('Assignment only') }}
                                </div>
                                <div class="small text-muted">
                                    {{ trans_choice(':count assigned agent|:count assigned agents', $rule->assignments_count, ['count' => $rule->assignments_count]) }}
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="agent-rule-actions">
                                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 agent-rule-action" data-coreui-toggle="modal" data-coreui-target="#commission-rule-{{ $rule->id }}">
                                        <x-icon name="manage" height="20" width="20"/>
                                        <span>{{ __('Manage') }}</span>
                                    </button>
                                    <button type="button" class="btn btn-danger text-white d-inline-flex align-items-center justify-content-center gap-2 agent-rule-action delete" data-url="{{ route('admin.agent.commission-rules.destroy', $rule) }}" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                                        <x-icon name="delete-3" height="20" width="20"/>
                                        <span>{{ __('Delete') }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-3">
                                <x-admin-not-found
                                    :title="__('No commission rules found')"
                                    :message="__('Create a reusable rule to control cash-in and cash-out commission by operation, amount range, or currency.')"
                                    icon="fa-percent"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $rules->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="commission-rule-create" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl agent-rule-modal">
            <div class="modal-content border-0 agent-rule-modal__content">
                <div class="modal-header agent-rule-modal__header">
                    <div>
                        <span class="agent-rule-modal__eyebrow">{{ __('Reusable commission template') }}</span>
                        <h5 class="modal-title">{{ __('New Commission Rule') }}</h5>
                        <div class="text-muted small">{{ __('Set the operation, scope, amount range, and commission type in one clean workflow.') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form action="{{ route('admin.agent.commission-rules.store') }}" method="POST">
                    @csrf
                    <div class="modal-body agent-rule-modal__body">
                        <div class="row g-3">
                            @include('backend.agent.commission-rules.partials._form', ['rule' => null])
                        </div>
                    </div>
                    <div class="modal-footer agent-rule-modal__footer">
                        <button type="button" class="btn btn-light" data-coreui-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-1"></i>{{ __('Create Rule') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($rules as $rule)
        <div class="modal fade" id="commission-rule-{{ $rule->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl agent-rule-modal">
                <div class="modal-content border-0 agent-rule-modal__content">
                    <div class="modal-header agent-rule-modal__header">
                        <div>
                            <span class="agent-rule-modal__eyebrow">{{ __('Rule management') }}</span>
                            <h5 class="modal-title">{{ __('Edit Commission Rule') }}</h5>
                            <div class="text-muted small">{{ $rule->name }}</div>
                        </div>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <form action="{{ route('admin.agent.commission-rules.update', $rule) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body agent-rule-modal__body">
                            <div class="row g-3">
                                @include('backend.agent.commission-rules.partials._form', ['rule' => $rule])
                            </div>
                        </div>
                        <div class="modal-footer agent-rule-modal__footer">
                            <button type="button" class="btn btn-light" data-coreui-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
