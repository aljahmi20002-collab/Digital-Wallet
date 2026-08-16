<?php

namespace App\Http\Requests\CustomLanding;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateCustomLandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customLanding = $this->route('custom_landing');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('custom_landings', 'name')->ignore($customLanding?->id),
            ],
            'status' => [
                'required',
                'boolean',
            ],
            'zipFile' => [
                'nullable',
                File::types(['zip'])->max('10mb'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'    => __('Landing page name'),
            'status'  => __('Publication status'),
            'zipFile' => __('ZIP file'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => __('A landing page with this name already exists.'),
        ];
    }
}
