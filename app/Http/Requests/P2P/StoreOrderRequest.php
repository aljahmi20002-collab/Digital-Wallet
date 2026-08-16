<?php

declare(strict_types=1);

namespace App\Http\Requests\P2P;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'offer_id' => [
                'required',
                'integer',
                Rule::exists('p2p_offers', 'id')->whereNull('deleted_at'),
            ],
            'amount'             => ['required', 'numeric', 'min:0.00000001'],
            'payment_account_id' => [
                'required',
                'integer',
                Rule::exists('p2p_payment_accounts', 'id')
                    ->where(fn ($query) => $query
                        ->where('user_id', auth()->id())
                        ->whereNull('deleted_at')),
            ],
            'agree_terms' => ['accepted'],
        ];
    }
}
