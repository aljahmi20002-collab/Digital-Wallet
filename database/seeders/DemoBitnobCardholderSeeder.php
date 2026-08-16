<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Enums\VirtualCard\CardholderStatus;
use App\Enums\VirtualCard\CardholderType;
use App\Models\Cardholders;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DemoBitnobCardholderSeeder
 *
 * Bitnob's virtual-card KYC enumeration rejects US cardholders ("ID type
 * not supported for unitedstates"). Per the user's testing on a sibling
 * project, Bitnob sandbox accepts Madagascar (MG) for virtual-card
 * issuance. This seeder creates a parallel set of Madagascar-based
 * cardholders for every demo account so Bitnob has data it can accept.
 *
 * It does NOT replace the US-based personal cardholders seeded by
 * `DemoCardholderSeeder` — those stay in place for Stripe Issuing test
 * mode. Each demo user ends up with both:
 *
 *   user_id=N + email=name.card@digikash.test  (US, for Stripe)
 *   user_id=N + email=name.mg@digikash.test    (MG, for Bitnob)
 *
 * The lookup key on `Cardholders::updateOrCreate` is the trio
 * (user_id, email, card_type), so the two rows never collide.
 *
 * Madagascar specifics encoded here:
 *   - ISO country code:  MG
 *   - Phone prefix:      +261  (mobile bands 32 / 33 / 34 / 38)
 *   - ID types:          CIN (mapped to NATIONAL_ID), PASSPORT,
 *                        DRIVERS_LICENSE — all in Bitnob's accepted set
 *   - Cities:            Antananarivo (101), Toamasina (501),
 *                        Mahajanga (401), Fianarantsoa (301)
 *   - No BVN — that's a Nigeria-only requirement and Bitnob skips it
 *     for non-NG countries.
 *
 * Idempotent: safe to re-run, never creates duplicates.
 */
