<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('merchant');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business_name'  => ['required', 'string', 'max:255'],
            'site_url'       => ['required', 'url', 'max:255', Rule::unique('merchants', 'site_url')],
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
