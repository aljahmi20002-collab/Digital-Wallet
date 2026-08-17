<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TestInstallEnvatoLicenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_url'              => ['nullable', 'url', 'max:255'],
            'envato_purchase_code' => ['required', 'string', 'max:80', 'regex:/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'envato_purchase_code.required' => __('Envato purchase code is required before installation.'),
            'envato_purchase_code.regex'    => __('Enter a valid Envato purchase code from your license certificate.'),
        ];
    }
}
