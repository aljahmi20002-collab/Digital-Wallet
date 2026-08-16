<?php

declare(strict_types=1);

namespace App\Services\VirtualCard\Drivers\Bitnob;

use App\Exceptions\NotifyErrorException;
use App\Models\Cardholders;
use App\Models\PaymentGateway;
use App\Models\VirtualCard;
use App\Models\VirtualCardRequest;
use App\Services\Bitnob\BitnobException;
use App\Services\Bitnob\BitnobService;
use App\Services\VirtualCard\Drivers\AbstractVirtualCardProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bitnob virtual-card provider.
 *
 * Mirrors the documented Bitnob v1 virtual-card flow:
 *   POST /api/v1/virtualcards/registercarduser   — register the cardholder + KYC
 *   POST /api/v1/virtualcards/create             — issue the card (linked by email)
 *   POST /api/v1/virtualcards/topup              — fund a card (amount in cents)
 *   POST /api/v1/virtualcards/withdraw           — withdraw from a card
 *   POST /api/v1/virtualcards/freeze             — freeze a card by cardId
 *   POST /api/v1/virtualcards/unfreeze           — unfreeze a card by cardId
 *   POST /api/v1/virtualcards/terminate          — permanently terminate
 *   GET  /api/v1/virtualcards/cards/{id}         — fetch live card details
 *   GET  /api/v1/virtualcards/cards/{id}/transactions — list transactions
 *
 * Authentication is a static API token sent via `Authorization: Bearer ...`.
 * The token is stored in the `payment_gateways.bitnob` row's credentials JSON
 * under any of: api_secret, api_key, secret_key, secret, token, access_token.
 *
 * Bitnob uses a two-step issuance flow keyed by `customerEmail`: the register
 * call captures the cardholder + KYC, the create call references the same
 * email and returns the new card. There is no customer-id round-trip.
 */
class BitnobCardProvider extends AbstractVirtualCardProvider
{
    private const SUPPORTED_CARD_BRAND = 'visa';

    private const CARD_AMOUNT_UNITS = 100;

    private array $credentials;

    private Client $httpClient;

    private string $baseUrl;

    private bool $sandbox;

    private string $apiToken;

    private BitnobService $api;

    public function __construct()
    {
        $this->credentials = PaymentGateway::getCredentials('bitnob');
        $this->httpClient  = new Client;

        $env = strtolower((string) (
            $this->credentials['environment']
            ?? $this->credentials['env']
            ?? $this->credentials['mode']
            ?? ''
        ));
        $this->sandbox = (bool) (
            $this->credentials['sandbox']
            ?? $this->credentials['test_mode']
            ?? ($env !== '' ? ! in_array($env, ['live', 'production', 'prod'], true) : true)
        );
        $this->baseUrl = $this->sandbox
            ? 'https://sandboxapi.bitnob.co/api/v1'
            : 'https://api.bitnob.co/api/v1';

        // Resolve API token from any of the credential key shapes we've
        // seen across Bitnob plans / our own seeders. The Bearer token
        // is what Bitnob calls the "Secret Key" — any of these slots
        // may carry it depending on how the row was provisioned.
        $tokenKeys = [
            'api_secret', 'api_key', 'secret_key', 'secret',
            'token', 'access_token', 'bearer_token',
            'client_secret', // legacy seeder key; Bitnob's Bearer token lives here
        ];
        $resolvedToken = null;
        foreach ($tokenKeys as $key) {
            if (! empty($this->credentials[$key]) && is_string($this->credentials[$key])) {
                $resolvedToken = trim((string) $this->credentials[$key]);
                break;
            }
        }
        if (empty($resolvedToken)) {
            throw new NotifyErrorException(__('Bitnob API token is missing. Please add api_secret or api_key to the Bitnob gateway credentials.'));
        }
        $this->apiToken = $resolvedToken;
        $this->api      = app(BitnobService::class);
    }

    private function issueCurrentApiCard(Cardholders $cardholder, VirtualCardRequest $request): array
    {
        $this->resolveCardBrand($request);

        $payload = $this->buildCurrentCardPayload($cardholder, $request);
        $this->assertCurrentCardPayloadReady($payload);

        Log::info('Bitnob issueCard begin', [
            'requestId'       => $request->id,
            'cardholderId'    => $cardholder->id,
            'cardholderEmail' => $cardholder->email,
            'userId'          => $request->user_id,
            'walletId'        => $request->wallet_id,
            'providerId'      => $request->provider_id,
            'country'         => $cardholder->country,
            'amount'          => $request->initial_load_amount,
            'currency'        => $request->wallet?->currency?->code ?? 'USD',
            'codeVersion'     => 'v3-api-cards',
            'payload_keys'    => array_keys($payload),
            'customer_keys'   => array_keys($payload['customer'] ?? []),
        ]);

        try {
            $response = $this->api->post($this->api->url('cards'), $payload);
        } catch (BitnobException $e) {
            Log::error('Bitnob issueCard API exception', [
                'requestId'    => $request->id,
                'cardholderId' => $cardholder->id,
                'userId'       => $request->user_id,
                'message'      => $e->getMessage(),
                'context'      => $this->redactLogContext($e->context()),
            ]);

            throw new NotifyErrorException($e->getMessage());
        }

        $cardData = $this->extractCurrentCardData($response);
        if (empty($cardData['id'])) {
            Log::error('Bitnob card creation response missing card id', [
                'requestId'    => $request->id,
                'cardholderId' => $cardholder->id,
                'response'     => $this->redactLogContext($response),
            ]);

            throw new NotifyErrorException(__('Bitnob did not return a card id.'));
        }

        Log::info('Bitnob create card response', [
            'cardholderId'  => $cardholder->id,
            'requestId'     => $request->id,
            'cardId'        => $cardData['id']             ?? null,
            'status'        => $cardData['status']         ?? null,
            'createdStatus' => $cardData['created_status'] ?? ($cardData['createdStatus'] ?? null),
            'response_keys' => array_keys($cardData),
        ]);

        if ($this->isCreationFailed($cardData)) {
            $bitnobMessage = $this->extractFailureReason($cardData);
            Log::error('Bitnob card creation failed after provisioning', [
                'requestId'    => $request->id,
                'cardholderId' => $cardholder->id,
                'cardId'       => $cardData['id'] ?? null,
                'reason'       => $bitnobMessage,
                'raw'          => $this->redactLogContext($cardData),
            ]);

            return $this->failedCardResponse($cardData, $request, $bitnobMessage);
        }

        $last4                = $this->extractLast4($cardData);
        [$expMonth, $expYear] = $this->extractExpiry($cardData);
        $rawStatus            = (string) ($cardData['status'] ?? ($cardData['created_status'] ?? 'pending'));

        return [
            'id'           => (string) $cardData['id'],
            'last4'        => $last4,
            'brand'        => $cardData['card_brand'] ?? ($cardData['brand'] ?? 'visa'),
            'expiry_month' => $expMonth,
            'expiry_year'  => $expYear,
            'status'       => $this->mapBitnobStatus($rawStatus),
            'meta'         => array_filter([
                'card_id'          => (string) $cardData['id'],
                'bitnob_card_id'   => (string) $cardData['id'],
                'bitnob_user_id'   => $cardData['customer_id'] ?? null,
                'reference'        => $cardData['reference']   ?? null,
                'masked_pan'       => $cardData['masked_pan']  ?? null,
                'card_number'      => $cardData['card_number'] ?? null,
                'cvv'              => $cardData['cvv']         ?? null,
                'card_type'        => $cardData['card_type']   ?? 'virtual',
                'balance'          => isset($cardData['display_amount']) ? (float) $cardData['display_amount'] : 0,
                'currency'         => $cardData['balance_currency'] ?? ($request->wallet?->currency?->code ?? 'USD'),
                'created_at'       => $cardData['created_at']       ?? now()->toIso8601String(),
                'failure_reason'   => $cardData['failure_reason']   ?? null,
                'provider_details' => $cardData,
                'raw'              => $cardData,
            ], fn ($v) => $v !== null),
            'raw' => $cardData,
        ];
    }