class DemoBitnobCardholderSeeder extends Seeder
{
    public function run(): void
    {
        // Bitnob's KYC verifier silently drops images that don't look
        // like real photos (the previous attempt with `placeholder.png`
        // came back with `idImage: null` in Bitnob's response, which is
        // why card creation kept ending in `createdStatus: failed`).
        // Use real photos hosted on a public CDN so Bitnob's verifier
        // can both fetch them AND classify them as valid KYC content.
        // `picsum.photos` is the de-facto Lorem-Ipsum-for-images service —
        // it always returns real JPGs and the seeded URLs are stable.
        $demoUserPhoto  = 'https://picsum.photos/seed/digikash-face/640/640.jpg';
        $demoIdImage    = 'https://picsum.photos/seed/digikash-idcard/1024/640.jpg';
        $demoIdDocument = $demoIdImage;

        $bitnobProfiles = [
            // Demo customer Ayesha → Antananarivo resident, CIN holder.
            'ayesha.rahman@digikash.test' => [
                'title'              => 'Ms',
                'first_name'         => 'Ayesha',
                'middle_name'        => null,
                'last_name'          => 'Rahman',
                'email'              => 'ayesha.rahman.mg@digikash.test',
                'mobile'             => '321234567',
                'phone_country_code' => '+261',
                'gender'             => Gender::Female->value,
                'dob'                => '1992-04-18',
                'nationality'        => 'MG',
                'place_of_birth'     => 'Antananarivo, Madagascar',
                'relation'           => 'Self',

                'address_line1' => 'Lot II M 53 Bis, Antanimena',
                'address_line2' => 'Quartier Isoraka',
                'city'          => 'Antananarivo',
                'state'         => 'Analamanga',
                'postal_code'   => '101',
                'country'       => 'MG',

                'id_type'          => 'national_id',
                'id_number'        => '101011234567',
                'id_issue_country' => 'MG',
                'id_issue_date'    => '2022-01-15',
                'id_expiry'        => '2032-01-14',

                'tax_id'      => 'NIF-DEMO-MG-0101',
                'tax_country' => 'MG',

                'occupation'      => 'Product Designer',
                'employer'        => 'DigiKash Sandbox Co.',
                'annual_income'   => 95000.00,
                'source_of_funds' => 'salary',

                'pep_flag'       => false,
                'sanctions_flag' => false,

                'kyc_documents' => [
                    'id_document' => $demoIdImage,
                    'idImage'     => $demoIdImage,
                    'userPhoto'   => $demoUserPhoto,
                    'idType'      => 'NATIONAL_ID',
                    'idNumber'    => '101011234567',
                ],
            ],

            // Demo customer Imran → Toamasina resident, Passport holder.
            'imran.hossain@digikash.test' => [
                'title'              => 'Mr',
                'first_name'         => 'Imran',
                'middle_name'        => null,
                'last_name'          => 'Hossain',
                'email'              => 'imran.hossain.mg@digikash.test',
                'mobile'             => '331234568',
                'phone_country_code' => '+261',
                'gender'             => Gender::Male->value,
                'dob'                => '1989-11-03',
                'nationality'        => 'MG',
                'place_of_birth'     => 'Toamasina, Madagascar',
                'relation'           => 'Self',

                'address_line1' => '24 Boulevard Joffre',
                'address_line2' => 'Tanambao V',
                'city'          => 'Toamasina',
                'state'         => 'Atsinanana',
                'postal_code'   => '501',
                'country'       => 'MG',

                'id_type'          => 'passport',
                'id_number'        => 'MG-PASS-098765',
                'id_issue_country' => 'MG',
                'id_issue_date'    => '2021-06-01',
                'id_expiry'        => '2031-05-31',

                'tax_id'      => 'NIF-DEMO-MG-0202',
                'tax_country' => 'MG',

                'occupation'      => 'Operations Manager',
                'employer'        => 'DigiKash Sandbox Co.',
                'annual_income'   => 110000.00,
                'source_of_funds' => 'salary',

                'pep_flag'       => false,
                'sanctions_flag' => false,

                'kyc_documents' => [
                    'id_document' => $demoIdImage,
                    'idImage'     => $demoIdImage,
                    'userPhoto'   => $demoUserPhoto,
                    'idType'      => 'PASSPORT',
                    'idNumber'    => 'MG-PASS-098765',
                ],
            ],

            // Demo merchant Nusrat → Mahajanga, Driver's License.
            'nusrat.farhana@digikash.test' => [
                'title'              => 'Ms',
                'first_name'         => 'Nusrat',
                'middle_name'        => null,
                'last_name'          => 'Farhana',
                'email'              => 'nusrat.farhana.mg@digikash.test',
                'mobile'             => '341234569',
                'phone_country_code' => '+261',
                'gender'             => Gender::Female->value,
                'dob'                => '1985-08-22',
                'nationality'        => 'MG',
                'place_of_birth'     => 'Mahajanga, Madagascar',
                'relation'           => 'Owner',

                'address_line1' => '12 Rue Marius Jutheau',
                'address_line2' => 'Mahabibo',
                'city'          => 'Mahajanga',
                'state'         => 'Boeny',
                'postal_code'   => '401',
                'country'       => 'MG',

                'id_type'          => 'drivers_license',
                'id_number'        => 'MG-DL-2018-2001',
                'id_issue_country' => 'MG',
                'id_issue_date'    => '2018-03-10',
                'id_expiry'        => '2028-03-09',

                'tax_id'      => 'NIF-DEMO-MG-2001',
                'tax_country' => 'MG',

                'occupation'      => 'Retail Owner',
                'employer'        => 'Nusrat Mart',
                'annual_income'   => 145000.00,
                'source_of_funds' => 'business',

                'pep_flag'       => false,
                'sanctions_flag' => false,

                'kyc_documents' => [
                    'id_document' => $demoIdImage,
                    'idImage'     => $demoIdImage,
                    'userPhoto'   => $demoUserPhoto,
                    'idType'      => 'DRIVERS_LICENSE',
                    'idNumber'    => 'MG-DL-2018-2001',
                ],
            ],

            // Demo merchant Tanvir → Fianarantsoa, CIN holder.
            'tanvir.ahmed@digikash.test' => [
                'title'              => 'Mr',
                'first_name'         => 'Tanvir',
                'middle_name'        => null,
                'last_name'          => 'Ahmed',
                'email'              => 'tanvir.ahmed.mg@digikash.test',
                'mobile'             => '381234570',
                'phone_country_code' => '+261',
                'gender'             => Gender::Male->value,
                'dob'                => '1990-02-14',
                'nationality'        => 'MG',
                'place_of_birth'     => 'Fianarantsoa, Madagascar',
                'relation'           => 'Owner',

                'address_line1' => '8 Avenue de l\'Indépendance',
                'address_line2' => 'Tsianolondroa',
                'city'          => 'Fianarantsoa',
                'state'         => 'Haute Matsiatra',
                'postal_code'   => '301',
                'country'       => 'MG',

                'id_type'          => 'national_id',
                'id_number'        => '301021234570',
                'id_issue_country' => 'MG',
                'id_issue_date'    => '2020-09-01',
                'id_expiry'        => '2030-08-31',

                'tax_id'      => 'NIF-DEMO-MG-2002',
                'tax_country' => 'MG',

                'occupation'      => 'Electronics Retailer',
                'employer'        => 'Tanvir Tech Care',
                'annual_income'   => 90000.00,
                'source_of_funds' => 'business',

                'pep_flag'       => false,
                'sanctions_flag' => false,

                'kyc_documents' => [
                    'id_document' => $demoIdImage,
                    'idImage'     => $demoIdImage,
                    'userPhoto'   => $demoUserPhoto,
                    'idType'      => 'NATIONAL_ID',
                    'idNumber'    => '301021234570',
                ],
            ],
        ];

        $created = 0;
        foreach ($bitnobProfiles as $userEmail => $profile) {
            $user = User::query()->where('email', $userEmail)->first();
            if (! $user) {
                $this->command?->warn("DemoBitnobCardholderSeeder: user {$userEmail} not found, skipping.");

                continue;
            }

            $payload = array_merge($profile, [
                'user_id'    => $user->id,
                'card_type'  => CardholderType::PERSONAL->value,
                'kyc_status' => KycStatus::APPROVED->value,
                'status'     => CardholderStatus::APPROVED->value,
                'note'       => 'Seeded demo Madagascar (MG) cardholder — for Bitnob sandbox issuance.',
            ]);

            Cardholders::query()->updateOrCreate(
                [
                    'user_id'   => $user->id,
                    'email'     => $profile['email'],
                    'card_type' => CardholderType::PERSONAL->value,
                ],
                $payload
            );

            $created++;
        }

        $this->command?->info("DemoBitnobCardholderSeeder: seeded {$created} Madagascar-based cardholders for Bitnob testing.");
    }
}
