<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $userPhoto = 'https://picsum.photos/seed/digikash-face/640/640.jpg';
        $idImage   = 'https://picsum.photos/seed/digikash-idcard/1024/640.jpg';

        $profiles = [
            ['emails' => ['aarav.karim.mg@digikash.test', 'aarav.karim.mg.v2@digikash.test'], 'idType' => 'NATIONAL_ID', 'idNumber' => '101011234567'],
            ['emails' => ['sarah.johnson.mg@digikash.test', 'sarah.johnson.mg.v2@digikash.test'], 'idType' => 'PASSPORT', 'idNumber' => 'MG-PASS-098765'],
            ['emails' => ['rahim.hossain.mg@digikash.test', 'rahim.hossain.mg.v2@digikash.test'], 'idType' => 'DRIVERS_LICENSE', 'idNumber' => 'MG-DL-2018-2001'],
            ['emails' => ['fatema.akter.mg@digikash.test', 'fatema.akter.mg.v2@digikash.test'], 'idType' => 'NATIONAL_ID', 'idNumber' => '301021234570'],
        ];

        foreach ($profiles as $profile) {
            DB::table('cardholders')
                ->whereIn('email', $profile['emails'])
                ->update([
                    'kyc_documents' => json_encode([
                        'id_document' => $idImage,
                        'idImage'     => $idImage,
                        'userPhoto'   => $userPhoto,
                        'idType'      => $profile['idType'],
                        'idNumber'    => $profile['idNumber'],
                    ]),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not restore placeholder SVG KYC documents.
    }
};
