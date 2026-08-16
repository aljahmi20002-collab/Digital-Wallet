<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class ActivateProjectLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_code.required' => __('The Envato purchase code is required.'),
            'purchase_code.regex'    => __('Enter a valid Envato purchase code from the license certificate.'),
        ];
    }
}
