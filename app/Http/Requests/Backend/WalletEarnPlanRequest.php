<?php

namespace App\Http\Requests\Backend;

use App\Enums\WalletEarnPayoutFrequency;
use App\Enums\WalletEarnProfitType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletEarnPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currency_id'      => ['nullable', Rule::exists('currencies', 'id')],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'minimum_amount'   => ['required', 'numeric', 'min:0.00000001'],
            'maximum_amount'   => ['nullable', 'numeric', 'gte:minimum_amount'],
            'profit_rate'      => ['required', 'numeric', 'min:0.00000001'],
            'profit_type'      => ['required', Rule::in(array_keys(WalletEarnProfitType::options()))],
            'duration_value'   => ['required', 'integer', 'min:1', 'max:10000'],
            'duration_unit'    => ['required', Rule::in(['hours', 'days', 'months'])],
            'payout_frequency' => ['required', Rule::in(array_keys(WalletEarnPayoutFrequency::options()))],
            'return_principal' => ['nullable', 'boolean'],
            'auto_approve'     => ['nullable', 'boolean'],
            'is_featured'      => ['nullable', 'boolean'],
            'icon'             => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,svg', 'max:2048'],
            'plan_badge'       => ['nullable', 'string', 'max:50'],
            'status'           => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'maximum_amount.gte' => __('Maximum amount must be greater than or equal to the minimum amount.'),
            'profit_rate.min'    => __('Profit value must be greater than zero.'),
            'duration_value.min' => __('Duration must be at least one unit.'),
        ];
    }
}
