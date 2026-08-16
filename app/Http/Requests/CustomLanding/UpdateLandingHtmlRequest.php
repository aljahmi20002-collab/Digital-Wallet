<?php

namespace App\Http\Requests\CustomLanding;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLandingHtmlRequest extends FormRequest
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
            'htmlContent' => [
                'required',
                'string',
                'max:1048576',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'htmlContent' => __('HTML content'),
        ];
    }
}
