<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class GiftCardStoreRequest extends FormRequest
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
            'gift_card_template_id' => ['required', 'integer', 'exists:gift_card_templates,id'],
            'wallet_id'             => ['required', 'integer', 'exists:wallets,id'],
            'amount'                => ['required', 'numeric', 'min:1'],
            'recipient_mode'        => ['required', 'in:user,email'],
            'recipient_name'        => ['required', 'string', 'max:120'],
            'recipient_email'       => ['required', 'email', 'max:191'],
            'sender_name'           => ['required', 'string', 'max:120'],
            'message'               => ['nullable', 'string', 'max:200'],
            'schedule'              => ['nullable', 'in:0,1,true,false'],
            'scheduled_at'          => ['nullable', 'date', 'after:now'],
            'terms'                 => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'gift_card_template_id.required' => __('Please choose a gift card design.'),
            'wallet_id.required'             => __('Please select a wallet to pay from.'),
            'amount.required'                => __('Please enter the gift card amount.'),
            'recipient_email.required'       => __('Recipient email is required so we can deliver the card.'),
            'terms.accepted'                 => __('You must agree to the gift card terms.'),
        ];
    }
}
