<?php

namespace App\Http\Requests\Frontend;

use App\Enums\VirtualCard\CardholderType;
use Illuminate\Foundation\Http\FormRequest;

class StoreCardholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'card_type' => ['required', 'string'],

            // ---------------- Personal cardholder ----------------
            // Identity
            'title'          => ['nullable', 'string', 'max:12'],
            'first_name'     => ['nullable', 'string', 'max:191'],
            'middle_name'    => ['nullable', 'string', 'max:191'],
            'last_name'      => ['nullable', 'string', 'max:191'],
            'gender'         => ['nullable', 'string'],
            'dob'            => ['nullable', 'date'],
            'nationality'    => ['nullable', 'string', 'max:10'],
            'place_of_birth' => ['nullable', 'string', 'max:191'],
            'relation'       => ['nullable', 'string', 'max:100'],

            // Contact
            'email'              => ['nullable', 'email', 'max:191'],
            'mobile'             => ['nullable', 'string', 'max:30'],
            'phone_country_code' => ['nullable', 'string', 'max:8'],

            // Address (billing)
            'address_line1' => ['nullable', 'string', 'max:191'],
            'address_line2' => ['nullable', 'string', 'max:191'],
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

            // Tax / fiscal
            'tax_id'      => ['nullable', 'string', 'max:191'],
            'tax_country' => ['nullable', 'string', 'max:10'],

            // Employment / AML
            'occupation'      => ['nullable', 'string', 'max:191'],
            'employer'        => ['nullable', 'string', 'max:191'],
            'annual_income'   => ['nullable', 'numeric', 'min:0'],
            'source_of_funds' => ['nullable', 'string', 'max:64'],

            // Compliance
            'pep_flag'       => ['nullable', 'boolean'],
            'sanctions_flag' => ['nullable', 'boolean'],

            // ID document upload (replaces the legacy KYC-template flow —
            // a single file living under `kyc_documents['id_document']`)
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],

            // Legacy compatibility — leave both nullable so existing
            // installations can still POST these without 422-ing.
            'kyc_template_id' => ['nullable', 'exists:kyc_templates,id'],
            'credentials'     => ['nullable', 'array'],

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

            // Beneficial owners (UBO ≥ 25%) — array of objects
            'beneficial_owners'                 => ['nullable', 'array', 'max:10'],
            'beneficial_owners.*.name'          => ['nullable', 'string', 'max:191'],
            'beneficial_owners.*.dob'           => ['nullable', 'date'],
            'beneficial_owners.*.ownership_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'beneficial_owners.*.country'       => ['nullable', 'string', 'max:10'],
            'beneficial_owners.*.id_type'       => ['nullable', 'string', 'max:32'],
            'beneficial_owners.*.id_number'     => ['nullable', 'string', 'max:191'],
        ];

        if ($this->input('card_type') === CardholderType::PERSONAL->value) {
            $rules['first_name']  = ['required', 'string', 'max:191'];
            $rules['last_name']   = ['required', 'string', 'max:191'];
            $rules['email']       = ['required', 'email', 'max:191'];
            $rules['mobile']      = ['required', 'string', 'max:30'];
            $rules['dob']         = ['required', 'date'];
            $rules['country']     = ['required', 'string', 'max:10'];
            $rules['id_type']     = ['required', 'string', 'max:32'];
            $rules['id_number']   = ['required', 'string', 'max:191'];
            $rules['id_document'] = ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'];
        } elseif ($this->input('card_type') === CardholderType::BUSINESS->value) {
            $rules['business_name']   = ['required', 'string', 'max:255'];
            $rules['business_type']   = ['required', 'string', 'max:100'];
            $rules['contact_email']   = ['required', 'email', 'max:100'];
            $rules['contact_phone']   = ['required', 'string', 'max:30'];
            $rules['address_line1_b'] = ['required', 'string', 'max:255'];
            $rules['country_b']       = ['required', 'string', 'max:10'];
        }

        return $rules;
    }
}