    /**
     * Issue a virtual card using Bitnob's current /api/cards endpoint.
     */
    public function issueCard(VirtualCardRequest $request): array
    {
        $cardholder = Cardholders::with(['user', 'kycTemplate'])->findOrFail($request->cardholder_id);

        try {
            return $this->issueCurrentApiCard($cardholder, $request);
        } catch (NotifyErrorException $e) {
            if (! $this->isBitnobHmacAuthMessage($e->getMessage())) {
                throw $e;
            }

            Log::warning('Bitnob current API auth failed; falling back to legacy Bearer card issuance', [
                'requestId'    => $request->id,
                'cardholderId' => $cardholder->id,
                'message'      => $e->getMessage(),
            ]);
        }

        $this->assertRegistrationPayloadReady($this->buildRegistrationPayload($cardholder, $this->resolveCardBrand($request)));

        // Verbose breadcrumb — proves which code path is actually
        // running. If you see this line followed by `cardBrand: Visa`
        // in the next log entry, you know the new auto-index code is
        // live; if you only see the registration response without this
        // breadcrumb above it, OPcache is still serving stale bytecode
        // and PHP-FPM needs a reload.
        Log::info('Bitnob issueCard begin', [
            'requestId'       => $request->id,
            'cardholderId'    => $cardholder->id,
            'cardholderEmail' => $cardholder->email,
            'country'         => $cardholder->country,
            'codeVersion'     => 'v2-auto-index',
        ]);

        $cardBrand = $this->resolveCardBrand($request);

        // 1) Register the card user. Already-registered errors are ignored —
        //    Bitnob returns a non-success status with "already registered"
        //    message which is fine when re-issuing for the same cardholder.
        $this->registerCardUser($cardholder, $cardBrand);

        // 2) Create the actual card.
        $cardData = $this->createCard($cardholder, $request);

        // 3) Card creation is asynchronous on Bitnob's side. The POST
        //    returns immediately with `createdStatus: "pending"` and the
        //    card eventually flips to active (PAN/CVV/expiry populated)
        //    or failed. Poll the card endpoint until either side wins.
        //    Failure is permanent — usually insufficient parent-account
        //    USD balance, KYC reject, or unfetchable image URLs.
        if (! $this->hasFullCardData($cardData) && ! $this->isCreationFailed($cardData)) {
            try {
                $details  = $this->fetchCardDetailsRaw((string) $cardData['id']);
                $cardData = array_replace_recursive($cardData, $details);
            } catch (\Throwable $e) {
                Log::warning('Bitnob fetch card details after create failed', [
                    'cardId' => $cardData['id'] ?? null,
                    'error'  => $e->getMessage(),
                ]);
            }
        }
        if (! $this->hasFullCardData($cardData) && ! $this->isCreationFailed($cardData)) {
            for ($i = 0; $i < 8; $i++) {
                usleep(750000); // 0.75s backoff (~6s total)
                try {
                    $details  = $this->fetchCardDetailsRaw((string) $cardData['id']);
                    $cardData = array_replace_recursive($cardData, $details);
                    if ($this->hasFullCardData($cardData) || $this->isCreationFailed($cardData)) {
                        break;
                    }
                } catch (\Throwable $e) {
                    // keep retrying — transient fetch errors are fine
                }
            }
        }

        // Bitnob's create-card endpoint is asynchronous: a 200 response
        // with a card id is not the same as a successfully provisioned
        // card. If it flips to `createdStatus: failed`, treat that as a
        // provider decline so the approval transaction rolls back.
        $last4                = $this->extractLast4($cardData);
        [$expMonth, $expYear] = $this->extractExpiry($cardData);
        $rawStatus            = (string) ($cardData['status'] ?? ($cardData['createdStatus'] ?? 'pending'));

        if ($this->isCreationFailed($cardData)) {
            $bitnobMessage = $this->extractFailureReason($cardData);
            Log::error('Bitnob card creation failed after provisioning', [
                'cardId' => $cardData['id'] ?? null,
                'reason' => $bitnobMessage,
                'raw'    => $cardData,
            ]);

            return $this->failedCardResponse($cardData, $request, $bitnobMessage);
        }

        return [
            'id'           => (string) $cardData['id'],
            'last4'        => $last4,
            'brand'        => $cardData['cardBrand'] ?? ($cardData['brand'] ?? 'visa'),
            'expiry_month' => $expMonth,
            'expiry_year'  => $expYear,
            'status'       => $this->mapBitnobStatus($rawStatus),
            'meta'         => array_filter([
                'card_id'        => (string) $cardData['id'],
                'bitnob_card_id' => (string) $cardData['id'],
                'bitnob_user_id' => $cardData['cardUserId'] ?? ($cardData['userId'] ?? null),
                'reference'      => $cardData['reference']  ?? null,
                'card_number'    => $cardData['cardNumber'] ?? null,
                'cvv'            => $cardData['cvv']        ?? null,
                'card_type'      => $cardData['cardType']   ?? 'virtual',
                'balance'        => isset($cardData['balance']) ? (float) $cardData['balance'] : 0,
                'currency'       => $cardData['currency']  ?? ($request->wallet?->currency?->code ?? 'USD'),
                'created_at'     => $cardData['createdAt'] ?? now()->toIso8601String(),
                // Surface the failure reason on the card row so the
                // dashboard can show it without re-querying Bitnob.
                'failure_reason'   => $cardData['failure_reason'] ?? null,
                'provider_details' => $cardData,
                'raw'              => $cardData,
            ], fn ($v) => $v !== null),
            'raw' => $cardData,
        ];
    }

