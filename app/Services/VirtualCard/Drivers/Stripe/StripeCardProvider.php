<?php

namespace App\Services\VirtualCard\Drivers\Stripe;

use App\Exceptions\NotifyErrorException;
use App\Models\Cardholders;
use App\Models\PaymentGateway;
use App\Models\VirtualCard;
use App\Models\VirtualCardRequest;
use App\Services\VirtualCard\Drivers\AbstractVirtualCardProvider;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

class StripeCardProvider extends AbstractVirtualCardProvider
{
    private function client(): StripeClient
    {
        $credentials = PaymentGateway::getCredentials('stripe');

        return new StripeClient($credentials['stripe_secret']);
    }

    /**
     * Stripe health probe — pulls the account record. Test-mode keys
     * surface as `livemode: false`, so the admin UI can show whether
     * the gateway is wired to test or live.
     */
    public function testConnection(): array
    {
        $started = microtime(true);
        try {
            $account = $this->client()->accounts->retrieve();
            $latency = (int) ((microtime(true) - $started) * 1000);
            $live    = (bool) ($account->charges_enabled ?? false) && ! str_starts_with((string) ($account->id ?? ''), 'acct_test');
            $mode    = $live ? 'live' : 'sandbox';

            return [
                'ok'      => true,
                'mode'    => $mode,
                'message' => __('Stripe credentials accepted (account :id). Reachable in :ms ms.', [
                    'id' => $account->id ?? 'unknown',
                    'ms' => $latency,
                ]),
                'latency_ms' => $latency,
                'details'    => [
                    'account_id'       => $account->id               ?? null,
                    'charges_enabled'  => $account->charges_enabled  ?? null,
                    'payouts_enabled'  => $account->payouts_enabled  ?? null,
                    'default_currency' => $account->default_currency ?? null,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'ok'         => false,
                'mode'       => null,
                'message'    => __('Stripe connection failed: :err', ['err' => $e->getMessage()]),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'details'    => ['exception' => get_class($e)],
            ];
        }
    }

    /**
     * Issue a virtual card with Stripe for the given request.
     * Handles both personal and business cardholders.
     */
    public function issueCard(VirtualCardRequest $request): array
    {
        $stripe = $this->client();
        $ch     = Cardholders::with('business')->findOrFail($request->cardholder_id);

        // 1. Create or repair the Stripe cardholder with the FULL payload
        //    Stripe Issuing requires (phone, DOB, terms acceptance, etc.).
        //    Skipping any of these creates a cardholder with "outstanding
        //    requirements" and Stripe will refuse to activate cards for it.
        $stripeCardholder = $stripe->issuing->cardholders->create(
            $this->buildCardholderPayload($ch)
        );

        // 2. Issue the card. Stripe defaults to `inactive`; we ask for
        //    `active` explicitly so the user can use it immediately.
        $stripeCard = $stripe->issuing->cards->create([
            'cardholder' => $stripeCardholder->id,
            'currency'   => $request->wallet->currency->code,
            'type'       => 'virtual',
            'status'     => 'active',
        ]);

        return [
            'id'           => $stripeCard->id,
            'last4'        => $stripeCard->last4,
            'brand'        => $stripeCard->brand,
            'expiry_month' => $stripeCard->exp_month,
            'expiry_year'  => $stripeCard->exp_year,
            'status'       => $stripeCard->status,
            'meta'         => [
                'stripe_cardholder_id' => $stripeCardholder->id,
                'stripe_card_id'       => $stripeCard->id,
            ],
            'raw' => $stripeCard,
        ];
    }

    /**
     * Build the complete Stripe Issuing cardholder payload. Used both
     * at issuance and when repairing an existing cardholder before an
     * unfreeze attempt.
     *
     * Stripe Issuing requires the following for US individuals:
     *   - name, email
     *   - phone_number in E.164 (e.g. +14155552671)
     *   - billing.address (line1/city/state/country/postal_code)
     *   - individual.first_name, individual.last_name
     *   - individual.dob.{day,month,year}
     *   - individual.card_issuing.user_terms_acceptance.{date,ip}
     *
     * For US companies:
     *   - name, email
     *   - phone_number
     *   - billing.address
     *   - company.tax_id
     */
    private function buildCardholderPayload(Cardholders $ch): array
    {
        $isBusiness = $ch->card_type && method_exists($ch->card_type, 'isBusiness') && $ch->card_type->isBusiness();
        $business   = $ch->business;

        $billing = [
            'line1'       => $isBusiness && $business ? ($business->address_line1 ?? $ch->address_line1) : $ch->address_line1,
            'city'        => $isBusiness && $business ? ($business->city ?? $ch->city) : $ch->city,
            'state'       => $isBusiness && $business ? ($business->state ?? $ch->state) : $ch->state,
            'country'     => $isBusiness && $business ? ($business->country ?? $ch->country) : $ch->country,
            'postal_code' => $isBusiness && $business ? ($business->postal_code ?? $ch->postal_code) : $ch->postal_code,
        ];

        if ($isBusiness && $business?->address_line2) {
            $billing['line2'] = $business->address_line2;
        } elseif (! $isBusiness && $ch->address_line2) {
            $billing['line2'] = $ch->address_line2;
        }

        $payload = [
            'type'         => $isBusiness ? 'company' : 'individual',
            'name'         => $isBusiness && $business ? $business->business_name : ($ch->full_name ?: trim($ch->first_name.' '.$ch->last_name)),
            'email'        => $isBusiness && $business ? ($business->contact_email ?? $ch->email) : $ch->email,
            'phone_number' => $this->formatPhoneE164(
                $isBusiness && $business ? ($business->phone_country_code ?? $ch->phone_country_code) : $ch->phone_country_code,
                $isBusiness && $business ? ($business->contact_phone ?? $ch->mobile) : $ch->mobile
            ),
            'billing' => [
                'address' => $billing,
            ],
        ];

        if ($isBusiness && $business) {
            $payload['company'] = [
                'tax_id' => $business->tin,
            ];
        } else {
            $dob                   = $ch->dob;
            $payload['individual'] = [
                'first_name' => $ch->first_name,
                'last_name'  => $ch->last_name,
                'dob'        => $dob ? [
                    'day'   => (int) $dob->format('d'),
                    'month' => (int) $dob->format('m'),
                    'year'  => (int) $dob->format('Y'),
                ] : null,
                // Stripe requires explicit acceptance of card terms by the
                // cardholder. The admin clicking "Approve" or "Activate"
                // is treated as the acceptance signal — Stripe accepts a
                // server-side timestamp + IP address.
                'card_issuing' => [
                    'user_terms_acceptance' => [
                        'date' => time(),
                        'ip'   => request()?->ip() ?? '127.0.0.1',
                    ],
                ],
            ];
        }

        return array_filter($payload, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Format a phone into Stripe-friendly E.164. Accepts any combination
     * of country code (with or without "+") and local mobile, strips
     * everything that isn't a digit, then prepends "+".
     */
    private function formatPhoneE164(?string $countryCode, ?string $mobile): ?string
    {
        $combined = preg_replace('/\D+/', '', (string) $countryCode.(string) $mobile);

        if (! $combined) {
            return null;
        }

        return '+'.$combined;
    }

    /**
     * Push the latest cardholder data up to Stripe (used before an
     * activation attempt on a card whose Stripe cardholder is still
     * marked with outstanding requirements).
     */
    private function syncCardholderRequirements(VirtualCard $card): void
    {
        $stripeCardholderId = $card->meta['stripe_cardholder_id'] ?? null;
        if (! $stripeCardholderId) {
            return;
        }

        // Resolve the local Cardholder via card → request → cardholder_id.
        // Fall back to the user's most recent approved cardholder so an
        // orphan card row (request deleted) can still be repaired.
        $cardholderId = $card->request?->cardholder_id;
        $ch           = $cardholderId
            ? Cardholders::with('business')->find($cardholderId)
            : Cardholders::with('business')
                ->where('user_id', $card->user_id)
                ->latest('id')
                ->first();

        if (! $ch) {
            return;
        }

        // Strip create-only fields. Stripe's cardholder UPDATE endpoint
        // rejects `name` and `type` (set permanently at creation) — sending
        // them returns "Received unknown parameter: name" / `type`.
        $payload = $this->buildCardholderPayload($ch);
        unset($payload['name'], $payload['type']);

        $this->client()->issuing->cardholders->update($stripeCardholderId, $payload);
    }

    /**
     * Return the sensitive card details (full PAN, CVV, expiry) for the
     * "Reveal" / card-flip flow. Stripe Issuing returns these only when
     * the card is retrieved with `expand[]=number&expand[]=cvc` (the
     * Stripe SDK accepts the same shape).
     *
     * In Stripe test mode this works for any card. In live mode it
     * requires the account to have PCI-compliant access enabled.
     */
    public function getCardDetails(VirtualCard $card)
    {
        try {
            $stripeCard = $this->client()->issuing->cards->retrieve(
                $card->provider_card_id,
                ['expand' => ['number', 'cvc']]
            );

            // Stripe sets `livemode: false` on every object created with a
            // test secret key. We forward that flag so the front-end can
            // badge the card as TEST and explain why real merchants will
            // reject the (otherwise valid) PAN.
            $isTestMode = isset($stripeCard->livemode) ? ! $stripeCard->livemode : true;

            return [
                'pan'        => $stripeCard->number                     ?? null,
                'cvv'        => $stripeCard->cvc                        ?? null,
                'exp_month'  => $stripeCard->exp_month                  ?? $card->expiry_month,
                'exp_year'   => $stripeCard->exp_year                   ?? $card->expiry_year,
                'last4'      => $stripeCard->last4                      ?? $card->last4,
                'brand'      => $stripeCard->brand                      ?? $card->brand,
                'cardholder' => optional($stripeCard->cardholder)->name ?? null,
                'test_mode'  => $isTestMode,
                'notice'     => $isTestMode
                    ? __('This is a Stripe test-mode card. The number is valid (passes Luhn) but only works in Stripe\'s test environment — real merchants will reject it.')
                    : null,
            ];
        } catch (\Throwable $e) {
            throw new NotifyErrorException(__('Failed to load card details: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Top up a Stripe Issuing card.
     *
     * Stripe Issuing has no direct "fund this card" API — Treasury is the
     * only first-class way and that's enterprise-only. The standard pattern
     * for non-Treasury accounts is to model the card balance via the card's
     * `spending_controls.spending_limits` with an `all_time` interval —
     * raising it has the practical effect of "adding" spendable headroom.
     *
     * The internal wallet → ledger transfer is handled by the caller
     * (`VirtualCardManager::topup` already debits the wallet and writes a
     * Transaction row); this method just synchronises the gateway-side
     * ceiling.
     *
     * @param float|int $amount in major units (e.g. dollars)
     * @param string    $cardID Stripe Issuing card id
     */
    public function topUpCard($amount, $cardID): array
    {
        try {
            $current = $this->client()->issuing->cards->retrieve($cardID, []);
            $cents   = (int) round(((float) $amount) * 100);
            $newCap  = $this->resolveAllTimeLimitCents($current) + $cents;

            $updated = $this->client()->issuing->cards->update($cardID, [
                'spending_controls' => [
                    'spending_limits' => [
                        ['amount' => $newCap, 'interval' => 'all_time'],
                    ],
                ],
            ]);

            return [
                'gateway'     => 'stripe',
                'method'      => 'spend_limit_increase',
                'limit_cents' => $newCap,
                'card_id'     => $updated->id,
            ];
        } catch (\Throwable $e) {
            throw new NotifyErrorException(__('Failed to top up Stripe card: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Withdraw from a Stripe Issuing card by lowering its all-time spend
     * limit. Same model as topUpCard above. The amount is clamped at zero
     * so an over-withdrawal can't drive the card into a negative ceiling.
     */
    public function withdrawFromCard($amount, $cardID): array
    {
        try {
            $current = $this->client()->issuing->cards->retrieve($cardID, []);
            $cents   = (int) round(((float) $amount) * 100);
            $newCap  = max(0, $this->resolveAllTimeLimitCents($current) - $cents);

            $updated = $this->client()->issuing->cards->update($cardID, [
                'spending_controls' => [
                    'spending_limits' => $newCap > 0
                        ? [['amount' => $newCap, 'interval' => 'all_time']]
                        : [],
                ],
            ]);

            return [
                'gateway'     => 'stripe',
                'method'      => 'spend_limit_decrease',
                'limit_cents' => $newCap,
                'card_id'     => $updated->id,
            ];
        } catch (\Throwable $e) {
            throw new NotifyErrorException(__('Failed to withdraw from Stripe card: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Find the current `all_time` spending-limit amount in cents on a
     * Stripe Issuing card. Returns 0 when no all_time limit is set so a
     * fresh card starts at a clean baseline.
     */
    private function resolveAllTimeLimitCents($stripeCard): int
    {
        $limits = $stripeCard->spending_controls->spending_limits ?? [];
        if (! is_iterable($limits)) {
            return 0;
        }
        foreach ($limits as $limit) {
            $interval = is_array($limit) ? ($limit['interval'] ?? null) : ($limit->interval ?? null);
            $amount   = is_array($limit) ? ($limit['amount'] ?? 0) : ($limit->amount ?? 0);
            if ($interval === 'all_time') {
                return (int) $amount;
            }
        }

        return 0;
    }

    public function freezeCard(VirtualCard $card): array
    {
        try {
            $stripeCard = $this->client()->issuing->cards->update(
                $card->provider_card_id,
                ['status' => 'inactive']
            );

            return ['status' => $stripeCard->status, 'soft' => false];
        } catch (\Throwable $e) {
            throw new NotifyErrorException(__('Failed to freeze card: :msg', ['msg' => $e->getMessage()]));
        }
    }

    public function unfreezeCard(VirtualCard $card): array
    {
        // Stripe blocks card activation when the cardholder has any
        // outstanding requirements. Push the latest profile data first
        // so the activation request never trips that gate; only retry
        // once if a sync ran successfully — we don't want to mask a
        // genuine config error in an infinite retry loop.
        $attemptedSync = false;

        retry:
        try {
            $stripeCard = $this->client()->issuing->cards->update(
                $card->provider_card_id,
                ['status' => 'active']
            );

            return ['status' => $stripeCard->status, 'soft' => false];
        } catch (InvalidRequestException $e) {
            $msg       = $e->getMessage();
            $needsSync = ! $attemptedSync && str_contains($msg, 'outstanding requirements');

            if ($needsSync) {
                $attemptedSync = true;
                try {
                    $this->syncCardholderRequirements($card);
                    goto retry;
                } catch (\Throwable $syncErr) {
                    throw new NotifyErrorException(__('Failed to sync cardholder profile to Stripe: :msg', ['msg' => $syncErr->getMessage()]));
                }
            }

            throw new NotifyErrorException(__('Failed to unfreeze card: :msg', ['msg' => $msg]));
        } catch (\Throwable $e) {
            throw new NotifyErrorException(__('Failed to unfreeze card: :msg', ['msg' => $e->getMessage()]));
        }
    }
}
