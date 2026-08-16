<?php

namespace App\Http\Requests\Backend\MobileRecharge;

use App\Enums\FixPctType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMobileRechargeProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $drivers = array_keys((array) config('mobile_services.recharge.providers', []));

        return [
            'code'                 => ['required', 'string', 'max:64', 'alpha_dash', 'unique:mobile_recharge_providers,code', 'unique:plugins,code'],
            'name'                 => ['required', 'string', 'max:255'],
            'driver'               => ['required', 'string', Rule::in($drivers)],
            'description'          => ['nullable', 'string', 'max:500'],
            'logo'                 => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp,ico', 'max:2048'],
            'status'               => ['required', 'boolean'],
            'is_default'           => ['required', 'boolean'],
            'supported_countries'  => ['nullable', 'string', 'max:1000', 'regex:/^[A-Za-z, ]*$/'],
            'supported_currencies' => ['nullable', 'string', 'max:1000', 'regex:/^[A-Za-z, ]*$/'],
            'fee_amount'           => $this->feeAmountRules(),
            'fee_type'             => ['nullable', Rule::in(array_column(FixPctType::cases(), 'value'))],
            'fee_fixed'            => ['nullable', 'numeric', 'min:0'],
            'fee_percent'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_amount'           => ['required', 'numeric', 'min:0'],
            'max_amount'           => ['nullable', 'numeric', 'gte:min_amount'],
            'config'               => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => __('A plugin or provider with this code already exists.'),
            'driver.in'   => __('The selected driver is not registered.'),
        ];
    }

    /**
     * @return array<int, ValidationRule|string>
     */
    private function feeAmountRules(): array
    {
        $rules = ['nullable', 'numeric', 'min:0'];

        if ($this->input('fee_type') === FixPctType::PERCENT->value) {
            $rules[] = 'max:100';
        }

        return $rules;
    }
}
