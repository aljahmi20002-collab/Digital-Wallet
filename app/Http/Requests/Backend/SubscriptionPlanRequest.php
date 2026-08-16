<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $planId = $this->route('plan')?->id;

        return [
            'name'                => ['required', 'string', 'max:100'],
            'slug'                => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/', Rule::unique('subscription_plans', 'slug')->ignore($planId)],
            'description'         => ['nullable', 'string', 'max:2000'],
            'trial_days'          => ['nullable', 'integer', 'min:0', 'max:365'],
            'grace_days'          => ['nullable', 'integer', 'min:0', 'max:90'],
            'is_featured'         => ['nullable', 'boolean'],
            'plan_badge'          => ['nullable', 'string', 'max:50'],
            'auto_renew_default'  => ['nullable', 'boolean'],
            'cancellation_policy' => ['required', Rule::in(['immediate', 'end_of_period'])],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'status'              => ['nullable', 'boolean'],
            // Prices — base monthly price + optional half_yearly/yearly discounts
            'price'                => ['required', 'numeric', 'min:0'],
            'half_yearly_discount' => ['nullable', 'integer', 'min:1', 'max:99'],
            'yearly_discount'      => ['nullable', 'integer', 'min:1', 'max:99'],
            // Features array
            'features'                 => ['nullable', 'array'],
            'features.*.feature_key'   => ['required', 'string', 'max:100'],
            'features.*.feature_label' => ['required', 'string', 'max:150'],
            'features.*.feature_value' => ['required', 'string', 'max:100'],
            'features.*.feature_type'  => ['required', Rule::in(['limit', 'toggle', 'quota'])],
            'features.*.reset_cycle'   => ['nullable', Rule::in(['daily', 'weekly', 'monthly', null])],
            'features.*.sort_order'    => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex'             => __('Slug must contain only lowercase letters, numbers, and hyphens.'),
            'slug.unique'            => __('This slug is already in use by another plan.'),
            'price.required'         => __('A base monthly price is required.'),
            'price.numeric'          => __('Price must be a valid number.'),
            'half_yearly_discount.*' => __('Half yearly discount must be between 1 and 99.'),
            'yearly_discount.*'      => __('Yearly discount must be between 1 and 99.'),
        ];
    }
}
