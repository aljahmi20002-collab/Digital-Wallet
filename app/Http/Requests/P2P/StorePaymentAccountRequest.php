<?php

declare(strict_types=1);

namespace App\Http\Requests\P2P;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('p2p_payment_methods', 'id'),
                Rule::unique('p2p_payment_accounts', 'payment_method_id')
                    ->where(fn ($query) => $query->where('user_id', auth()->id())->whereNull('deleted_at')),
            ],
            'label' => ['nullable', 'string', 'max:191'],
        ];
    }
}
