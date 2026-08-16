<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerQrCashOutRequest extends FormRequest
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
        return [
            'wallet_id' => [
                'required',
                Rule::exists('wallets', 'id')->where('user_id', (int) $this->user()?->id),
            ],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'wallet_pin' => ['required', 'digits:6'],
            'note'       => ['nullable', 'string', 'max:255'],
        ];
    }
}
