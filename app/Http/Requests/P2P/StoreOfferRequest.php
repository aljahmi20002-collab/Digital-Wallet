<?php

declare(strict_types=1);

namespace App\Http\Requests\P2P;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'wallet_id'              => ['required', 'integer', 'exists:wallets,id'],
            'side'                   => ['required', Rule::in(['BUY', 'SELL'])],
            'price'                  => ['required', 'numeric', 'min:0.00000001'],
            'min_amount'             => ['required', 'numeric', 'min:0.00000001'],
            'max_amount'             => ['nullable', 'numeric', 'gte:min_amount'],
            'payment_method_ids'     => ['nullable', 'array'],
            'payment_method_ids.*'   => ['integer', 'exists:p2p_payment_methods,id'],
            'terms'                  => ['nullable', 'string', 'max:2000'],
            'payment_window_minutes' => ['nullable', 'integer', 'min:5', 'max:2880'],
            'promote_now'            => ['nullable', 'boolean'],
            'promotion_package_id'   => ['nullable', 'required_if:promote_now,1', 'integer', 'exists:p2p_promotion_packages,id'],
            'promotion_wallet_id'    => ['nullable', 'required_if:promote_now,1', 'integer', 'exists:wallets,id'],
        ];
    }
}
