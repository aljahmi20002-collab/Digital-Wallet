<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentCashOutRequest extends FormRequest
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
            'agent_id' => [
                'required',
                Rule::exists('agents', 'id')->where('user_id', (int) $this->user()?->id),
            ],
            'wallet_id' => [
                'required',
                Rule::exists('wallets', 'id')->where('user_id', (int) $this->user()?->id),
            ],
            'customer'     => ['required', 'string', 'max:255'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'customer_otp' => ['required', 'digits:6'],
            'note'         => ['nullable', 'string', 'max:255'],
        ];
    }
}
