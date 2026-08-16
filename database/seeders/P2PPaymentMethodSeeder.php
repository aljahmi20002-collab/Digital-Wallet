<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\P2P\PaymentMethod;
use Illuminate\Database\Seeder;

class P2PPaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->allMethods() as $method) {
            $record = PaymentMethod::query()
                ->withTrashed()
                ->firstOrNew([
                    'name'    => $method['name'],
                    'country' => $method['country'],
                ]);

            $record->fill([
                'logo'         => $method['logo'],
                'instructions' => $method['instructions'],
                'fields'       => $method['fields'],
                'sort_order'   => $method['sort_order'],
                'status'       => $method['status'],
            ]);

            if (method_exists($record, 'trashed') && $record->trashed()) {
                $record->restore();
            }

            $record->save();
        }
    }

    private function allMethods(): array
    {
        return [
            ...$this->bangladeshMethods(),
            ...$this->internationalMethods(),
        ];
    }

    private function bangladeshMethods(): array
    {
        return [
            [
                'name'         => 'bKash',
                'country'      => 'BD',
                'logo'         => $this->logoPath('bkash.svg'),
                'sort_order'   => 10,
                'status'       => true,
                'instructions' => 'Use your own verified bKash wallet. Confirm the sender number, amount, and reference before marking a trade as paid.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('bkash_number', 'bKash Number', sortOrder: 2),
                    $this->field('account_type', 'Account Type', 'select', true, ['Personal', 'Agent', 'Merchant'], 3),
                    $this->field('default_reference', 'Default Reference / Note', 'text', false, [], 4),
                ],
            ],
            [
                'name'         => 'Nagad',
                'country'      => 'BD',
                'logo'         => $this->logoPath('nagad.svg'),
                'sort_order'   => 20,
                'status'       => true,
                'instructions' => 'Save the Nagad number that is registered in your own name. Recheck the cash out or transfer reference before releasing crypto.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('nagad_number', 'Nagad Number', sortOrder: 2),
                    $this->field('account_type', 'Account Type', 'select', true, ['Personal', 'Merchant'], 3),
                    $this->field('district', 'District', 'text', false, [], 4),
                ],
            ],
            [
                'name'         => 'Rocket',
                'country'      => 'BD',
                'logo'         => $this->logoPath('rocket.svg'),
                'sort_order'   => 30,
                'status'       => true,
                'instructions' => 'Use the Rocket wallet linked to your Dutch-Bangla Bank profile. The account owner name must match your verified account information.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('rocket_number', 'Rocket Number', sortOrder: 2),
                    $this->field('account_type', 'Account Type', 'select', true, ['Personal', 'Agent'], 3),
                    $this->field('linked_branch', 'Linked Branch (Optional)', 'text', false, [], 4),
                ],
            ],
            [
                'name'         => 'Upay',
                'country'      => 'BD',
                'logo'         => $this->logoPath('upay.svg'),
                'sort_order'   => 40,
                'status'       => true,
                'instructions' => 'Save your Upay wallet details exactly as registered. Use the note field if you need to instruct traders to mention a specific reference.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('upay_number', 'Upay Number', sortOrder: 2),
                    $this->field('account_type', 'Account Type', 'select', true, ['Personal', 'Merchant'], 3),
                    $this->field('payment_note', 'Payment Note', 'textarea', false, [], 4),
                ],
            ],
            [
                'name'         => 'SureCash',
                'country'      => 'BD',
                'logo'         => $this->logoPath('surecash.svg'),
                'sort_order'   => 50,
                'status'       => true,
                'instructions' => 'Use only a SureCash wallet that belongs to you. Confirm the registered mobile number and exact transfer amount before releasing funds.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('surecash_number', 'SureCash Number', sortOrder: 2),
                    $this->field('institution_name', 'Institution / Agent Name', 'text', false, [], 3),
                    $this->field('default_reference', 'Default Reference', 'text', false, [], 4),
                ],
            ],
            [
                'name'         => 'CellFin',
                'country'      => 'BD',
                'logo'         => $this->logoPath('cellfin.svg'),
                'sort_order'   => 60,
                'status'       => true,
                'instructions' => 'Use your verified CellFin-enabled Islami Bank account. Share clear receiving details for mobile transfer or bank transfer as needed.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('registered_mobile', 'Registered Mobile Number', sortOrder: 2),
                    $this->field('bank_name', 'Bank Name', 'text', true, [], 3),
                    $this->field('account_number', 'Account Number', 'text', false, [], 4),
                ],
            ],
            [
                'name'         => 'Bangladesh Bank Transfer',
                'country'      => 'BD',
                'logo'         => $this->logoPath('bangladesh-bank-transfer.svg'),
                'sort_order'   => 70,
                'status'       => true,
                'instructions' => 'Only Bangladesh bank accounts in your own name are supported. Share the exact bank, branch, and account details needed for EFT, NPSB, BEFTN, or regular transfer.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('bank_name', 'Bank Name', 'select', true, ['DBBL', 'BRAC Bank', 'Islami Bank', 'City Bank', 'Eastern Bank', 'Sonali Bank', 'IFIC Bank', 'Prime Bank', 'Southeast Bank', 'Other'], 2),
                    $this->field('account_number', 'Account Number', 'text', true, [], 3),
                    $this->field('branch_name', 'Branch Name', 'text', true, [], 4),
                    $this->field('routing_number', 'Routing Number', 'text', false, [], 5),
                    $this->field('transfer_type', 'Preferred Transfer Type', 'select', false, ['BEFTN', 'NPSB', 'RTGS', 'Regular Transfer'], 6),
                ],
            ],
            [
                'name'         => 'Bangladesh Card to Card',
                'country'      => 'BD',
                'logo'         => $this->logoPath('bangladesh-card-to-card.svg'),
                'sort_order'   => 80,
                'status'       => true,
                'instructions' => 'Use only your own Bangladesh-issued debit or credit card details for supported card-to-card settlement flows.',
                'fields'       => [
                    $this->field('card_holder_name', 'Card Holder Name', sortOrder: 1),
                    $this->field('bank_name', 'Issuing Bank', 'text', true, [], 2),
                    $this->field('card_last_four', 'Card Last 4 Digits', 'text', true, [], 3),
                    $this->field('registered_mobile', 'Registered Mobile Number', 'text', false, [], 4),
                ],
            ],
        ];
    }

    private function internationalMethods(): array
    {
        return [
            [
                'name'         => 'Wise Account',
                'country'      => null,
                'logo'         => $this->logoPath('wise-account.svg'),
                'sort_order'   => 110,
                'status'       => true,
                'instructions' => 'Ensure your Wise profile is verified. Use only an account that belongs to you and matches your KYC information.',
                'fields'       => [
                    $this->field('profile_name', 'Profile Name', sortOrder: 1),
                    $this->field('wise_email', 'Wise Email', sortOrder: 2),
                    $this->field('wise_currency', 'Preferred Currency', 'select', true, ['USD', 'EUR', 'GBP', 'AUD', 'SGD', 'BDT'], 3),
                    $this->field('wise_profile_id', 'Wise Profile ID', 'text', false, [], 4),
                ],
            ],
            [
                'name'         => 'Payoneer',
                'country'      => null,
                'logo'         => $this->logoPath('payoneer.svg'),
                'sort_order'   => 120,
                'status'       => true,
                'instructions' => 'We transfer only to verified Payoneer accounts. Make sure the registered name matches your DigiKash profile.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('payoneer_customer_id', 'Payoneer Customer ID', sortOrder: 2),
                    $this->field('payoneer_email', 'Payoneer Email', sortOrder: 3),
                ],
            ],
            [
                'name'         => 'PayPal',
                'country'      => null,
                'logo'         => $this->logoPath('paypal.svg'),
                'sort_order'   => 130,
                'status'       => true,
                'instructions' => 'Use only a PayPal account owned by you. Double-check the PayPal email and account type before sharing it with traders.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('paypal_email', 'PayPal Email', sortOrder: 2),
                    $this->field('paypal_type', 'PayPal Account Type', 'select', false, ['Personal', 'Business'], 3),
                ],
            ],
            [
                'name'         => 'SEPA Bank Transfer',
                'country'      => null,
                'logo'         => $this->logoPath('sepa-bank-transfer.svg'),
                'sort_order'   => 140,
                'status'       => true,
                'instructions' => 'Send or receive payments only from a bank account that matches your verified name. Include the provided reference when required.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('iban', 'IBAN', sortOrder: 2),
                    $this->field('bank_name', 'Bank Name', sortOrder: 3),
                    $this->field('swift_bic', 'SWIFT / BIC', 'text', false, [], 4),
                ],
            ],
            [
                'name'         => 'US Domestic Wire',
                'country'      => 'US',
                'logo'         => $this->logoPath('us-domestic-wire.svg'),
                'sort_order'   => 150,
                'status'       => true,
                'instructions' => 'Only ACH or domestic wire transfers from accounts in your own name are accepted.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('account_number', 'Account Number', sortOrder: 2),
                    $this->field('routing_number', 'Routing Number (ABA)', sortOrder: 3),
                    $this->field('bank_name', 'Bank Name', sortOrder: 4),
                ],
            ],
            [
                'name'         => 'UPI Transfer',
                'country'      => 'IN',
                'logo'         => $this->logoPath('upi-transfer.svg'),
                'sort_order'   => 160,
                'status'       => true,
                'instructions' => 'Use only UPI IDs that belong to you. Confirm the transaction in your UPI app before marking the trade as paid.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('upi_id', 'UPI ID (VPA)', sortOrder: 2),
                    $this->field('upi_app', 'Preferred UPI App', 'select', false, ['PhonePe', 'Google Pay', 'Paytm', 'BHIM', 'Other'], 3),
                ],
            ],
            [
                'name'         => 'PIX Instant Transfer',
                'country'      => 'BR',
                'logo'         => $this->logoPath('pix-instant-transfer.svg'),
                'sort_order'   => 170,
                'status'       => true,
                'instructions' => 'Provide the PIX key that matches your verified CPF or CNPJ. PIX transfers usually settle instantly.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('pix_key_type', 'PIX Key Type', 'select', true, ['CPF', 'CNPJ', 'EMAIL', 'PHONE', 'EVP'], 2),
                    $this->field('pix_key_value', 'PIX Key Value', sortOrder: 3),
                ],
            ],
            [
                'name'         => 'GCash',
                'country'      => 'PH',
                'logo'         => $this->logoPath('gcash.svg'),
                'sort_order'   => 180,
                'status'       => true,
                'instructions' => 'GCash number must match your verified mobile number. Confirm successful receipt before releasing funds.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('gcash_number', 'GCash Mobile Number', sortOrder: 2),
                    $this->field('gcash_email', 'GCash Email', 'text', false, [], 3),
                ],
            ],
            [
                'name'         => 'M-Pesa Kenya',
                'country'      => 'KE',
                'logo'         => $this->logoPath('mpesa-kenya.svg'),
                'sort_order'   => 190,
                'status'       => true,
                'instructions' => 'We only support Safaricom M-Pesa wallets. Use the phone number that is registered in your own name.',
                'fields'       => [
                    $this->field('account_holder_name', 'Account Holder Name', sortOrder: 1),
                    $this->field('mpesa_phone', 'M-Pesa Phone Number', sortOrder: 2),
                    $this->field('id_number', 'National ID (Optional)', 'text', false, [], 3),
                ],
            ],
        ];
    }

    private function field(
        string $key,
        string $label,
        string $type = 'text',
        bool $required = true,
        array $options = [],
        int $sortOrder = 1
    ): array {
        return [
            'key'        => $key,
            'label'      => $label,
            'type'       => $type,
            'required'   => $required,
            'options'    => $type === 'select' ? array_values($options) : [],
            'sort_order' => $sortOrder,
        ];
    }

    private function logoPath(string $fileName): string
    {
        return 'images/p2p/payment-methods/'.ltrim($fileName, '/');
    }
}
