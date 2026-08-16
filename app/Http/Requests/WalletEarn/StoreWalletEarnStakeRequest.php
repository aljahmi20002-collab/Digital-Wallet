<?php

namespace App\Http\Requests\WalletEarn;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalletEarnStakeRequest extends FormRequest
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
        $userId = $this->user()?->id;

        return [
            'plan_id'   => ['required', Rule::exists('wallet_earn_plans', 'id')->where(fn ($query) => $query->where('status', true))],
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('user_id', $userId)->where('status', true))],
            'amount'    => ['required', 'numeric', 'min:0.00000001'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required'   => __('Please choose an earning plan.'),
            'plan_id.exists'     => __('Please choose an available earning plan.'),
            'wallet_id.required' => __('Please choose the wallet you want to stake from.'),
            'wallet_id.exists'   => __('Please choose one of your active wallets.'),
            'amount.min'         => __('Stake amount must be greater than zero.'),
        ];
    }
}
