<?php

namespace App\Http\Requests\PhoneVerification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyPhoneRequest extends FormRequest
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
            'code' => ['required', 'string', 'min:4', 'max:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => __('Please enter the verification code.'),
            'code.min'      => __('The verification code is too short.'),
            'code.max'      => __('The verification code is too long.'),
        ];
    }
}