    /**
     * Top up a virtual card. Bitnob expects current card API base units.
     */
    public function topUpCard($amount, $cardID): array
    {
        try {
            $response = $this->api->post($this->api->url('card_balance', (string) $cardID), [
                'type'      => 'fund',
                'amount'    => (int) round((float) $amount * self::CARD_AMOUNT_UNITS),
                'reference' => 'vc_topup_'.Str::uuid()->toString(),
            ]);
            $data = $this->extractCurrentCardData($response);

            return [
                'id'        => (string) ($data['id'] ?? ''),
                'status'    => $data['status'] ?? 'completed',
                'cardId'    => (string) ($data['card_id'] ?? $cardID),
                'reference' => $data['reference'] ?? null,
                'amount'    => (float) $amount,
                'balance'   => $data['display_amount'] ?? null,
            ];
        } catch (BitnobException $e) {
            if (! $this->isBitnobHmacAuthMessage($e->getMessage())) {
                throw new NotifyErrorException($e->getMessage());
            }

            Log::warning('Bitnob current API auth failed; falling back to legacy Bearer card top-up', [
                'cardId'  => $cardID,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $reference     = 'vc_topup_'.Str::uuid()->toString();
            $amountInCents = (int) round((float) $amount * 100);

            $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/topup", [
                'json' => [
                    'cardId'    => (string) $cardID,
                    'amount'    => $amountInCents,
                    'reference' => $reference,
                ],
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (! ($responseData['status'] ?? false)) {
                Log::error('Bitnob top-up error', ['response' => $responseData]);
                throw new NotifyErrorException($responseData['message'] ?? __('Bitnob top-up failed.'));
            }

            $data = $responseData['data'] ?? [];

            return [
                'id'        => (string) ($data['id'] ?? ''),
                'status'    => $data['status'] ?? 'completed',
                'cardId'    => (string) ($data['cardId'] ?? $cardID),
                'reference' => $data['reference'] ?? $reference,
                'amount'    => (float) $amount,
                'balance'   => isset($data['balance']) ? ((float) $data['balance']) / 100 : null,
            ];
        } catch (GuzzleException $e) {
            Log::error('Bitnob top-up exception', ['error' => $e->getMessage()]);
            throw new NotifyErrorException($this->extractApiErrorMessage($e));
        }
    }

    /**
     * Withdraw from a virtual card.
     */
    public function withdrawFromCard($amount, $cardID): array
    {
        try {
            $response = $this->api->post($this->api->url('card_withdraw', (string) $cardID), [
                'type'      => 'withdraw',
                'amount'    => (int) round((float) $amount * self::CARD_AMOUNT_UNITS),
                'reference' => 'vc_withdraw_'.Str::uuid()->toString(),
            ]);
            $data = $this->extractCurrentCardData($response);

            return [
                'id'        => (string) ($data['id'] ?? ''),
                'status'    => $data['status'] ?? 'completed',
                'cardId'    => (string) ($data['card_id'] ?? $cardID),
                'reference' => $data['reference'] ?? null,
                'amount'    => (float) $amount,
                'balance'   => $data['display_amount'] ?? null,
            ];
        } catch (BitnobException $e) {
            if (! $this->isBitnobHmacAuthMessage($e->getMessage())) {
                throw new NotifyErrorException($e->getMessage());
            }

            Log::warning('Bitnob current API auth failed; falling back to legacy Bearer card withdrawal', [
                'cardId'  => $cardID,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $reference     = 'vc_withdraw_'.Str::uuid()->toString();
            $amountInCents = (int) round((float) $amount * 100);

            $payload = [
                'cardId'    => (string) $cardID,
                'amount'    => $amountInCents,
                'reference' => $reference,
            ];

            // Try the canonical endpoint first, fall back to the legacy
            // `withdrawal` spelling that some Bitnob plans expose.
            $responseData = null;
            try {
                $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/withdraw", [
                    'json'    => $payload,
                    'headers' => $this->getHeaders(),
                    'timeout' => 30,
                ]);
                $responseData = json_decode($response->getBody()->getContents(), true);
            } catch (GuzzleException $primaryEx) {
                if (in_array($primaryEx->getCode(), [404, 405], true)) {
                    $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/withdrawal", [
                        'json'    => $payload,
                        'headers' => $this->getHeaders(),
                        'timeout' => 30,
                    ]);
                    $responseData = json_decode($response->getBody()->getContents(), true);
                } else {
                    throw $primaryEx;
                }
            }

            if (! ($responseData['status'] ?? false)) {
                Log::error('Bitnob withdrawal error', ['response' => $responseData]);
                throw new NotifyErrorException($responseData['message'] ?? __('Bitnob withdrawal failed.'));
            }

            $data = $responseData['data'] ?? [];

            return [
                'id'        => (string) ($data['id'] ?? ''),
                'status'    => $data['status'] ?? 'completed',
                'cardId'    => (string) ($data['cardId'] ?? $cardID),
                'reference' => $data['reference'] ?? $reference,
                'amount'    => (float) $amount,
                'balance'   => isset($data['balance']) ? ((float) $data['balance']) / 100 : null,
            ];
        } catch (GuzzleException $e) {
            Log::error('Bitnob withdrawal exception', ['error' => $e->getMessage()]);
            throw new NotifyErrorException($this->extractApiErrorMessage($e));
        }
    }

