<div class="modal fade" id="run-task-modal" tabindex="-1" aria-labelledby="runTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="runTaskModalLabel">
                    <x-icon name="apps-1" height="18" width="18" class="text-primary"/>
                    @lang('Run Background Task')
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <form method="POST" action="{{ route('admin.background-tasks.run') }}" id="run-task-form">
                @csrf
                <input type="hidden" name="task_key" id="modal-task-key">
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="fw-bold mb-1" id="modal-task-label"></div>
                        <div class="text-muted small" id="modal-task-desc"></div>
                    </div>
                    <div class="rounded p-2 mb-3" style="background:#f1f5f9;">
                        <span class="small text-muted me-1">@lang('Signature:')</span>
                        <code class="small" id="modal-task-sig"></code>
                    </div>

                    {{-- Limit option (wallet_earn_process only) --}}
                    <div id="field-limit" class="d-none mb-3">
                        <label class="form-label fw-semibold">@lang('Limit')</label>
                        <input type="number" name="limit" id="modal-limit-input"
                               class="form-control" value="100" min="1" max="1000">
                        <div class="form-text">@lang('Maximum number of stakes to process in this run (1-1000).')</div>
                    </div>

                    <div id="field-renewals" class="d-none mb-3">
                        <input type="hidden" name="renewals" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="renewals" value="1"
                                   id="modal-renewals-input" checked>
                            <label class="form-check-label fw-semibold" for="modal-renewals-input">
                                @lang('Process auto-renewals')
                            </label>
                        </div>
                        <div class="form-text">@lang('Attempt due wallet auto-renewals before expiry and grace checks.')</div>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 py-2 small">
                        <x-icon name="warning" height="14" width="14" class="flex-shrink-0"/>
                        @lang('This will execute the command immediately on the server.')
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-coreui-dismiss="modal">
                        @lang('Cancel')
                    </button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="run-task-submit">
                        <x-icon name="apps" height="16" width="16"/>
                        @lang('Run Now')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
