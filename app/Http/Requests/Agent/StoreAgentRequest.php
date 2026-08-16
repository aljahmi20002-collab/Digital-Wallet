<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('agent');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'agent_name'     => 'required|string|max:255',
            'currency_ids'   => ['required', 'array', 'min:1'],
            'currency_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('currencies', 'id'),
                Rule::in($this->activeWalletCurrencyIds()),
            ],
            'logo'        => 'nullable|image|max:1024',
            'description' => 'nullable|string|max:500',
        ];
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
