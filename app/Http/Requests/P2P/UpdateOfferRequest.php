<?php

declare(strict_types=1);

namespace App\Http\Requests\P2P;

use App\Models\P2P\Offer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offer = $this->route('offer');

        return auth()->check()
            && $offer instanceof Offer
            && (int) $offer->user_id === (int) auth()->id();
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
        ];
    }
}
