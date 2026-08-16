<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AgentStatus;
use App\Enums\AmountFlow;
use App\Enums\KycStatus;
use App\Enums\MerchantStatus;
use App\Enums\MethodType;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Agent;
use App\Models\Currency;
use App\Models\KycSubmission;
use App\Models\KycTemplate;
use App\Models\LoginActivity;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    private const PASSWORD = '12345678';

    public function run(): void
    {
        $defaultCurrency = Currency::query()->where('default', true)->first() ?? Currency::query()->first();

        if (!$defaultCurrency) {
            $this->command?->warn('DemoAccountSeeder skipped because no currency record exists.');

            return;
        }

        $kycTemplate = KycTemplate::query()->active()->first() ?? KycTemplate::query()->first();

        foreach ($this->customers($defaultCurrency) as $account) {
            $user = $this->upsertUser($account['profile'], $account['legacy_emails']);

            $this->syncWalletBalances($user, $account['wallets']);
            $this->syncTransactions($user, $account['transactions']);
            $this->syncLoginActivity($user, $account['login_activity']);
            $this->syncKycSubmission($user, $kycTemplate, $account['kyc_status'], $account['kyc_notes']);
        }

        foreach ($this->merchants($defaultCurrency) as $account) {
            $user = $this->upsertUser($account['profile'], $account['legacy_emails']);

            $merchant = Merchant::query()->updateOrCreate(
                ['user_id' => $user->id],
                $account['merchant']
            );

            $merchant->forceFill([
                'status' => $account['merchant']['status'],
            ])->save();

            $this->syncWalletBalances($user, $account['wallets']);
            $this->syncTransactions($user, $account['transactions']);
            $this->syncLoginActivity($user, $account['login_activity']);
            $this->syncKycSubmission($user, $kycTemplate, $account['kyc_status'], $account['kyc_notes']);
        }

        foreach ($this->agents($defaultCurrency) as $account) {
            $user = $this->upsertUser($account['profile'], $account['legacy_emails']);

            $agent = Agent::query()->updateOrCreate(
                ['user_id' => $user->id],
                $account['agent']
            );

            $this->syncAgentCurrencies($agent, $account['currency_ids']);
            $this->syncWalletBalances($user, $account['wallets']);
            $this->syncTransactions($user, $account['transactions']);
            $this->syncLoginActivity($user, $account['login_activity']);
            $this->syncKycSubmission($user, $kycTemplate, $account['kyc_status'], $account['kyc_notes']);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function customers(Currency $defaultCurrency): array
    {
        return [
            [
                'legacy_emails' => ['demo.user1@digikash.test'],
                'profile' => $this->profile([
                    'first_name' => 'Ayesha',
                    'last_name' => 'Rahman',
                    'username' => 'demo_customer_ayesha',
                    'email' => 'ayesha.rahman@digikash.test',
                    'phone' => '+8801711000101',
                    'country' => 'BD',
                    'state' => 'Dhaka',
                    'city' => 'Dhaka',
                    'postal_code' => '1212',
                    'address' => 'House 42, Road 7, Banani',
                    'role' => UserRole::USER,
                ]),
                'wallets' => [
                    $defaultCurrency->code => 18500.00,
                ],
                'transactions' => [
                    $this->transaction('demo-ayesha-wallet-topup', TrxType::DEPOSIT, 'Demo wallet top-up from bank transfer.', 10000.00, 25.00, AmountFlow::PLUS, TrxStatus::COMPLETED, 6),
                    $this->transaction('demo-ayesha-send-money', TrxType::SEND_MONEY, 'Demo send money to family contact.', 2750.00, 12.50, AmountFlow::MINUS, TrxStatus::COMPLETED, 2),
                ],
                'login_activity' => $this->loginActivity('203.0.113.21', 'Bangladesh', 'Mobile', 'Chrome Mobile', 'Android', 4),
                'kyc_status' => KycStatus::APPROVED,
                'kyc_notes' => 'Demo customer KYC approved for everyday wallet testing.',
            ],
            [
                'legacy_emails' => ['demo.user2@digikash.test'],
                'profile' => $this->profile([
                    'first_name' => 'Imran',
                    'last_name' => 'Hossain',
                    'username' => 'demo_customer_imran',
                    'email' => 'imran.hossain@digikash.test',
                    'phone' => '+8801811000202',
                    'country' => 'BD',
                    'state' => 'Chattogram',
                    'city' => 'Chattogram',
                    'postal_code' => '4000',
                    'address' => 'Jamal Khan Road, Chattogram',
                    'role' => UserRole::USER,
                ]),
                'wallets' => [
                    $defaultCurrency->code => 9200.00,
                ],
                'transactions' => [
                    $this->transaction('demo-imran-request-money', TrxType::REQUEST_MONEY, 'Demo pending request money from a customer.', 1500.00, 0.00, AmountFlow::DEFAULT, TrxStatus::PENDING, 1),
                ],
                'login_activity' => $this->loginActivity('203.0.113.22', 'Bangladesh', 'Laptop', 'Firefox', 'Windows', 11),
                'kyc_status' => KycStatus::PENDING,
                'kyc_notes' => 'Demo customer KYC pending for review queue testing.',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function profile(array $overrides): array
    {
        return array_merge([
            'rank_id' => 1,
            'phone_verified_at' => now(),
            'phone_verification_enabled' => true,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
            'password' => Hash::make(self::PASSWORD),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function transaction(
        string     $reference,
        TrxType    $type,
        string     $description,
        float      $amount,
        float      $fee,
        AmountFlow $amountFlow,
        TrxStatus  $status,
        int        $daysAgo
    ): array
    {
        return [
            'trx_reference' => $reference,
            'trx_type' => $type,
            'description' => $description,
            'amount' => $amount,
            'fee' => $fee,
            'amount_flow' => $amountFlow,
            'status' => $status,
            'remarks' => 'Seeded demo transaction for local testing.',
            'created_at' => Carbon::now()->subDays($daysAgo),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loginActivity(string $ipAddress, string $country, string $device, string $browser, string $platform, int $hoursAgo): array
    {
        return [
            'ip_address' => $ipAddress,
            'country' => $country,
            'device' => $device,
            'browser' => $browser,
            'platform' => $platform,
            'user_agent' => "{$browser} demo session on {$platform}",
            'login_at' => Carbon::now()->subHours($hoursAgo),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<int, string> $legacyEmails
     */
    private function upsertUser(array $attributes, array $legacyEmails = []): User
    {
        $user = User::query()->where('email', $attributes['email'])->first();

        if (!$user && $legacyEmails !== []) {
            $user = User::query()->whereIn('email', $legacyEmails)->first();
        }

        if (!$user) {
            return User::query()->create($attributes);
        }

        $user->fill($attributes)->save();

        return $user;
    }

    /**
     * @param array<string, float> $balances
     */
    private function syncWalletBalances(User $user, array $balances): void
    {
        foreach ($balances as $currencyCode => $balance) {
            $currency = Currency::query()->where('code', $currencyCode)->first();

            if (!$currency) {
                continue;
            }

            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->where('currency_id', $currency->id)
                ->first();

            if (!$wallet) {
                app(WalletService::class)->createWalletForCurrency($user, (int)$currency->id);

                $wallet = Wallet::query()
                    ->where('user_id', $user->id)
                    ->where('currency_id', $currency->id)
                    ->first();
            }

            $wallet?->update([
                'balance' => $balance,
                'status' => true,
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $transactions
     */
    private function syncTransactions(User $user, array $transactions): void
    {
        $defaultWallet = $user->wallets()->first();
        $walletReference = $defaultWallet?->uuid;
        $currencyCode = $defaultWallet?->currency?->code ?? 'USD';

        foreach ($transactions as $transaction) {
            $amount = (float)$transaction['amount'];
            $fee = (float)$transaction['fee'];
            $netAmount = $transaction['amount_flow'] === AmountFlow::MINUS
                ? max($amount - $fee, 0)
                : $amount - $fee;

            Transaction::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'trx_reference' => $transaction['trx_reference'],
                ],
                [
                    'trx_type' => $transaction['trx_type'],
                    'description' => $transaction['description'],
                    'provider' => 'system',
                    'processing_type' => MethodType::SYSTEM,
                    'amount' => $amount,
                    'amount_flow' => $transaction['amount_flow'],
                    'fee' => $fee,
                    'currency' => $currencyCode,
                    'net_amount' => $netAmount,
                    'payable_amount' => $amount,
                    'payable_currency' => $currencyCode,
                    'wallet_reference' => $walletReference,
                    'trx_data' => [
                        'seeded' => true,
                        'user_email' => $user->email,
                    ],
                    'remarks' => $transaction['remarks'],
                    'status' => $transaction['status'],
                    'created_at' => $transaction['created_at'],
                    'updated_at' => $transaction['created_at'],
                ]
            );
        }
    }

    /**
     * @param array<string, mixed> $activity
     */
    private function syncLoginActivity(User $user, array $activity): void
    {
        LoginActivity::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'ip_address' => $activity['ip_address'],
            ],
            array_merge($activity, ['user_id' => $user->id])
        );
    }

    private function syncKycSubmission(User $user, ?KycTemplate $kycTemplate, KycStatus $status, string $notes): void
    {
        if (!$kycTemplate) {
            return;
        }

        KycSubmission::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'kyc_template_id' => $kycTemplate->id,
                'submission_data' => [
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'address' => $user->address,
                    'seeded' => true,
                ],
                'status' => $status,
                'notes' => $notes,
            ]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function merchants(Currency $defaultCurrency): array
    {
        return [
            [
                'legacy_emails' => ['demo.merchant1@digikash.test'],
                'profile' => $this->profile([
                    'first_name' => 'Nusrat',
                    'last_name' => 'Farhana',
                    'business_name' => 'Nusrat Mart',
                    'business_address' => 'Level 3, Police Plaza Concord, Gulshan 1',
                    'username' => 'demo_merchant_nusrat_mart',
                    'email' => 'nusrat.farhana@digikash.test',
                    'phone' => '+8801911000303',
                    'country' => 'BD',
                    'state' => 'Dhaka',
                    'city' => 'Dhaka',
                    'postal_code' => '1212',
                    'address' => 'Gulshan 1, Dhaka',
                    'role' => UserRole::MERCHANT,
                ]),
                'merchant' => [
                    'business_name' => 'Nusrat Mart',
                    'site_url' => 'https://nusrat-mart.digikash.test',
                    'business_email' => 'payments@nusrat-mart.digikash.test',
                    'business_description' => 'Neighborhood grocery and essentials shop for checkout, webhook, and settlement demos.',
                    'currency_id' => $defaultCurrency->id,
                    'fee' => 1.25,
                    'status' => MerchantStatus::APPROVED,
                    'webhook_url' => 'https://nusrat-mart.digikash.test/webhooks/digikash',
                ],
                'wallets' => [
                    $defaultCurrency->code => 126500.00,
                ],
                'transactions' => [
                    $this->transaction('demo-nusrat-mart-payment-received', TrxType::RECEIVE_PAYMENT, 'Demo merchant checkout payment received.', 18500.00, 231.25, AmountFlow::PLUS, TrxStatus::COMPLETED, 4),
                    $this->transaction('demo-nusrat-mart-withdrawal', TrxType::WITHDRAW, 'Demo merchant settlement withdrawal request.', 25000.00, 75.00, AmountFlow::MINUS, TrxStatus::PENDING, 1),
                ],
                'login_activity' => $this->loginActivity('203.0.113.31', 'Bangladesh', 'Desktop', 'Edge', 'Windows', 3),
                'kyc_status' => KycStatus::APPROVED,
                'kyc_notes' => 'Demo merchant KYB approved for live merchant dashboard testing.',
            ],
            [
                'legacy_emails' => ['demo.merchant2@digikash.test'],
                'profile' => $this->profile([
                    'first_name' => 'Tanvir',
                    'last_name' => 'Ahmed',
                    'business_name' => 'Tanvir Tech Care',
                    'business_address' => 'Shop 18, Eastern Plaza, Hatirpool',
                    'username' => 'demo_merchant_tanvir_tech',
                    'email' => 'tanvir.ahmed@digikash.test',
                    'phone' => '+8801611000404',
                    'country' => 'BD',
                    'state' => 'Dhaka',
                    'city' => 'Dhaka',
                    'postal_code' => '1205',
                    'address' => 'Hatirpool, Dhaka',
                    'role' => UserRole::MERCHANT,
                ]),
                'merchant' => [
                    'business_name' => 'Tanvir Tech Care',
                    'site_url' => 'https://tanvir-tech-care.digikash.test',
                    'business_email' => 'billing@tanvir-tech-care.digikash.test',
                    'business_description' => 'Mobile accessories and repair shop used for pending merchant review demos.',
                    'currency_id' => $defaultCurrency->id,
                    'fee' => 1.75,
                    'status' => MerchantStatus::PENDING,
                    'webhook_url' => 'https://tanvir-tech-care.digikash.test/webhooks/digikash',
                ],
                'wallets' => [
                    $defaultCurrency->code => 48750.00,
                ],
                'transactions' => [
                    $this->transaction('demo-tanvir-tech-payment', TrxType::PAYMENT, 'Demo merchant outgoing supplier payment.', 8200.00, 45.00, AmountFlow::MINUS, TrxStatus::COMPLETED, 3),
                ],
                'login_activity' => $this->loginActivity('203.0.113.32', 'Bangladesh', 'Mobile', 'Mobile Safari', 'iOS', 9),
                'kyc_status' => KycStatus::PENDING,
                'kyc_notes' => 'Demo merchant KYB pending for admin approval screen testing.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function agents(Currency $defaultCurrency): array
    {
        return [
            [
                'legacy_emails' => [],
                'profile' => $this->profile([
                    'first_name' => 'Farid',
                    'last_name' => 'Uddin',
                    'username' => 'demo_agent_farid_uttara',
                    'email' => 'farid.uddin@digikash.test',
                    'phone' => '+8801511000505',
                    'country' => 'BD',
                    'state' => 'Dhaka',
                    'city' => 'Dhaka',
                    'postal_code' => '1230',
                    'address' => 'House 12, Sector 7, Uttara',
                    'role' => UserRole::AGENT,
                ]),
                'agent' => [
                    'agent_code' => 'DK-UTTARA-001',
                    'currency_id' => $defaultCurrency->id,
                    'agent_name' => 'Farid Uttara Cash Point',
                    'description' => 'Approved demo counter for cash-in, QR cash-out, and assisted customer service flows.',
                    'commission' => 0.45,
                    'status' => AgentStatus::APPROVED,
                ],
                'currency_ids' => [$defaultCurrency->id],
                'wallets' => [
                    $defaultCurrency->code => 73500.00,
                ],
                'transactions' => [
                    $this->transaction('demo-farid-agent-cash-in', TrxType::AGENT_CASH_IN, 'Demo cash-in handled at Uttara counter.', 6500.00, 0.00, AmountFlow::PLUS, TrxStatus::COMPLETED, 2),
                    $this->transaction('demo-farid-agent-commission', TrxType::AGENT_COMMISSION, 'Demo commission earned from assisted transactions.', 29.25, 0.00, AmountFlow::PLUS, TrxStatus::COMPLETED, 1),
                ],
                'login_activity' => $this->loginActivity('203.0.113.41', 'Bangladesh', 'Android POS', 'Chrome Mobile', 'Android', 2),
                'kyc_status' => KycStatus::APPROVED,
                'kyc_notes' => 'Demo agent KYC approved for counter operation testing.',
            ],
            [
                'legacy_emails' => [],
                'profile' => $this->profile([
                    'first_name' => 'Sabila',
                    'last_name' => 'Akter',
                    'username' => 'demo_agent_sabila_sylhet',
                    'email' => 'sabila.akter@digikash.test',
                    'phone' => '+8801311000606',
                    'country' => 'BD',
                    'state' => 'Sylhet',
                    'city' => 'Sylhet',
                    'postal_code' => '3100',
                    'address' => 'Zindabazar, Sylhet',
                    'role' => UserRole::AGENT,
                ]),
                'agent' => [
                    'agent_code' => 'DK-SYLHET-001',
                    'currency_id' => $defaultCurrency->id,
                    'agent_name' => 'Sabila Sylhet Service Desk',
                    'description' => 'Pending demo counter for admin review and approval workflow testing.',
                    'commission' => 0.35,
                    'status' => AgentStatus::PENDING,
                ],
                'currency_ids' => [$defaultCurrency->id],
                'wallets' => [
                    $defaultCurrency->code => 28500.00,
                ],
                'transactions' => [
                    $this->transaction('demo-sabila-agent-cash-out', TrxType::AGENT_CASH_OUT, 'Demo pending cash-out queue item.', 4200.00, 0.00, AmountFlow::MINUS, TrxStatus::PENDING, 1),
                ],
                'login_activity' => $this->loginActivity('203.0.113.42', 'Bangladesh', 'Mobile', 'Chrome Mobile', 'Android', 8),
                'kyc_status' => KycStatus::PENDING,
                'kyc_notes' => 'Demo agent KYC pending for agent review workflow testing.',
            ],
        ];
    }

    /**
     * @param array<int, int> $currencyIds
     */
    private function syncAgentCurrencies(Agent $agent, array $currencyIds): void
    {
        $syncPayload = [];

        foreach (array_values(array_unique($currencyIds)) as $index => $currencyId) {
            $syncPayload[$currencyId] = [
                'is_primary' => $index === 0,
            ];
        }

        $agent->supportedCurrencies()->sync($syncPayload);
    }
}
