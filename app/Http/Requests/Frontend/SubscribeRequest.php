<?php

namespace App\Http\Requests\Frontend;

use App\Enums\BillingCycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('subscription_plans', 'id')->where('status', true),
            ],
            'billing_cycle' => [
                'required',
                Rule::in([BillingCycle::Monthly->value, BillingCycle::HalfYearly->value, BillingCycle::Yearly->value]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.exists'         => __('The selected plan is not available.'),
            'billing_cycle.in'       => __('Invalid billing cycle selected.'),
            'billing_cycle.required' => __('Please select a billing cycle.'),
        ];
    }
}
