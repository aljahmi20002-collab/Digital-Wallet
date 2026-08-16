<?php

namespace App\Http\Requests\Backend;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
        return Setting::getValidationRules($this->section());
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Setting::getValidationMessages($this->section());
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return Setting::getValidationAttributes($this->section());
    }

    protected function failedValidation(Validator $validator): void
    {
        session()->flash('section', $this->section());
        notifyEvs('error', $validator->errors()->first() ?: __('Please review the settings fields.'));

        parent::failedValidation($validator);
    }

    private function section(): string
    {
        return (string) $this->route('site', 'general_settings');
    }
}
