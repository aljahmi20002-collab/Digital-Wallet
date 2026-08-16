<?php

namespace App\Services\Bitnob;

use App\Models\WithdrawAccount;

/**
 * Bitnob fiat payout (withdraw) service.
 *
 * Bitnob's payout flow is three steps:
 *   1. Quote   — POST /api/payouts/quote — locks an exchange rate, returns quote_id.
 *   2. Initialize — POST /api/payouts/{quoteId}/initialize — attaches beneficiary.
 *   3. Finalize — POST /api/payouts/{quoteId}/finalize — releases the funds.
 *
 * The webhook `payout.success` / `payout.failed` confirms terminal state. A
 * caller typically performs Quote+Initialize synchronously, persists the
 * Bitnob payout id, then runs Finalize and waits for the webhook.
 */
class BitnobPayoutService
{
    public function __construct(protected BitnobService $api)
    {
        //
    }

    /**
     * Step 1: lock a quote.
     *
     * @param  array<string, mixed> $input amount/settlement_amount, country, from_asset (USDT), to_currency, source
     * @return array<mixed>
     */
    public function quote(array $input): array
    {
        $payload = array_filter([
            'amount'            => $input['amount']            ?? null,
            'settlement_amount' => $input['settlement_amount'] ?? null,
            'country'           => $input['country']           ?? null,
            'from_asset'        => $input['from_asset']        ?? 'USDT',
            'to_currency'       => $input['to_currency']       ?? null,
            'source'            => $input['source']            ?? 'offchain',
            'reference'         => $input['reference']         ?? BitnobService::reference('bn_quote'),
            'chain'             => $input['chain']             ?? null,
        ], fn ($v) => $v !== null);

        return $this->api->post($this->api->url('payout_quote'), $payload);
    }

    /**
     * Step 2: attach beneficiary + payment reason. The beneficiary shape
     * differs per `destination_type` (bank / mobile_money / swift) — the
     * caller assembles the right shape from a WithdrawAccount.
     *
     * @param  array<string, mixed> $beneficiary
     * @return array<mixed>
     */
    public function initialize(string $quoteId, array $beneficiary, ?string $paymentReason = 'vendor_payment', ?string $callbackUrl = null, ?string $reference = null): array
    {
        $payload = array_filter([
            'quote_id'       => $quoteId,
            'reference'      => $reference ?? BitnobService::reference('bn_payout'),
            'payment_reason' => $paymentReason,
            'beneficiary'    => $beneficiary,
            'callback_url'   => $callbackUrl,
        ], fn ($v) => $v !== null);

        return $this->api->post($this->api->url('payout_initialize', $quoteId), $payload);
    }

    /**
     * Step 3: actually release funds.
     *
     * @return array<mixed>
     */
    public function finalize(string $quoteId): array
    {
        return $this->api->post($this->api->url('payout_finalize', $quoteId));
    }

    /**
     * Verify a destination account (returns the real account holder name).
     *
     * @return array<mixed>
     */
    public function accountLookup(string $country, string $bankCode, string $accountNumber): array
    {
        return $this->api->get($this->api->url('payout_lookup'), [
            'country'        => $country,
            'bank_code'      => $bankCode,
            'account_number' => $accountNumber,
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function listPayouts(int $limit = 20, int $offset = 0): array
    {
        return $this->api->get($this->api->url('payout_list'), ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * @return array<mixed>
     */
    public function showPayout(string $payoutId): array
    {
        return $this->api->get($this->api->url('payout_show', $payoutId));
    }

    /**
     * @return array<mixed>
     */
    public function supportedCountries(): array
    {
        return $this->api->get($this->api->url('payout_countries'));
    }

    /**
     * @return array<mixed>
     */
    public function banksFor(string $countryCode): array
    {
        return $this->api->get($this->api->url('payout_banks', $countryCode));
    }

    /**
     * @return array<mixed>
     */
    public function limits(): array
    {
        return $this->api->get($this->api->url('payout_limits'));
    }

    /**
     * Convenience: build a beneficiary payload from a WithdrawAccount row.
     * Adjust as your withdraw-account form fields evolve — the keys must
     * match what Bitnob expects per `destination_type`.
     *
     * @return array<string, mixed>
     */
    public function beneficiaryFromWithdrawAccount(WithdrawAccount $account, string $country, string $destinationType = 'bank'): array
    {
        $info = is_array($account->credentials) ? $account->credentials : [];

        return array_filter([
            'destination_type' => $destinationType,
            'country'          => $country,
            'account_name'     => $info['account_name']   ?? $account->name ?? null,
            'account_number'   => $info['account_number'] ?? null,
            'bank_code'        => $info['bank_code']      ?? null,
            'swift_code'       => $info['swift_code']     ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Run the full quote → initialize → finalize sequence in one call.
     *
     * @param  array<string, mixed> $quoteInput
     * @param  array<string, mixed> $beneficiary
     * @return array<mixed>
     */
    public function payout(array $quoteInput, array $beneficiary, ?string $callbackUrl = null, ?string $paymentReason = 'vendor_payment', ?string $reference = null): array
    {
        $quote   = $this->quote($quoteInput);
        $quoteId = data_get($quote, 'data.quote_id') ?? data_get($quote, 'data.id') ?? data_get($quote, 'quote_id');

        if (! $quoteId) {
            throw BitnobException::fromResponse('Bitnob payout: missing quote_id', is_array($quote) ? $quote : []);
        }

        $this->initialize($quoteId, $beneficiary, $paymentReason, $callbackUrl, $reference);

        return $this->finalize($quoteId);
    }
}
