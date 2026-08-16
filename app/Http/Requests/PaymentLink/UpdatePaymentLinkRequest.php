<?php

declare(strict_types=1);

namespace App\Http\Requests\PaymentLink;

use App\Models\PaymentLink;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentLinkRequest extends FormRequest
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
            'title'        => ['required', 'string', 'max:120'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'merchant_id'  => ['nullable', 'integer', 'exists:merchants,id'],
            'currency_id'  => [$hasMerchant ? 'nullable' : 'required', 'integer', 'exists:currencies,id'],
            'amount'       => ['nullable', 'numeric', 'min:0.01'],
            'min_amount'   => ['nullable', 'numeric', 'min:0.01'],
            'max_amount'   => ['nullable', 'numeric', 'min:0.01', 'gte:min_amount'],
            'expires_at'   => ['nullable', 'date'],
            'max_payments' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $amount    = $this->input('amount');
            $minAmount = $this->input('min_amount');
            $maxAmount = $this->input('max_amount');

            if ($amount !== null && $amount !== '' && (($minAmount !== null && $minAmount !== '') || ($maxAmount !== null && $maxAmount !== ''))) {
                $validator->errors()->add('amount', __('Use either a fixed amount or min/max range, not both.'));
            }

            // Mirror StorePaymentLinkRequest semantics: a newly entered or
            // changed expiry must be in the future. We skip this check when
            // the submitted value matches the link's existing expires_at so
            // the user can re-save other fields on a link whose expiry has
            // already passed without being forced to clear/reset it.
            //
            // Comparison is done at minute precision on the formatted string
            // to avoid timezone / sub-second drift between the cast model
            // attribute and the freshly-parsed datetime-local input.
            $newExpiresAt = $this->input('expires_at');

            if ($newExpiresAt !== null && $newExpiresAt !== '') {
                $newCarbon = $this->safeCarbonParse($newExpiresAt);
                $existing  = $this->existingExpiresAt();

                $unchanged = $existing !== null
                    && $newCarbon !== null
                    && $existing->format('Y-m-d H:i') === $newCarbon->format('Y-m-d H:i');

                if (! $unchanged && $newCarbon !== null && $newCarbon->isPast()) {
                    $validator->errors()->add('expires_at', __('Expiry must be a future date and time.'));
                }
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

    protected function existingExpiresAt(): ?Carbon
    {
        $link = $this->route('paymentLink');

        if ($link instanceof PaymentLink) {
            return $link->expires_at;
        }

        return null;
    }

    protected function safeCarbonParse(mixed $value): ?Carbon
    {
        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
