<div class="modal fade p2p-ui-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ $formAction }}" id="{{ $formId }}" enctype="multipart/form-data">
                @csrf
                @if($httpMethod)
                    @method($httpMethod)
                @endif
                <input type="hidden" name="form_type" value="{{ $formType }}">
                @if($accountIdInputId)
                    <input type="hidden" name="account_id" id="{{ $accountIdInputId }}" value="{{ $accountIdValue }}">
                @endif
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">@lang('Payment Method')</label>
                            <select name="payment_method_id" id="{{ $methodSelectId }}" class="form-select" required>
                                <option value="">@lang('Select Payment Method')</option>
                                @foreach($methodOptions as $methodOption)
                                    <option value="{{ $methodOption['id'] }}" @selected((string) $methodOption['id'] === (string) ($selectedPaymentMethodId ?? ''))>
                                        {{ $methodOption['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">@lang('Account Label')</label>
                            <input
                                type="text"
                                name="label"
                                id="{{ $labelInputId }}"
                                class="form-control"
                                value="{{ $labelValue ?? '' }}"
                                placeholder="@lang('Example: Personal account')"
                            >
                        </div>
                        <div class="col-12">
                            <div id="{{ $infoId }}" class="alert alert-light border d-none mb-0"></div>
                        </div>
                        <div class="col-12">
                            <div id="{{ $fieldsWrapId }}" class="row g-3"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-base btn-sm">
                        <i class="fas fa-save me-1"></i> {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
