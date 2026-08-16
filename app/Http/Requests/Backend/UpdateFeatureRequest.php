<?php

declare(strict_types=1);

namespace App\Http\Requests\Backend;

use App\Models\FeatureAccessRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'is_enabled' => 'required|boolean',
            'panels'     => 'required|array',
        ];

        // Each panel block is optional — only the panels declared by the
        // feature's catalog config are rendered in the form, so absent
        // panel keys must not fail validation.
        foreach (FeatureAccessRule::PANELS as $panel) {
            $rules["panels.{$panel}"]                   = 'sometimes|array';
            $rules["panels.{$panel}.is_visible"]        = 'sometimes|boolean';
            $rules["panels.{$panel}.is_accessible"]     = 'sometimes|boolean';
            $rules["panels.{$panel}.requires_kyc"]      = 'nullable|boolean';
            $rules["panels.{$panel}.requires_phone"]    = 'nullable|boolean';
            $rules["panels.{$panel}.countries_allowed"] = ['nullable', 'string', 'max:500'];
        }

        $rules['panels.*.panel'] = ['sometimes', Rule::in(FeatureAccessRule::PANELS)];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'is_enabled.required' => __('The global enable/disable state is required.'),
            'panels.required'     => __('Panel access rules must be provided.'),
        ];
    }
}
