<?php

declare(strict_types=1);

namespace App\Http\Requests\PaymentLink;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hasMerchant = $this->filled('merchant_id');

        return [
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'merchant_id' => ['nullable', 'integer', 'exists:merchants,id'],
            // Currency is mandatory only when no merchant shop is selected;
            // with a merchant selected, the service accepts only that shop's
            // supported currencies and falls back to the primary one.
            'currency_id'  => [$hasMerchant ? 'nullable' : 'required', 'integer', 'exists:currencies,id'],
            'amount'       => ['nullable', 'numeric', 'min:0.01'],
            'min_amount'   => ['nullable', 'numeric', 'min:0.01'],
            'max_amount'   => ['nullable', 'numeric', 'min:0.01', 'gte:min_amount'],
            'expires_at'   => ['nullable', 'date', 'after:now'],
            'max_payments' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $amount    = $this->input('amount');
            $minAmount = $this->input('min_amount');
            $maxAmount = $this->input('max_amount');

            // Fixed amount and open amount are mutually exclusive.
            if ($amount !== null && $amount !== '' && (($minAmount !== null && $minAmount !== '') || ($maxAmount !== null && $maxAmount !== ''))) {
                $validator->errors()->add('amount', __('Use either a fixed amount or min/max range, not both.'));
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required'       => __('Please provide a title for this payment link.'),
            'currency_id.required' => __('Please choose a currency.'),
            'currency_id.exists'   => __('Selected currency is invalid.'),
            'expires_at.after'     => __('Expiry must be a future date and time.'),
        ];
    }
}
