<form method="POST" action="{{ isset($feeSetting) ? route('admin.virtual-card.fee-settings.update', $feeSetting->id) : route('admin.virtual-card.fee-settings.store') }}">
    @csrf
    @if(isset($feeSetting))
        @method('PUT')
    @endif
    <div class="row mb-3">
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label">@lang('Provider')</label>
            <select name="provider_id" class="form-select" required>
                <option value="">@lang('Select Provider')</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->id }}" {{ old('provider_id', optional($feeSetting)->provider_id) == $provider->id ? 'selected' : '' }}>{{ $provider->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label">@lang('Currency')</label>
            <select name="currency_id" class="form-select" required>
                <option value="">@lang('Select Currency')</option>
                @foreach($currencies as $currency)
                    <option value="{{ $currency->id }}" {{ old('currency_id', optional($feeSetting)->currency_id) == $currency->id ? 'selected' : '' }}>{{ $currency->code }}
                        - {{ $currency->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label">@lang('Operation')</label>
            <select name="operation" class="form-select" required>
                <option value="">@lang('Select Operation')</option>
                @foreach(\App\Enums\VirtualCard\VirtualCardFeeOperation::options() as $value => $label)
                    <option value="{{ $value }}" {{ old('operation', optional($feeSetting)->operation?->value) == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label">
                @lang('Approval Threshold')
                <span class="modal-tooltip ms-1" data-coreui-toggle="tooltip" data-coreui-placement="top"
                      title="@lang('If a transaction amount exceeds this value, additional admin approval will be required.')">
                    <x-icon name="info" height="18"/>
                </span>
            </label>
            <div class="input-group">
                <input type="number" name="approval_threshold" class="form-control" min="0" step="0.01" value="{{ old('approval_threshold', optional($feeSetting)->approval_threshold) }}">
                <span class="input-group-text">{{ siteCurrency() }}</span>
            </div>
        </div>
        
    </div>
    
    <div class="row mb-3">
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label fw-semibold">@lang('Fixed Fee')</label>
            <div class="mb-2">
                <div class="input-group">
                    <span class="input-group-text">@lang('Fixed')</span>
                    <input class="form-control" type="number" min="0" step="0.01"
                           name="fee_amount" placeholder="@lang('Enter fixed fee (e.g., 1.50)')" required value="{{ old('fee_amount', optional($feeSetting)->fee_amount) }}">
                    <span class="input-group-text">{{ siteCurrency() }}</span>
                </div>
                <small class="text-muted">@lang('A constant fee added to every transaction, charged in your base currency.')</small>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label fw-semibold">@lang('Percentage Fee')</label>
            <div>
                <div class="input-group">
                    <span class="input-group-text">@lang('Percent')</span>
                    <input class="form-control" type="number" min="0" step="0.01"
                           name="fee_percent" placeholder="@lang('Enter percentage (e.g., 2.5)')" required value="{{ old('fee_percent', optional($feeSetting)->fee_percent) }}">
                    <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">@lang('Applied to the transaction amount. Total fee = Fixed Fee + (Amount x Percentage).')</small>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label">@lang('Min Amount')</label>
            <div class="input-group">
                <input type="number" name="min_amount" class="form-control" min="0" step="0.01" value="{{ old('min_amount', optional($feeSetting)->min_amount) }}" required>
                <span class="input-group-text">{{ siteCurrency() }}</span>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-12">
            <label class="form-label">@lang('Max Amount')</label>
            <div class="input-group">
                <input type="number" name="max_amount" class="form-control" min="0" step="0.01" value="{{ old('max_amount', optional($feeSetting)->max_amount) }}">
                <span class="input-group-text">{{ siteCurrency() }}</span>
            </div>
        </div>
    </div>

    
    <div class="row mb-3">
        <div class="col-lg-4 col-md-4 col-4">
            <label class="form-label" for="status">@lang('Status')</label>
            <div class="form-check form-switch">
                <input class="form-check-input coevs-switch" type="checkbox" name="active" id="active" value="1" {{ old('active', optional($feeSetting)->active ?? 1) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="text-end mt-3">
        <button class="btn btn-primary" type="submit">
            <x-icon name="check" height="20"/> {{ isset($feeSetting) ? __('Update Fee Setting') : __('Add Fee Setting') }}
        </button>
    </div>
</form>
