<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;

class MerchantActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'merchant_id'      => ['required', 'integer', 'exists:merchants,id'],
            'fee'              => ['required_if:action,approve', 'nullable', 'numeric', 'min:0', 'max:100'],
            'action'           => ['required', 'in:approve,reject,disable,enable'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
