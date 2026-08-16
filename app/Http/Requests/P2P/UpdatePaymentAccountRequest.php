<?php

declare(strict_types=1);

namespace App\Http\Requests\P2P;

use App\Models\P2P\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $paymentAccount = $this->route('paymentAccount');

        return auth()->check()
            && $paymentAccount instanceof PaymentAccount
            && (int) $paymentAccount->user_id === (int) auth()->id();
    }

    public function rules(): array
    {
        /** @var PaymentAccount|null $paymentAccount */
        $paymentAccount = $this->route('paymentAccount');

        return [
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('p2p_payment_methods', 'id'),
                Rule::unique('p2p_payment_accounts', 'payment_method_id')
                    ->ignore($paymentAccount?->id)
                    ->where(fn ($query) => $query->where('user_id', auth()->id())->whereNull('deleted_at')),
            ],
            'label' => ['nullable', 'string', 'max:191'],
        ];
    }
}
