<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'old_password' => ['required', 'current_password'],
            'password'     => [
                'required',
                'confirmed',
                Password::min(6),
            ],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'old_password.required'         => __('Please enter your current password.'),
            'old_password.current_password' => __('Wrong current password.'),
            'password.required'             => __('Please enter a new password.'),
            'password.confirmed'            => __('New password confirmation does not match.'),
        ];
    }
}
