<?php

namespace App\Services\Bitnob;

use App\Enums\TrxStatus;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Handlers\DepositHandler;

/**
 * Bitnob deposit / on-ramp service (stablecoin pattern).
 *
 * Bitnob's stablecoin deposit flow:
 *  1. The merchant calls a "generate address" endpoint with `customer_email`,
 *     `currency` (e.g. USDT), `chain` (e.g. tron, bsc, eth) and an idempotent
 *     `reference`.
 *  2. The customer sends crypto to the returned address.
 *  3. Bitnob fires a `stablecoin.deposit.success` webhook with the same
 *     `reference`, `amount`, `chain`, `currency`, `address`, `hash`.
 *  4. The webhook handler (BitnobWebhookController) credits the matching
 *     pending Transaction.
 *
 * The exact endpoint path is exposed via `config('bitnob.endpoints.deposit_address')`
 * so an environment-specific override is one env var away.
 */
class BitnobDepositService
{
    public function __construct(protected BitnobService $api)
    {
        //
    }

    /**
     * Generate a deposit address for a user/wallet pair.
     *
     * @return array<string, mixed> Provider response — typically contains
     *                              `address`, `chain`, `currency`, `reference`.
     */
    public function generateAddress(User $user, Wallet $wallet, string $chain = 'tron', ?string $reference = null): array
    {
        $reference = $reference ?? BitnobService::reference('bn_dep');

        $payload = array_filter([
            'customer_email' => $user->email,
            'first_name'     => $user->first_name       ?? $user->name ?? 'User',
            'last_name'      => $user->last_name        ?? '',
            'currency'       => $wallet->currency->code ?? 'USDT',
            'chain'          => $chain,
            'reference'      => $reference,
        ], fn ($v) => $v !== null && $v !== '');

        $response = $this->api->post($this->api->url('deposit_address'), $payload);

        $data = $response['data'] ?? $response;

        return [
            'address'   => $data['address']  ?? $data['wallet_address'] ?? null,
            'chain'     => $data['chain']    ?? $chain,
            'currency'  => $data['currency'] ?? $payload['currency'],
            'reference' => $reference,
            'raw'       => $data,
        ];
    }

    /**
     * Apply a verified `stablecoin.deposit.success` webhook payload to a
     * pending transaction. Caller is responsible for signature verification.
     *
     * @param  array<string, mixed>                                 $event
     * @return array{credited: bool, transaction: Transaction|null}
     */
    public function applyDepositSuccess(array $event): array
    {
        $reference = (string) ($event['reference'] ?? '');
        if ($reference === '') {
            return ['credited' => false, 'transaction' => null];
        }

        $tx = Transaction::query()->where('trx_id', $reference)->first()
            ?? Transaction::query()->whereJsonContains('meta->bitnob_reference', $reference)->first();

        if (! $tx || $tx->status?->value === 'completed') {
            return ['credited' => false, 'transaction' => $tx];
        }

        $meta                        = $tx->meta ?? [];
        $meta['bitnob_deposit']      = $event;
        $meta['bitnob_deposit_hash'] = $event['hash'] ?? null;
        $meta['bitnob_deposit_at']   = now()->toIso8601String();
        $tx->meta                    = $meta;

        // Credit via the existing DepositHandler success path so wallets,
        // notifications, and referrals all fire consistently.
        $tx->status = TrxStatus::COMPLETED;
        $tx->save();
        app(DepositHandler::class)->handleSuccess($tx);

        return ['credited' => true, 'transaction' => $tx];
    }
}
