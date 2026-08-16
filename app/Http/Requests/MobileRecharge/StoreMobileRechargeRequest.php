<?php

namespace App\Http\Requests\MobileRecharge;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMobileRechargeRequest extends FormRequest
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
            'wallet_id'    => ['required', 'integer', 'exists:wallets,id'],
            'phone_number' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9\s\-\(\)]{8,32}$/'],
            'operator'     => ['nullable', 'string', 'max:64'],
            'country'      => ['nullable', 'string', 'size:2'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'wallet_id.required'    => __('Please select a wallet.'),
            'wallet_id.exists'      => __('The selected wallet is invalid.'),
            'phone_number.required' => __('Please enter the mobile number to recharge.'),
            'phone_number.regex'    => __('Please enter a valid mobile number.'),
            'amount.required'       => __('Please enter a recharge amount.'),
            'amount.min'            => __('The amount must be at least :min.'),
        ];
    }
}
