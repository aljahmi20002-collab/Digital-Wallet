<?php

namespace App\Http\Requests\Merchant;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMerchantPaymentMethodsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $merchant = $this->route('merchant');

        return $merchant instanceof Merchant
            && $this->user() !== null
            && $this->user()->can('merchant')
            && $this->user()->can('update', $merchant);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method_ids'   => ['nullable', 'array'],
            'payment_method_ids.*' => ['integer', 'distinct'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $merchant = $this->route('merchant');

            if (! $merchant instanceof Merchant) {
                return;
            }

            $eligibleIds = app(MerchantService::class)
                ->eligiblePaymentMethods($merchant)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $selectedIds = collect($this->input('payment_method_ids', []))
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (array_diff($selectedIds, $eligibleIds) !== []) {
                $validator->errors()->add(
                    'payment_method_ids',
                    __('Selected payment gateway must match a merchant-supported currency with an active merchant wallet.')
                );
            }
        });
    }
}
