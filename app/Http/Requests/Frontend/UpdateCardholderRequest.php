<?php

namespace App\Http\Requests\Frontend;

use App\Enums\Gender;
use App\Enums\VirtualCard\CardholderType;
use App\Models\Cardholders;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCardholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add your authorization logic if needed
        return true;
    }

    public function rules(): array
    {
        /** @var Cardholders $cardholder */
        $cardholder = $this->route('cardholder') ?? $this->route('id');
        if (is_numeric($cardholder)) {
            $cardholder = Cardholders::findOrFail($cardholder);
        }

        $rules = [
            'card_type' => [
                'required',
                Rule::in(array_keys(CardholderType::options())),
                function ($attribute, $value, $fail) use ($cardholder) {
                    if ($cardholder && $value !== $cardholder->card_type->value) {
                        $fail(__('Cardholder type cannot be changed.'));
                    }
                },
            ],
            // ID document upload (file is optional on update — keeping the
            // current document is fine if user is just editing other fields)
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],

            // Legacy KYC template — kept nullable for backward compatibility
            'kyc_template_id' => ['nullable', 'exists:kyc_templates,id'],
            // ---------------- Personal cardholder ----------------
            // Identity
            'title'              => ['nullable', 'string', 'max:12'],
            'first_name'         => ['nullable', 'string', 'max:100'],
            'middle_name'        => ['nullable', 'string', 'max:100'],
            'last_name'          => ['nullable', 'string', 'max:100'],
            'email'              => ['nullable', 'email', 'max:100'],
            'mobile'             => ['nullable', 'string', 'max:30'],
            'phone_country_code' => ['nullable', 'string', 'max:8'],
            'gender'             => ['nullable', Rule::in(array_keys(Gender::options()))],
            'dob'                => ['nullable', 'date'],
            'nationality'        => ['nullable', 'string', 'max:10'],
            'place_of_birth'     => ['nullable', 'string', 'max:191'],
            'relation'           => ['nullable', 'string', 'max:100'],

            // Address
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'state'         => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'max:10'],

            // Government ID
            'id_type'          => ['nullable', 'string', 'max:32'],
            'id_number'        => ['nullable', 'string', 'max:191'],
            'id_issue_country' => ['nullable', 'string', 'max:10'],
            'id_issue_date'    => ['nullable', 'date'],
            'id_expiry'        => ['nullable', 'date', 'after_or_equal:id_issue_date'],

            // Tax
            'tax_id'      => ['nullable', 'string', 'max:191'],
            'tax_country' => ['nullable', 'string', 'max:10'],

            // Employment / AML
            'occupation'      => ['nullable', 'string', 'max:191'],
            'employer'        => ['nullable', 'string', 'max:191'],
            'annual_income'   => ['nullable', 'numeric', 'min:0'],
            'source_of_funds' => ['nullable', 'string', 'max:64'],

            // Compliance flags
            'pep_flag'       => ['nullable', 'boolean'],
            'sanctions_flag' => ['nullable', 'boolean'],

            // ---------------- Business cardholder ----------------
            'business_name'         => ['nullable', 'string', 'max:255'],
            'trading_name'          => ['nullable', 'string', 'max:255'],
            'registration_number'   => ['nullable', 'string', 'max:100'],
            'tin'                   => ['nullable', 'string', 'max:100'],
            'business_type'         => ['nullable', 'string', 'max:100'],
            'incorporation_date'    => ['nullable', 'date'],
            'incorporation_country' => ['nullable', 'string', 'max:10'],
            'industry'              => ['nullable', 'string', 'max:100'],
            'mcc_code'              => ['nullable', 'string', 'max:8'],
            'website_url'           => ['nullable', 'url', 'max:255'],

            'contact_email'        => ['nullable', 'email', 'max:100'],
            'contact_phone'        => ['nullable', 'string', 'max:30'],
            'phone_country_code_b' => ['nullable', 'string', 'max:8'],

            'address_line1_b' => ['nullable', 'string', 'max:255'],
            'address_line2_b' => ['nullable', 'string', 'max:255'],
            'city_b'          => ['nullable', 'string', 'max:100'],
            'state_b'         => ['nullable', 'string', 'max:100'],
            'postal_code_b'   => ['nullable', 'string', 'max:20'],
            'country_b'       => ['nullable', 'string', 'max:10'],

            // Beneficial owners (UBO ≥ 25%)
            'beneficial_owners'                 => ['nullable', 'array', 'max:10'],
            'beneficial_owners.*.name'          => ['nullable', 'string', 'max:191'],
            'beneficial_owners.*.dob'           => ['nullable', 'date'],
            'beneficial_owners.*.ownership_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'beneficial_owners.*.country'       => ['nullable', 'string', 'max:10'],
            'beneficial_owners.*.id_type'       => ['nullable', 'string', 'max:32'],
            'beneficial_owners.*.id_number'     => ['nullable', 'string', 'max:191'],
        ];

        return $rules;
    }
}