    public function freezeCard(VirtualCard $card): array
    {
        $cardID = $this->resolveProviderCardId($card);
        if (! $cardID) {
            // No remote card yet — soft mode keeps the controller flow alive.
            return ['status' => 'inactive', 'soft' => true];
        }

        try {
            $response = $this->api->post($this->api->url('card_status', $cardID), ['status' => 'frozen']);
            $data     = $this->extractCurrentCardData($response);

            return [
                'status' => $data['status'] ?? 'frozen',
                'soft'   => false,
                'raw'    => $data,
            ];
        } catch (BitnobException $e) {
            if (! $this->isBitnobHmacAuthMessage($e->getMessage())) {
                throw new NotifyErrorException($e->getMessage());
            }

            Log::warning('Bitnob current API auth failed; falling back to legacy Bearer freeze', [
                'cardId'  => $cardID,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/freeze", [
                'json'    => ['cardId' => $cardID],
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (! ($responseData['status'] ?? false)) {
                Log::error('Bitnob freeze card error', ['response' => $responseData]);
                throw new NotifyErrorException($responseData['message'] ?? __('Bitnob freeze card failed.'));
            }

            return [
                'status' => 'inactive',
                'soft'   => false,
                'raw'    => $responseData['data'] ?? [],
            ];
        } catch (GuzzleException $e) {
            Log::error('Bitnob freeze card exception', ['error' => $e->getMessage()]);
            throw new NotifyErrorException($this->extractApiErrorMessage($e));
        }
    }

    public function unfreezeCard(VirtualCard $card): array
    {
        $cardID = $this->resolveProviderCardId($card);
        if (! $cardID) {
            return ['status' => 'active', 'soft' => true];
        }

        try {
            $response = $this->api->post($this->api->url('card_status', $cardID), ['status' => 'active']);
            $data     = $this->extractCurrentCardData($response);

            return [
                'status' => $data['status'] ?? 'active',
                'soft'   => false,
                'raw'    => $data,
            ];
        } catch (BitnobException $e) {
            if (! $this->isBitnobHmacAuthMessage($e->getMessage())) {
                throw new NotifyErrorException($e->getMessage());
            }

            Log::warning('Bitnob current API auth failed; falling back to legacy Bearer unfreeze', [
                'cardId'  => $cardID,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/unfreeze", [
                'json'    => ['cardId' => $cardID],
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (! ($responseData['status'] ?? false)) {
                Log::error('Bitnob unfreeze card error', ['response' => $responseData]);
                throw new NotifyErrorException($responseData['message'] ?? __('Bitnob unfreeze card failed.'));
            }

            return [
                'status' => 'active',
                'soft'   => false,
                'raw'    => $responseData['data'] ?? [],
            ];
        } catch (GuzzleException $e) {
            Log::error('Bitnob unfreeze card exception', ['error' => $e->getMessage()]);
            throw new NotifyErrorException($this->extractApiErrorMessage($e));
        }
    }

    public function getCardDetails(VirtualCard $card)
    {
        $cardID = $this->resolveProviderCardId($card);
        if (! $cardID) {
            throw new NotifyErrorException(__('Provider card id is missing for this card.'));
        }

        try {
            $response = $this->api->get($this->api->url('card', $cardID));
            $data     = $this->extractCurrentCardData($response);

            [$expMonth, $expYear] = $this->extractExpiry($data);

            return [
                'card_holder_name' => $data['name']        ?? null,
                'card_number'      => $data['card_number'] ?? null,
                'cvv'              => $data['cvv']         ?? null,
                'expiry'           => ($expMonth && $expYear)
                    ? sprintf('%02d/%s', (int) $expMonth, substr((string) $expYear, -2))
                    : null,
                'card_brand'  => $data['card_brand'] ?? $card->brand,
                'card_status' => $data['status']     ?? ($card->status?->value ?? null),
                'balance'     => isset($data['display_amount']) ? (float) $data['display_amount'] : 0,
                'currency'    => $data['balance_currency'] ?? null,
                'reference'   => $data['reference']        ?? null,
            ];
        } catch (BitnobException $e) {
            if (! $this->isBitnobHmacAuthMessage($e->getMessage())) {
                throw new NotifyErrorException(__('Card details API error: :msg', ['msg' => $e->getMessage()]));
            }

            Log::warning('Bitnob current API auth failed; falling back to legacy Bearer card details', [
                'cardId'  => $cardID,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $data = $this->fetchCardDetailsRaw($cardID);

            [$expMonth, $expYear] = $this->extractExpiry($data);

            return [
                'card_holder_name' => $data['name']       ?? null,
                'card_number'      => $data['cardNumber'] ?? null,
                'cvv'              => $data['cvv']        ?? null,
                'expiry'           => ($expMonth && $expYear)
                    ? sprintf('%02d/%s', (int) $expMonth, substr((string) $expYear, -2))
                    : null,
                'card_brand'  => $data['cardBrand'] ?? $card->brand,
                'card_status' => $data['status']    ?? ($card->status?->value ?? null),
                'balance'     => isset($data['balance']) ? (float) $data['balance'] : 0,
                'currency'    => $data['currency']  ?? null,
                'reference'   => $data['reference'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Bitnob card details fetch error', ['error' => $e->getMessage()]);

            throw new NotifyErrorException(__('Card details API error: :msg', ['msg' => $e->getMessage()]));
        }
    }

    public function testConnection(): array
    {
        $started = microtime(true);

        try {
            $response = $this->api->get($this->api->url('whoami'));
            $latency  = (int) ((microtime(true) - $started) * 1000);

            return [
                'ok'         => (bool) ($response['authenticated'] ?? true),
                'mode'       => $this->sandbox ? 'sandbox' : 'live',
                'message'    => __('Bitnob credentials accepted. Reachable in :ms ms.', ['ms' => $latency]),
                'latency_ms' => $latency,
                'details'    => [
                    'base_url' => config('bitnob.base_url.'.($this->sandbox ? 'sandbox' : 'live')),
                    'client'   => [
                        'client_id'     => $response['client_id']     ?? null,
                        'client_name'   => $response['client_name']   ?? null,
                        'authenticated' => $response['authenticated'] ?? null,
                        'environment'   => $response['environment']   ?? null,
                        'active'        => $response['active']        ?? null,
                    ],
                ],
            ];
        } catch (BitnobException $e) {
            $legacy = $this->testLegacyConnection($started, $e);
            if (($legacy['ok'] ?? false) === true) {
                return $legacy;
            }

            $context = $e->context();
            $body    = is_array($context['body'] ?? null) ? $context['body'] : [];
            $message = $e->getMessage();
            if (($context['status'] ?? null) === 401 && in_array($message, ['Invalid client credentials', 'Authentication failed'], true)) {
                $message = __('Bitnob rejected the saved Client ID and signing key. Copy a fresh Client ID and HMAC/client secret from the same Bitnob API app, confirm that app is active, then retry.');
            }

            return [
                'ok'         => false,
                'mode'       => $this->sandbox ? 'sandbox' : 'live',
                'message'    => __('Bitnob connection failed: :err', ['err' => $message]),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'details'    => array_filter([
                    'base_url'       => config('bitnob.base_url.'.($this->sandbox ? 'sandbox' : 'live')),
                    'status'         => $context['status']                 ?? null,
                    'trace_id'       => $context['trace_id']               ?? null,
                    'correlation_id' => $body['correlation_id']            ?? null,
                    'legacy_error'   => $legacy['details']['legacy_error'] ?? null,
                ], fn ($value) => $value !== null),
            ];
        }

        try {
            $response = $this->httpClient->get("{$this->baseUrl}/wallets", [
                'headers' => $this->getHeaders(),
                'timeout' => 10,
            ]);
            $latency = (int) ((microtime(true) - $started) * 1000);
            $data    = json_decode($response->getBody()->getContents(), true);
            $ok      = $response->getStatusCode() === 200 && (($data['status'] ?? true) !== false);

            return [
                'ok'      => $ok,
                'mode'    => $this->sandbox ? 'sandbox' : 'live',
                'message' => $ok
                    ? __('Bitnob credentials accepted. Reachable in :ms ms.', ['ms' => $latency])
                    : ($this->extractErrorMessageFromPayload(is_array($data) ? $data : null) ?? __('Bitnob returned a non-success status.')),
                'latency_ms' => $latency,
                'details'    => [
                    'base_url'    => $this->baseUrl,
                    'status_code' => $response->getStatusCode(),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'ok'         => false,
                'mode'       => $this->sandbox ? 'sandbox' : 'live',
                'message'    => __('Bitnob connection failed: :err', ['err' => $e->getMessage()]),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'details'    => ['base_url' => $this->baseUrl, 'exception' => get_class($e)],
            ];
        }
    }

    private function testLegacyConnection(float $started, BitnobException $hmacException): array
    {
        try {
            $response = $this->httpClient->get("{$this->baseUrl}/wallets", [
                'headers' => $this->getHeaders(),
                'timeout' => 10,
            ]);
            $latency = (int) ((microtime(true) - $started) * 1000);
            $data    = json_decode($response->getBody()->getContents(), true);
            $ok      = $response->getStatusCode() === 200 && (($data['status'] ?? true) !== false);

            if (! $ok) {
                return [
                    'ok'         => false,
                    'mode'       => $this->sandbox ? 'sandbox' : 'live',
                    'message'    => $this->extractErrorMessageFromPayload(is_array($data) ? $data : null) ?? __('Bitnob returned a non-success status.'),
                    'latency_ms' => $latency,
                    'details'    => [
                        'base_url'    => $this->baseUrl,
                        'status_code' => $response->getStatusCode(),
                        'auth_mode'   => 'bearer',
                    ],
                ];
            }

            $wallets = is_array($data['data'] ?? null) ? $data['data'] : [];

            return [
                'ok'         => true,
                'mode'       => $this->sandbox ? 'sandbox' : 'live',
                'message'    => __('Bitnob legacy API credentials accepted. Reachable in :ms ms.', ['ms' => $latency]),
                'latency_ms' => $latency,
                'details'    => [
                    'base_url'      => $this->baseUrl,
                    'auth_mode'     => 'bearer',
                    'wallet_count'  => count($wallets),
                    'hmac_error'    => $hmacException->getMessage(),
                    'hmac_trace_id' => $hmacException->context()['trace_id'] ?? null,
                    'hmac_status'   => $hmacException->context()['status']   ?? null,
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Bitnob legacy connection test failed after HMAC probe failed', [
                'mode'       => $this->sandbox ? 'sandbox' : 'live',
                'base_url'   => $this->baseUrl,
                'hmac_error' => $hmacException->getMessage(),
                'error'      => $e->getMessage(),
            ]);

            return [
                'ok'         => false,
                'mode'       => $this->sandbox ? 'sandbox' : 'live',
                'message'    => __('Bitnob connection failed: :err', ['err' => $hmacException->getMessage()]),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'details'    => [
                    'base_url'      => $this->baseUrl,
                    'auth_mode'     => 'bearer',
                    'legacy_error'  => $e->getMessage(),
                    'hmac_trace_id' => $hmacException->context()['trace_id'] ?? null,
                    'hmac_status'   => $hmacException->context()['status']   ?? null,
                ],
            ];
        }
    }

    private function isBitnobHmacAuthMessage(string $message): bool
    {
        return in_array($message, ['Invalid client credentials', 'Authentication failed'], true);
    }

    /**
     * Public method to verify/register a cardholder with Bitnob from Admin.
     */
    public function verifyCardholder(Cardholders $cardholder, ?string $cardBrand = self::SUPPORTED_CARD_BRAND): array
    {
        try {
            $payload = $this->buildRegistrationPayload($cardholder, $cardBrand);
            $this->assertRegistrationPayloadReady($payload);

            $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/registercarduser", [
                'json'    => $payload,
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (! ($responseData['status'] ?? false)) {
                $message = $this->extractErrorMessageFromPayload($responseData);
                if ($message !== null && $this->isAlreadyRegisteredMessage($message)) {
                    return $this->alreadyRegisteredResponse($message, $responseData);
                }

                Log::warning('Bitnob verify cardholder warning', ['response' => $responseData]);
            }

            return $responseData;
        } catch (GuzzleException $e) {
            $message = $this->extractApiErrorMessage($e);
            if ($this->isAlreadyRegisteredMessage($message)) {
                Log::info('Bitnob verify cardholder is already registered', [
                    'cardholderId' => $cardholder->id,
                    'message'      => $message,
                ]);

                return $this->alreadyRegisteredResponse($message);
            }

            Log::error('Bitnob verify cardholder exception', ['error' => $message]);
            throw new NotifyErrorException($message);
        }
    }

    /**
     * Register a card user. Soft-fails when Bitnob says "already registered"
     * so the create-card step can still proceed for repeat issuance.
     */
    private function registerCardUser(Cardholders $cardholder, ?string $cardBrand = self::SUPPORTED_CARD_BRAND): void
    {
        try {
            $payload = $this->buildRegistrationPayload($cardholder, $cardBrand);
            $this->assertRegistrationPayloadReady($payload);

            // Pre-flight diagnostic — KYC images that fail to resolve are
            // the #1 cause of silent KYC rejection (Bitnob accepts the
            // registration but the card never provisions). Log it so the
            // root cause is visible without hunting through Bitnob logs.
            $missing = [];
            foreach (['userPhoto', 'idImage', 'idType', 'idNumber'] as $key) {
                if (empty($payload[$key])) {
                    $missing[] = $key;
                }
            }
            if (! empty($missing)) {
                Log::warning('Bitnob registration payload missing KYC fields — card may fail to provision', [
                    'missing'      => $missing,
                    'cardholderId' => $cardholder->id,
                    'email'        => $cardholder->email,
                    'country'      => $cardholder->country,
                ]);
            }

            $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/registercarduser", [
                'json'    => $payload,
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            // Always log the response shape for diagnostics — Bitnob's
            // virtual-card support team usually asks for this.
            Log::info('Bitnob registercarduser response', [
                'cardholderId' => $cardholder->id,
                'sent_keys'    => array_keys($payload),
                'response'     => $responseData,
            ]);

            if (! ($responseData['status'] ?? false)) {
                $message = $this->extractErrorMessageFromPayload($responseData) ?? __('Bitnob registration failed.');
                if (! $this->isAlreadyRegisteredMessage($message)) {
                    Log::warning('Bitnob user registration warning', ['response' => $responseData]);
                    // Surface the real Bitnob message so the user can fix
                    // the cardholder data without guessing.
                    throw new NotifyErrorException($message);
                }
            }
        } catch (GuzzleException $e) {
            $msg = $this->extractApiErrorMessage($e);
            Log::error('Bitnob user registration exception', ['error' => $msg]);
            if (! $this->isAlreadyRegisteredMessage($msg)) {
                throw new NotifyErrorException($msg);
            }
        }
    }

    /**
     * Build the registration payload using the v1 camelCase field names.
     */
    private function buildRegistrationPayload(Cardholders $cardholder, ?string $cardBrand = self::SUPPORTED_CARD_BRAND): array
    {
        $dob = null;
        if (! empty($cardholder->dob)) {
            try {
                $dob = is_string($cardholder->dob)
                    ? $cardholder->dob
                    : $cardholder->dob->format('Y-m-d');
            } catch (\Throwable $t) {
                $dob = null;
            }
        }

        // Mirror the working voaray reference EXACTLY — that's the only
        // payload shape we know works on a real Bitnob sandbox. No
        // `cardBrand` here (it goes on the create-card call), mobile
        // alone (no E.164 prefix), country uppercased, KYC images
        // resolved separately below.
        $payload = [
            'customerEmail' => $cardholder->email,
            'firstName'     => $cardholder->first_name,
            'lastName'      => $cardholder->last_name,
            'phoneNumber'   => (string) $cardholder->mobile,
            'dateOfBirth'   => $dob,
            'line1'         => $cardholder->address_line1,
            'city'          => $cardholder->city,
            'state'         => $cardholder->state,
            'country'       => strtoupper((string) $cardholder->country),
            'zipCode'       => $cardholder->postal_code,
        ];

        $cardBrand = strtolower(trim((string) $cardBrand));
        if ($cardBrand !== '') {
            $payload['cardBrand'] = $cardBrand;
        }

        $docs = is_array($cardholder->kyc_documents) ? $cardholder->kyc_documents : [];

        $userPhoto = $this->resolveUserPhoto($cardholder, $docs);
        if ($userPhoto !== null) {
            $payload['userPhoto'] = $userPhoto;
        }

        $idImage = $this->resolveIdImage($docs);
        if ($idImage !== null) {
            $payload['idImage'] = $idImage;
        }

        // Map structured id_type column first; fall back to whatever
        // happens to be in the kyc_documents JSON.
        if (! empty($cardholder->id_type)) {
            $payload['idType'] = $this->mapIdType((string) $cardholder->id_type);
        } else {
            foreach (['idType', 'id_type', 'document_type', 'id_document_type'] as $key) {
                if (! empty($docs[$key]) && is_string($docs[$key])) {
                    $payload['idType'] = $this->mapIdType($docs[$key]);
                    break;
                }
            }
        }

        if (! empty($cardholder->id_number)) {
            $payload['idNumber'] = (string) $cardholder->id_number;
        } else {
            foreach (['idNumber', 'id_number', 'nin', 'passport_number', 'dl_number'] as $key) {
                if (! empty($docs[$key]) && is_string($docs[$key])) {
                    $payload['idNumber'] = $docs[$key];
                    break;
                }
            }
        }

        // Nigerian cardholders need BVN.
        if (strtoupper((string) $cardholder->country) === 'NG') {
            foreach (['bvn', 'BVN'] as $key) {
                if (! empty($docs[$key]) && is_string($docs[$key])) {
                    $payload['bvn'] = $docs[$key];
                    break;
                }
            }
        }

        return $payload;
    }

    private function buildCurrentCardPayload(Cardholders $cardholder, VirtualCardRequest $request): array
    {
        $initial = max(0, round((float) ($request->initial_load_amount ?? 0.0), 2));
        if ($initial <= 0) {
            throw new NotifyErrorException(__('Initial load amount is required for Bitnob card issuance.'));
        }
        if ($initial < 2.00) {
            throw new NotifyErrorException(__('Minimum initial load for Bitnob cards is 2.00 USD.'));
        }

        $customer = [
            'customer_type' => $this->isBusinessCardholder($cardholder) ? 'business' : 'individual',
            'first_name'    => (string) $cardholder->first_name,
            'last_name'     => (string) $cardholder->last_name,
            'email'         => (string) $cardholder->email,
            'phone_number'  => preg_replace('/\D+/', '', (string) $cardholder->mobile),
            'dial_code'     => $this->resolveDialCode($cardholder),
            'date_of_birth' => $this->formatDate($cardholder->dob),
            'id_type'       => $this->mapCurrentIdType((string) $cardholder->id_type),
            'id_number'     => (string) $cardholder->id_number,
            'line1'         => (string) $cardholder->address_line1,
            'city'          => (string) $cardholder->city,
            'state'         => (string) $cardholder->state,
            'postal_code'   => (string) $cardholder->postal_code,
            'country'       => $this->toAlpha3Country((string) $cardholder->country),
        ];

        $payload = [
            'amount'              => (int) round($initial * self::CARD_AMOUNT_UNITS),
            'card_type'           => 'virtual',
            'currency'            => 'USD',
            'name'                => $cardholder->full_name,
            'contactless_payment' => false,
            'customer'            => $customer,
        ];

        $webhookUrl = $this->publicWebhookUrl();
        if ($webhookUrl !== null) {
            $payload['webhook_url'] = $webhookUrl;
        }

        return $payload;
    }

    private function assertCurrentCardPayloadReady(array $payload): void
    {
        $missing = [];
        foreach (['amount', 'card_type', 'currency', 'name', 'customer'] as $key) {
            if (empty($payload[$key])) {
                $missing[] = $key;
            }
        }

        foreach (['customer_type', 'first_name', 'last_name', 'email', 'phone_number', 'dial_code', 'date_of_birth', 'id_type', 'id_number', 'line1', 'city', 'state', 'postal_code', 'country'] as $key) {
            if (empty($payload['customer'][$key] ?? null)) {
                $missing[] = "customer.$key";
            }
        }

        if ($missing !== []) {
            throw new NotifyErrorException(__('Bitnob cardholder KYC is incomplete. Missing: :fields.', [
                'fields' => implode(', ', $missing),
            ]));
        }
    }

    private function extractCurrentCardData(array $response): array
    {
        $data = $response['data'] ?? $response;
        if (isset($data['card']) && is_array($data['card'])) {
            return $data['card'];
        }

        return is_array($data) ? $data : [];
    }

    private function publicWebhookUrl(): ?string
    {
        $url  = route('ipn.handle', ['gateway' => 'bitnob']);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.local')) {
            return null;
        }

        return str_starts_with($url, 'https://') ? $url : null;
    }

    private function isBusinessCardholder(Cardholders $cardholder): bool
    {
        $type = $cardholder->card_type?->value ?? $cardholder->card_type;

        return strtolower((string) $type) === 'business';
    }

    private function resolveDialCode(Cardholders $cardholder): string
    {
        $dialCode = trim((string) $cardholder->phone_country_code);
        if ($dialCode !== '') {
            return str_starts_with($dialCode, '+') ? $dialCode : '+'.$dialCode;
        }

        return match (strtoupper((string) $cardholder->country)) {
            'BD', 'BGD' => '+880',
            'MG', 'MDG' => '+261',
            'NG', 'NGA' => '+234',
            'KE', 'KEN' => '+254',
            'GH', 'GHA' => '+233',
            'US', 'USA' => '+1',
            'GB', 'GBR' => '+44',
            default => '+1',
        };
    }

    private function formatDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return is_string($value) ? substr($value, 0, 10) : $value->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapCurrentIdType(string $idType): string
    {
        return match (strtoupper($idType)) {
            'PASSPORT' => 'passport',
            'DRIVERS_LICENSE', 'DRIVER_LICENSE', 'DL' => 'drivers_license',
            default => 'national_id',
        };
    }

    private function toAlpha3Country(string $country): string
    {
        return match (strtoupper(trim($country))) {
            'BD', 'BGD' => 'BGD',
            'MG', 'MDG' => 'MDG',
            'NG', 'NGA' => 'NGA',
            'KE', 'KEN' => 'KEN',
            'GH', 'GHA' => 'GHA',
            'US', 'USA' => 'USA',
            'GB', 'GBR', 'UK' => 'GBR',
            default => strtoupper(trim($country)),
        };
    }

    private function assertRegistrationPayloadReady(array $payload): void
    {
        $missing = [];
        foreach (['customerEmail', 'firstName', 'lastName', 'phoneNumber', 'dateOfBirth', 'line1', 'city', 'state', 'country', 'zipCode', 'idType', 'idNumber', 'userPhoto', 'idImage'] as $key) {
            if (empty($payload[$key])) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new NotifyErrorException(__('Bitnob cardholder KYC is incomplete. Missing: :fields.', [
                'fields' => implode(', ', $missing),
            ]));
        }

        foreach (['userPhoto', 'idImage'] as $key) {
            if (! $this->isPublicRasterImageUrl((string) $payload[$key])) {
                throw new NotifyErrorException(__('Bitnob requires :field to be a public JPG, PNG, GIF, or WebP URL. Current value is not reachable by Bitnob.', [
                    'field' => $key,
                ]));
            }
        }
    }

    private function createCard(Cardholders $cardholder, VirtualCardRequest $request): array
    {
        $initial = max(0, round((float) ($request->initial_load_amount ?? 0.0), 2));
        if ($initial <= 0) {
            throw new NotifyErrorException(__('Initial load amount is required for Bitnob card issuance.'));
        }
        if ($initial < 2.00) {
            throw new NotifyErrorException(__('Minimum initial load for Bitnob cards is 2.00 USD.'));
        }

        $cardBrand = $this->resolveCardBrand($request);
        $payload   = [
            'customerEmail' => $cardholder->email,
            'firstName'     => $cardholder->first_name,
            'lastName'      => $cardholder->last_name,
            'cardBrand'     => $cardBrand,
            'cardType'      => 'virtual',
            'reference'     => uniqid('vc_', true),
            'amount'        => (int) round($initial * 100),
        ];

        return $this->createCardOrIndexAndRetry($cardholder, $payload);
    }

    /**
     * Call /virtualcards/create. If Bitnob replies that the user is not
     * yet indexed for the requested brand, re-run registercarduser and
     * retry the create exactly once.
     * Past one retry we surface the error so the caller can save the
     * failed-card row and the operator can fix the underlying issue.
     *
     * @return array<mixed>
     */
    private function createCardOrIndexAndRetry(Cardholders $cardholder, array $payload, bool $allowRetry = true): array
    {
        // Log the outgoing payload (sans secrets) — invaluable when
        // Bitnob marks the card as failed and we need to compare
        // request shape against their working sandbox.
        Log::info('Bitnob create card request', [
            'cardholderId' => $cardholder->id,
            'allowRetry'   => $allowRetry,
            'payload'      => $payload,
        ]);

        try {
            $response = $this->httpClient->post("{$this->baseUrl}/virtualcards/create", [
                'json'    => $payload,
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Bitnob create card response', [
                'cardholderId' => $cardholder->id,
                'allowRetry'   => $allowRetry,
                'response'     => $responseData,
            ]);

            if (! ($responseData['status'] ?? false)) {
                Log::error('Bitnob create card error', ['response' => $responseData]);
                $message = $this->extractErrorMessageFromPayload($responseData) ?? __('Failed to create virtual card.');
                // Auto-recover from "not indexed" once. If Bitnob says
                // the card user already exists while re-registering, that
                // is treated as idempotent and we still retry creation.
                if ($allowRetry && $this->isNotIndexedForBrandError($message)) {
                    Log::info('Bitnob create card hit "not indexed" — re-registering and retrying once', [
                        'cardBrand' => $payload['cardBrand'] ?? null,
                    ]);
                    $this->registerCardUser($cardholder, (string) ($payload['cardBrand'] ?? self::SUPPORTED_CARD_BRAND));
                    // Fresh reference so Bitnob doesn't reject as duplicate.
                    $payload['reference'] = uniqid('vc_', true);

                    return $this->createCardOrIndexAndRetry($cardholder, $payload, allowRetry: false);
                }

                throw new NotifyErrorException($message);
            }

            $data = $responseData['data'] ?? [];
            if (empty($data['id'])) {
                throw new NotifyErrorException(__('Bitnob did not return a card id.'));
            }

            return $data;
        } catch (GuzzleException $e) {
            $msg = $this->extractApiErrorMessage($e);
            Log::error('Bitnob create card exception', ['error' => $msg]);
            // Same auto-recovery for HTTP-level errors that surface the
            // index error in the response body.
            if ($allowRetry && $this->isNotIndexedForBrandError($msg)) {
                Log::info('Bitnob create card hit "not indexed" via exception — re-registering and retrying once', [
                    'cardBrand' => $payload['cardBrand'] ?? null,
                ]);
                $this->registerCardUser($cardholder, (string) ($payload['cardBrand'] ?? self::SUPPORTED_CARD_BRAND));
                $payload['reference'] = uniqid('vc_', true);

                return $this->createCardOrIndexAndRetry($cardholder, $payload, allowRetry: false);
            }
            throw new NotifyErrorException($msg);
        }
    }

    private function isNotIndexedForBrandError(string $message): bool
    {
        $lower = strtolower($message);

        return str_contains($lower, 'not indexed')
            || str_contains($lower, 'not enrolled')
            || str_contains($lower, 'not registered for')
            || str_contains($lower, 'visa indexing');
    }

    private function resolveCardBrand(VirtualCardRequest $request): string
    {
        $cardBrand = strtolower((string) ($request->network?->value ?? $request->network ?? self::SUPPORTED_CARD_BRAND));

        if ($cardBrand !== self::SUPPORTED_CARD_BRAND) {
            throw new NotifyErrorException(__('Bitnob currently supports only Visa virtual cards. Use a Visa request or select another provider for :network.', [
                'network' => strtoupper($cardBrand),
            ]));
        }

        return $cardBrand;
    }

    private function isAlreadyRegisteredMessage(string $message): bool
    {
        $lower = strtolower($message);

        return str_contains($lower, 'already registered')
            || str_contains($lower, 'already exists')
            || str_contains($lower, 'id number already exists')
            || str_contains($lower, 'card user with this id number');
    }

    private function alreadyRegisteredResponse(string $message, ?array $raw = null): array
    {
        return [
            'status'  => true,
            'message' => __('Cardholder already exists on Bitnob and can be used for issuance.'),
            'data'    => [
                'already_registered' => true,
                'raw_message'        => $message,
                'raw'                => $raw,
            ],
        ];
    }

    /**
     * Raw card-detail fetch — returns Bitnob's data envelope contents.
     *
     * @return array<mixed>
     */
    private function fetchCardDetailsRaw(string $cardID): array
    {
        try {
            $response = $this->httpClient->get("{$this->baseUrl}/virtualcards/cards/{$cardID}", [
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (! ($responseData['status'] ?? false)) {
                throw new NotifyErrorException($responseData['message'] ?? __('Failed to get card details.'));
            }

            return $responseData['data'] ?? [];
        } catch (GuzzleException $e) {
            throw new NotifyErrorException($this->extractApiErrorMessage($e));
        }
    }

    private function resolveProviderCardId(VirtualCard $card): ?string
    {
        $candidates = [
            $card->meta['card_id']        ?? null,
            $card->meta['bitnob_card_id'] ?? null,
            $card->provider_card_id,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiToken,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    private function hasFullCardData(array $data): bool
    {
        $hasLast4  = $this->extractLast4($data) !== null;
        [$mm, $yy] = $this->extractExpiry($data);

        return $hasLast4 && $mm && $yy;
    }

    /**
     * Bitnob's create-card endpoint returns 200 immediately, then the
     * card is provisioned asynchronously. The terminal-failed signal is
     * `createdStatus === 'failed'` (or `status === 'failed'`).
     */
    private function isCreationFailed(array $data): bool
    {
        $createdStatus = strtolower((string) ($data['createdStatus'] ?? ($data['created_status'] ?? '')));
        $status        = strtolower((string) ($data['status'] ?? ''));

        return in_array($createdStatus, ['failed', 'failure'], true)
            || in_array($status, ['failed', 'failure', 'rejected', 'declined'], true);
    }

    /**
     * Best-effort scrape of the human-readable reason from a failed-card
     * payload. Bitnob doesn't always include one, so this returns null
     * when nothing useful can be surfaced.
     */
    private function extractFailureReason(array $data): ?string
    {
        return $this->extractErrorMessageFromPayload($data);
    }

    private function bitnobProvisioningFailureMessage(?string $reason): string
    {
        if ($reason !== null && trim($reason) !== '') {
            return __('Bitnob declined card creation: :reason', ['reason' => $reason]);
        }

        return __('Bitnob declined card creation after provisioning. The failed provider response was saved for review and the issuing fee was not charged. Check Bitnob USD card wallet balance, card program/country enablement, and public KYC image URLs, then retry.');
    }

    private function failedCardResponse(array $cardData, VirtualCardRequest $request, ?string $reason): array
    {
        $message = $this->bitnobProvisioningFailureMessage($reason);

        return [
            'id'           => (string) ($cardData['id'] ?? $cardData['cardId'] ?? Str::uuid()->toString()),
            'last4'        => $this->extractLast4($cardData),
            'brand'        => $cardData['cardBrand'] ?? ($cardData['card_brand'] ?? ($cardData['brand'] ?? 'visa')),
            'expiry_month' => null,
            'expiry_year'  => null,
            'status'       => 'failed',
            'meta'         => array_filter([
                'card_id'          => (string) ($cardData['id'] ?? $cardData['cardId'] ?? ''),
                'bitnob_card_id'   => (string) ($cardData['id'] ?? $cardData['cardId'] ?? ''),
                'bitnob_user_id'   => $cardData['cardUserId'] ?? ($cardData['customer_id'] ?? ($cardData['customerId'] ?? null)),
                'reference'        => $cardData['reference']  ?? null,
                'card_type'        => $cardData['cardType']   ?? ($cardData['card_type'] ?? 'virtual'),
                'balance'          => isset($cardData['balance']) ? (float) $cardData['balance'] : 0,
                'currency'         => $cardData['currency']  ?? ($request->wallet?->currency?->code ?? 'USD'),
                'created_at'       => $cardData['createdAt'] ?? ($cardData['created_at'] ?? now()->toIso8601String()),
                'failure_reason'   => $message,
                'provider_details' => $cardData,
                'raw'              => $cardData,
            ], fn ($value) => $value !== null && $value !== ''),
            'raw' => $cardData,
        ];
    }

    private function redactLogContext(mixed $value): mixed
    {
        if (is_array($value)) {
            $redacted = [];
            foreach ($value as $key => $item) {
                $keyString      = is_string($key) ? $key : (string) $key;
                $redacted[$key] = $this->isSensitiveLogKey($keyString)
                    ? $this->redactedLogValue($item, $keyString)
                    : $this->redactLogContext($item);
            }

            return $redacted;
        }

        return $value;
    }

    private function isSensitiveLogKey(string $key): bool
    {
        $normalized = strtolower($key);

        return in_array($normalized, [
            'card_number',
            'cardnumber',
            'pan',
            'cvv',
            'cvv2',
            'id_number',
            'idnumber',
            'bvn',
            'userphoto',
            'idimage',
            'token',
            'access_token',
            'authorization',
            'signature',
        ], true)
            || str_contains($normalized, 'secret')
            || str_contains($normalized, 'token')
            || str_contains($normalized, 'signature');
    }

    private function redactedLogValue(mixed $value, string $key): string
    {
        if (! is_scalar($value)) {
            return '[redacted]';
        }

        $text = (string) $value;
        if ($text === '') {
            return '[empty]';
        }

        if (in_array(strtolower($key), ['userphoto', 'idimage'], true)) {
            return '[url:'.parse_url($text, PHP_URL_HOST).']';
        }

        return strlen($text) <= 4
            ? '[redacted]'
            : substr($text, 0, 2).'...'.substr($text, -2);
    }

    private function extractErrorMessageFromPayload(?array $payload): ?string
    {
        if ($payload === null) {
            return null;
        }

        foreach (['message', 'error', 'errorMessage', 'failureReason', 'failure_reason', 'reason', 'details', 'detail', 'msg'] as $key) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }
            $value = $payload[$key];
            if (is_string($value)) {
                $value = trim($value);
                if ($value !== '') {
                    return $value;
                }
            } elseif (is_array($value)) {
                $nested = $this->extractErrorMessageFromPayload($value);
                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        if (array_key_exists('errors', $payload) && is_array($payload['errors'])) {
            foreach ($payload['errors'] as $error) {
                if (is_string($error)) {
                    $error = trim($error);
                    if ($error !== '') {
                        return $error;
                    }
                } elseif (is_array($error)) {
                    $nested = $this->extractErrorMessageFromPayload($error);
                    if ($nested !== null) {
                        return $nested;
                    }
                }
            }
        }

        if (array_key_exists('data', $payload) && is_array($payload['data'])) {
            return $this->extractErrorMessageFromPayload($payload['data']);
        }

        return null;
    }

    private function normalizePhone(Cardholders $cardholder): ?string
    {
        $country = (string) ($cardholder->phone_country_code ?? '');
        $mobile  = (string) ($cardholder->mobile ?? '');
        if ($mobile === '' && $country === '') {
            return null;
        }

        return trim($country.$mobile);
    }

    private function resolveUserPhoto(Cardholders $cardholder, array $docs): ?string
    {
        $documentKeys = [
            'userPhoto', 'user_photo', 'selfie', 'selfie_photo', 'selfie_image',
            'profile_photo', 'face_photo', 'face_image', 'live_photo',
            'passport_photo', 'photo',
        ];

        foreach ($documentKeys as $key) {
            $resolved = $this->resolveImageUrl($docs[$key] ?? null);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        // Fallback: any non-id image attached to KYC docs.
        foreach ($docs as $key => $value) {
            if (! is_string($key) || preg_match('/id|document|license|address|proof/i', $key)) {
                continue;
            }
            $resolved = $this->resolveImageUrl($value);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        // Final fallback: the user account avatar.
        if ($cardholder->relationLoaded('user') || $cardholder->user) {
            $avatar   = $cardholder->user?->avatar ?? $cardholder->user?->avatar_alt ?? null;
            $resolved = $this->resolveImageUrl($avatar);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    private function resolveIdImage(array $docs): ?string
    {
        $documentKeys = [
            'idImage', 'id_image', 'id_document', 'document_image', 'document_photo',
            'id_card', 'id_card_front', 'national_id', 'national_id_front',
            'passport', 'passport_image', 'passport_photo', 'drivers_license',
            'drivers_license_front', 'license', 'license_front',
        ];

        foreach ($documentKeys as $key) {
            $resolved = $this->resolveImageUrl($docs[$key] ?? null);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        foreach ($docs as $key => $value) {
            if (! is_string($key) || ! preg_match('/id|document|passport|license|nin/i', $key)) {
                continue;
            }
            $resolved = $this->resolveImageUrl($value);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    private function resolveImageUrl(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        // Already an absolute URL — pass through when it is a raster image.
        if (preg_match('#^https?://#i', $value)) {
            return $this->isRasterImagePath($value) ? $value : null;
        }

        // Only image extensions are accepted by Bitnob's KYC ingest.
        if (! preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $value)) {
            return null;
        }

        return asset($value);
    }

    private function isPublicRasterImageUrl(string $url): bool
    {
        if (! preg_match('#^https?://#i', $url) || ! $this->isRasterImagePath($url)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host !== ''
            && ! in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)
            && ! str_ends_with($host, '.test')
            && ! str_ends_with($host, '.local');
    }

    private function isRasterImagePath(string $value): bool
    {
        $path = (string) (parse_url($value, PHP_URL_PATH) ?: $value);

        return (bool) preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $path);
    }

    private function extractApiErrorMessage(GuzzleException $e): string
    {
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;
        if ($response) {
            try {
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $message = $this->extractErrorMessageFromPayload($data);
                    if ($message !== null) {
                        return $message;
                    }
                }
            } catch (\Throwable $t) {
                // fall through to message scraping below
            }
        }

        $msg = $e->getMessage();
        if (preg_match('/\{.*\}/s', $msg, $m)) {
            $data = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $message = $this->extractErrorMessageFromPayload($data);
                if ($message !== null) {
                    return $message;
                }
            }
        }

        return $msg;
    }

    private function extractLast4(array $data): ?string
    {
        $candidates = [
            'last4', 'last_4', 'last_four', 'lastFour', 'lastFourDigits', 'last_digits', 'lastDigits',
            'ending', 'suffix', 'cardSuffix', 'cardLast4', 'card_number', 'cardNumber',
            'number', 'pan', 'masked_pan', 'maskedPan', 'masked_number', 'maskedCardNumber',
        ];
        foreach ($candidates as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $val = $data[$key];
            if (! (is_int($val) || is_float($val) || (is_string($val) && $val !== ''))) {
                continue;
            }
            $digits = preg_replace('/\D+/', '', (string) $val);
            if ($digits !== null && strlen($digits) >= 4) {
                return substr($digits, -4);
            }
        }

        foreach ($data as $v) {
            if (is_array($v)) {
                $found = $this->extractLast4($v);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function extractExpiry(array $data): array
    {
        $m = $data['expiry_month'] ?? ($data['expiryMonth'] ?? ($data['expMonth'] ?? null));
        $y = $data['expiry_year']  ?? ($data['expiryYear'] ?? ($data['expYear'] ?? null));
        if ($m && $y) {
            return $this->normalizeExpiryParts($m, $y);
        }

        $keys = ['expiry', 'expiration', 'expiryDate', 'expirationDate', 'exp', 'expDate', 'validThru', 'validThrough', 'validity'];
        foreach ($keys as $k) {
            if (empty($data[$k]) || ! is_string($data[$k])) {
                continue;
            }
            $val   = (string) $data[$k];
            $parts = preg_split('/[^\d]+/', $val);
            $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));
            if (count($parts) >= 2) {
                return $this->normalizeExpiryParts($parts[0], $parts[1]);
            }
            $digits = preg_replace('/\D+/', '', $val);
            if ($digits !== null && strlen($digits) >= 4) {
                return $this->normalizeExpiryParts(substr($digits, 0, 2), substr($digits, 2, 2));
            }
        }

        foreach ($data as $v) {
            if (is_array($v)) {
                [$mm, $yy] = $this->extractExpiry($v);
                if ($mm && $yy) {
                    return [$mm, $yy];
                }
            }
        }

        return [null, null];
    }

    private function normalizeExpiryParts(mixed $first, mixed $second): array
    {
        $firstDigits  = preg_replace('/\D+/', '', (string) $first);
        $secondDigits = preg_replace('/\D+/', '', (string) $second);

        if ($firstDigits === '' || $secondDigits === '') {
            return [null, null];
        }

        if (strlen($firstDigits) >= 4 && (int) $firstDigits > 12) {
            [$firstDigits, $secondDigits] = [$secondDigits, $firstDigits];
        }

        $month = str_pad(substr($firstDigits, 0, 2), 2, '0', STR_PAD_LEFT);
        $year  = $secondDigits;

        if (strlen($year) === 2) {
            $year = (string) (2000 + (int) $year);
        } elseif (strlen($year) > 4) {
            $year = substr($year, -4);
        }

        if ((int) $month < 1 || (int) $month > 12) {
            return [null, null];
        }

        return [$month, $year];
    }

    private function mapBitnobStatus(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'approved' => 'active',
            'pending' => 'pending',
            'frozen', 'suspended', 'blocked' => 'blocked',
            'expired'    => 'expired',
            'terminated' => 'failed',
            'failed', 'failure' => 'failed',
            'disabled', 'declined', 'rejected', 'inactive' => 'inactive',
            default => 'inactive',
        };
    }

    private function mapIdType(string $idType): string
    {
        return match (strtoupper($idType)) {
            'NIN'         => 'NIN',
            'NATIONAL_ID' => 'NATIONAL_ID',
            'PASSPORT'    => 'PASSPORT',
            'DRIVERS_LICENSE', 'DL' => 'DRIVERS_LICENSE',
            'PVC', 'VOTERS_ID', 'VOTER_ID' => 'PVC',
            'ECOWAS_ID'        => 'ECOWAS_ID',
            'RESIDENCE_PERMIT' => 'RESIDENCE_PERMIT',
            default            => 'NATIONAL_ID',
        };
    }
}
