<?php

namespace App\Http\Requests\Merchant;

use App\Models\Merchant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $merchant = $this->route('merchant');

        return $this->user() !== null
            && $this->user()->can('merchant')
            && $merchant instanceof Merchant
            && (int) $merchant->user_id === (int) $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $merchant   = $this->route('merchant');
        $merchantId = $merchant instanceof Merchant ? $merchant->id : null;

        return [
            'business_name'  => ['required', 'string', 'max:255'],
            'site_url'       => ['required', 'url', 'max:255', Rule::unique('merchants', 'site_url')->ignore($merchantId)],
            'currency_ids'   => ['required', 'array', 'min:1'],
            'currency_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('currencies', 'id'),
                Rule::in($this->activeWalletCurrencyIds()),
            ],
            'business_logo'        => ['nullable', 'image', 'max:1024'],
            'business_email'       => ['nullable', 'email', 'max:255'],
            'business_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('currency_ids') && $this->filled('currency_id')) {
            $this->merge([
                'currency_ids' => [(int) $this->input('currency_id')],
            ]);
        }
    }

    /**
     * @return array<int, int>
     */
    private function activeWalletCurrencyIds(): array
    {
        return $this->user()?->activeWallets()
            ->pluck('currency_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all() ?? [];
    }
}
