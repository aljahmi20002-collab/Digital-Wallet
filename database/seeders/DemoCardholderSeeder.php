<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Enums\VirtualCard\CardholderStatus;
use App\Enums\VirtualCard\CardholderType;
use App\Models\Businesses;
use App\Models\Cardholders;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DemoCardholderSeeder
 *
 * Creates a fully populated cardholder profile for every demo account
 * created by `DemoAccountSeeder`. Every demo cardholder is now
 * **US-based** so issuance succeeds on every provider — Stripe Issuing
 * (test mode is US-only), StroWallet, Bitnob, Marqeta, Adyen, Lithic.
 *
 * The PII is obviously fake (`P-DEMO-…` IDs, demo SSN format with `XXX`
 * masking, demo document path) so nothing here is mistaken for real
 * data, but the structure (state codes, ZIP, phone) follows valid US
 * formats so provider validators accept the payload.
 *
 * Idempotent: re-running keeps rows in sync via `updateOrCreate`.
 */
class DemoCardholderSeeder extends Seeder
{
    public function run(): void
    {
        // Personal cardholder profiles — keyed by demo user email so the
        // pairing with `DemoAccountSeeder` stays explicit.
        $personalProfiles = [
            'ayesha.rahman@digikash.test' => [
                'title'              => 'Ms',
                'first_name'         => 'Ayesha',
                'middle_name'        => null,
                'last_name'          => 'Rahman',
                'email'              => 'ayesha.rahman.card@digikash.test',
                'mobile'             => '5550100101',
                'phone_country_code' => '+1',
                'gender'             => Gender::Female->value,
                'dob'                => '1992-04-18',
                'nationality'        => 'US',
                'place_of_birth'     => 'San Francisco, CA, USA',
                'relation'           => 'Self',

                'address_line1' => '525 Market Street',
                'address_line2' => 'Suite 1500',
                'city'          => 'San Francisco',
                'state'         => 'CA',
                'postal_code'   => '94105',
                'country'       => 'US',

                'id_type'          => 'drivers_license',
                'id_number'        => 'DL-DEMO-CA-0001',
                'id_issue_country' => 'US',
                'id_issue_date'    => '2022-01-15',
                'id_expiry'        => '2032-01-14',

                'tax_id'      => 'SSN-DEMO-XXX-XX-0101',
                'tax_country' => 'US',

                'occupation'      => 'Product Designer',
                'employer'        => 'DigiKash Sandbox Co.',
                'annual_income'   => 95000.00,
                'source_of_funds' => 'salary',

                'pep_flag'       => false,
                'sanctions_flag' => false,
            ],
            'imran.hossain@digikash.test' => [
                'title'              => 'Mr',
                'first_name'         => 'Imran',
                'middle_name'        => null,
                'last_name'          => 'Hossain',
                'email'              => 'imran.hossain.card@digikash.test',
                'mobile'             => '5550100200',
                'phone_country_code' => '+1',
                'gender'             => Gender::Male->value,
                'dob'                => '1989-11-03',
                'nationality'        => 'US',
                'place_of_birth'     => 'New York, NY, USA',
                'relation'           => 'Self',

                'address_line1' => '350 5th Avenue',
                'address_line2' => 'Suite 2100',
                'city'          => 'New York',
                'state'         => 'NY',
                'postal_code'   => '10118',
                'country'       => 'US',

                'id_type'          => 'drivers_license',
                'id_number'        => 'DL-DEMO-NY-0002',
                'id_issue_country' => 'US',
                'id_issue_date'    => '2021-06-01',
                'id_expiry'        => '2029-05-31',

                'tax_id'      => 'SSN-DEMO-XXX-XX-0202',
                'tax_country' => 'US',

                'occupation'      => 'Operations Manager',
                'employer'        => 'DigiKash Sandbox Co.',
                'annual_income'   => 110000.00,
                'source_of_funds' => 'salary',

                'pep_flag'       => false,
                'sanctions_flag' => false,
            ],
            'nusrat.farhana@digikash.test' => [
                'title'              => 'Ms',
                'first_name'         => 'Nusrat',
                'middle_name'        => null,
                'last_name'          => 'Farhana',
                'email'              => 'nusrat.farhana.card@digikash.test',
                'mobile'             => '5550100301',
                'phone_country_code' => '+1',
                'gender'             => Gender::Female->value,
                'dob'                => '1985-08-22',
                'nationality'        => 'US',
                'place_of_birth'     => 'Houston, TX, USA',
                'relation'           => 'Owner',

                'address_line1' => '811 Main Street',
                'address_line2' => null,
                'city'          => 'Houston',
                'state'         => 'TX',
                'postal_code'   => '77002',
                'country'       => 'US',

                'id_type'          => 'passport',
                'id_number'        => 'P-DEMO-US-2001',
                'id_issue_country' => 'US',
                'id_issue_date'    => '2018-03-10',
                'id_expiry'        => '2028-03-09',

                'tax_id'      => 'SSN-DEMO-XXX-XX-2001',
                'tax_country' => 'US',

                'occupation'      => 'Retail Owner',
                'employer'        => 'Nusrat Mart',
                'annual_income'   => 145000.00,
                'source_of_funds' => 'business',

                'pep_flag'       => false,
                'sanctions_flag' => false,
            ],
            'tanvir.ahmed@digikash.test' => [
                'title'              => 'Mr',
                'first_name'         => 'Tanvir',
                'middle_name'        => null,
                'last_name'          => 'Ahmed',
                'email'              => 'tanvir.ahmed.card@digikash.test',
                'mobile'             => '5550100302',
                'phone_country_code' => '+1',
                'gender'             => Gender::Male->value,
                'dob'                => '1990-02-14',
                'nationality'        => 'US',
                'place_of_birth'     => 'Miami, FL, USA',
                'relation'           => 'Owner',

                'address_line1' => '1200 Brickell Avenue',
                'address_line2' => 'Floor 3',
                'city'          => 'Miami',
                'state'         => 'FL',
                'postal_code'   => '33131',
                'country'       => 'US',

                'id_type'          => 'drivers_license',
                'id_number'        => 'DL-DEMO-FL-2002',
                'id_issue_country' => 'US',
                'id_issue_date'    => '2020-09-01',
                'id_expiry'        => '2030-08-31',

                'tax_id'      => 'SSN-DEMO-XXX-XX-2002',
                'tax_country' => 'US',

                'occupation'      => 'Electronics Retailer',
                'employer'        => 'Tanvir Tech Care',
                'annual_income'   => 90000.00,
                'source_of_funds' => 'business',

                'pep_flag'       => false,
                'sanctions_flag' => false,
            ],
        ];

        // Business profiles — only attached to merchant accounts so the
        // KYB flow has something realistic to chew on. Both companies are
        // Delaware/Texas incorporated for maximum provider compatibility.
        $businessProfiles = [
            'nusrat.farhana@digikash.test' => [
                'business' => [
                    'business_name'         => 'Nusrat Mart LLC',
                    'trading_name'          => 'Nusrat Mart',
                    'registration_number'   => 'BIZ-DEMO-US-0001',
                    'tin'                   => 'EIN-DEMO-XX-0000001',
                    'business_type'         => 'llc',
                    'incorporation_date'    => '2018-05-10',
                    'incorporation_country' => 'US',
                    'industry'              => 'Retail',
                    'mcc_code'              => '5411',
                    'website_url'           => 'https://nusrat-mart.digikash.test',
                    'contact_email'         => 'payments@nusrat-mart.digikash.test',
                    'contact_phone'         => '5550100301',
                    'phone_country_code'    => '+1',
                    'address_line1'         => '811 Main Street',
                    'address_line2'         => null,
                    'city'                  => 'Houston',
                    'state'                 => 'TX',
                    'postal_code'           => '77002',
                    'country'               => 'US',
                    'documents'             => [
                        'incorporation_certificate' => 'demo/business/incorporation-demo-1.pdf',
                        'tax_certificate'           => 'demo/business/tax-demo-1.pdf',
                        'utility_bill'              => 'demo/business/utility-demo-1.pdf',
                    ],
                    'beneficial_owners' => [
                        [
                            'name'          => 'Nusrat Farhana',
                            'dob'           => '1985-08-22',
                            'ownership_pct' => 60.0,
                            'country'       => 'US',
                            'id_type'       => 'passport',
                            'id_number'     => 'P-DEMO-US-2001',
                        ],
                        [
                            'name'          => 'Rumana Farhana',
                            'dob'           => '1988-12-05',
                            'ownership_pct' => 40.0,
                            'country'       => 'US',
                            'id_type'       => 'drivers_license',
                            'id_number'     => 'DL-DEMO-TX-2003',
                        ],
                    ],
                    'kyc_status' => KycStatus::APPROVED,
                    'status'     => true,
                ],
                'cardholder_overrides' => [
                    'card_type'  => CardholderType::BUSINESS->value,
                    'kyc_status' => KycStatus::APPROVED->value,
                    'status'     => CardholderStatus::APPROVED->value,
                ],
            ],
            'tanvir.ahmed@digikash.test' => [
                'business' => [
                    'business_name'         => 'Tanvir Tech Care LLC',
                    'trading_name'          => 'Tanvir Tech Care',
                    'registration_number'   => 'BIZ-DEMO-US-0002',
                    'tin'                   => 'EIN-DEMO-XX-0000002',
                    'business_type'         => 'llc',
                    'incorporation_date'    => '2020-01-22',
                    'incorporation_country' => 'US',
                    'industry'              => 'Electronics / Repair',
                    'mcc_code'              => '5732',
                    'website_url'           => 'https://tanvir-tech-care.digikash.test',
                    'contact_email'         => 'billing@tanvir-tech-care.digikash.test',
                    'contact_phone'         => '5550100302',
                    'phone_country_code'    => '+1',
                    'address_line1'         => '1200 Brickell Avenue',
                    'address_line2'         => 'Floor 3',
                    'city'                  => 'Miami',
                    'state'                 => 'FL',
                    'postal_code'           => '33131',
                    'country'               => 'US',
                    'documents'             => [
                        'incorporation_certificate' => 'demo/business/incorporation-demo-2.pdf',
                        'tax_certificate'           => 'demo/business/tax-demo-2.pdf',
                    ],
                    'beneficial_owners' => [
                        [
                            'name'          => 'Tanvir Ahmed',
                            'dob'           => '1990-02-14',
                            'ownership_pct' => 100.0,
                            'country'       => 'US',
                            'id_type'       => 'drivers_license',
                            'id_number'     => 'DL-DEMO-FL-2002',
                        ],
                    ],
                    'kyc_status' => KycStatus::PENDING,
                    'status'     => true,
                ],
                'cardholder_overrides' => [
                    'card_type'  => CardholderType::BUSINESS->value,
                    'kyc_status' => KycStatus::PENDING->value,
                    'status'     => CardholderStatus::PENDING->value,
                ],
            ],
        ];

        // Demo file used as the ID document — kept in `public/general/static/svg`
        // which always exists, so the row never points at a missing path.
        $demoIdDocument = 'general/static/svg/cardHolder.svg';

        // ---- Personal cardholders ----
        foreach ($personalProfiles as $email => $profile) {
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                $this->command?->warn("DemoCardholderSeeder: user {$email} not found, skipping.");

                continue;
            }

            $payload = array_merge($profile, [
                'user_id'       => $user->id,
                'card_type'     => CardholderType::PERSONAL->value,
                'kyc_documents' => ['id_document' => $demoIdDocument],
                'kyc_status'    => KycStatus::APPROVED->value,
                'status'        => CardholderStatus::APPROVED->value,
                'note'          => 'Seeded demo personal cardholder — for sandbox card issuance only.',
            ]);

            Cardholders::query()->updateOrCreate(
                [
                    'user_id'   => $user->id,
                    'email'     => $profile['email'],
                    'card_type' => CardholderType::PERSONAL->value,
                ],
                $payload
            );
        }

