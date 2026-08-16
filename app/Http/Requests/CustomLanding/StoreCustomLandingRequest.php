<?php

namespace App\Http\Requests\CustomLanding;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCustomLandingRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('custom_landings', 'name'),
            ],
            'zipFile' => [
                'required',
                File::types(['zip'])->max('10mb'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'    => __('Landing page name'),
            'zipFile' => __('ZIP file'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'      => __('A landing page with this name already exists.'),
            'zipFile.required' => __('Please upload a ZIP file containing index.html.'),
        ];
    }
}
