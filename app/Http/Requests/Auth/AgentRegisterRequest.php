<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Validator;

class AgentRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => 'required|string|max:255|unique:users,username',
            'email'      => 'required|email|unique:users,email',
            'country'    => 'required|string|size:2',
            'phone'      => 'required|string',
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('country')) {
                return;
            }

            if ($this->countryData() === null) {
                $validator->errors()->add('country', __('The selected country is invalid.'));

                return;
            }

            $allowedCountries = $this->allowedCountries();
            if ($allowedCountries !== [] && ! in_array($this->countryCode(), $allowedCountries, true)) {
                $validator->errors()->add(
                    'country',
                    __('The Agent program is not available in :country yet.', ['country' => $this->countryCode()])
                );
            }
        });
    }

    public function countryCode(): string
    {
        return strtoupper((string) $this->input('country', ''));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function countryData(): ?array
    {
        $countryData = getCountryByCode($this->countryCode());

        return is_array($countryData) ? $countryData : null;
    }

    public function formattedPhone(): string
    {
        $countryData = $this->countryData() ?? [];

        return formatPhone((string) ($countryData['dial_code'] ?? ''), (string) $this->input('phone'));
    }

    /**
     * @return array<int, string>
     */
    private function allowedCountries(): array
    {
        $raw = (string) setting('agent_allowed_countries', '');
        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->values()
            ->all();
    }
}