        // ---- Business cardholders ----
        foreach ($businessProfiles as $email => $bundle) {
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                $this->command?->warn("DemoCardholderSeeder: user {$email} not found, skipping business profile.");

                continue;
            }

            // Cardholder row provides the human-name fields too — providers
            // that issue business cards still need a contact identity.
            $personal = $personalProfiles[$email] ?? [];

            $business = Businesses::query()->updateOrCreate(
                [
                    'user_id'       => $user->id,
                    'business_name' => $bundle['business']['business_name'],
                ],
                array_merge($bundle['business'], ['user_id' => $user->id])
            );

            $cardholderPayload = array_merge(
                $personal,
                $bundle['cardholder_overrides'],
                [
                    'user_id'       => $user->id,
                    'businesses_id' => $business->id,
                    'kyc_documents' => ['id_document' => $demoIdDocument],
                    'note'          => 'Seeded demo business cardholder — for sandbox card issuance only.',
                ]
            );

            // Business cardholder row needs distinct lookup keys so
            // updateOrCreate doesn't collide with the personal one above.
            Cardholders::query()->updateOrCreate(
                [
                    'user_id'       => $user->id,
                    'businesses_id' => $business->id,
                    'card_type'     => CardholderType::BUSINESS->value,
                ],
                $cardholderPayload
            );
        }

        $this->command?->info('DemoCardholderSeeder: seeded '
            .count($personalProfiles).' personal and '
            .count($businessProfiles).' business cardholders (all US-based).');
    }
}
