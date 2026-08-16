<?php

namespace App\Services\Payment\Stripe;

use App\Enums\TrxStatus;
use App\Models\PaymentGateway;
use App\Models\Transaction as TransactionModel;
use App\Services\Payment\PaymentGateway as PaymentGatewayInterface;
use App\Support\WithdrawFieldNormalizer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Payout;
use Stripe\Stripe;
use Stripe\Webhook;
use Transaction;
use UnexpectedValueException;

class StripePaymentGateway implements PaymentGatewayInterface
{
    private const array ZERO_DECIMAL_CURRENCIES = [
        'bif',
        'clp',
        'djf',
        'gnf',
        'jpy',
        'kmf',
        'krw',
        'mga',
        'pyg',
        'rwf',
        'ugx',
        'vnd',
        'vuv',
        'xaf',
        'xof',
        'xpf',
    ];

    protected array $credentials;

    private string $secretKey;

    public function __construct()
    {
        $this->credentials = PaymentGateway::getCredentials('stripe');
        $this->secretKey   = $this->credential('stripe_secret') ?: $this->credential('secret_key');

        if ($this->secretKey === '') {
            throw new Exception('Stripe secret key is not configured.');
        }

        Stripe::setApiKey($this->secretKey);
    }

    public function deposit($amount, $currency, $trxId)
    {
        // Create a new Checkout session for payment
        $session = CheckoutSession::create([
            'line_items' => [[
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => [
                        'name' => setting('site_title'),
                    ],
                    'unit_amount' => (int) ($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'                => 'payment',
            'success_url'         => route('status.success', ['trx_id' => $trxId]),
            'cancel_url'          => route('status.cancel', ['trx_id' => $trxId]),
            'client_reference_id' => $trxId, // Pass the transaction ID here
        ]);

        // Redirect to the Stripe checkout page
        return $session->url;
    }

    /**
     * @param  array<string, mixed>|string|null $withdrawCredential
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function withdraw($amount, $currency, $trxId, $withdrawCredential): array
    {
        $currency    = strtolower((string) $currency);
        $credentials = is_array($withdrawCredential)
            ? $withdrawCredential
            : WithdrawFieldNormalizer::values($withdrawCredential);

        $payload = [
            'amount'      => $this->amountToSubunit((float) $amount, $currency),
            'currency'    => $currency,
            'description' => 'DigiKash withdrawal '.$trxId,
            'metadata'    => [
                'trx_id' => $trxId,
                'source' => 'wallet-withdraw',
            ],
        ];

        $this->putIfPresent($payload, 'destination', $this->firstCredentialValue($credentials, ['destination', 'destination_id', 'external_account_id']));
        $this->putIfPresent($payload, 'method', $this->firstCredentialValue($credentials, ['method']));
        $this->putIfPresent($payload, 'source_type', $this->firstCredentialValue($credentials, ['source_type']));
        $this->putIfPresent($payload, 'statement_descriptor', $this->statementDescriptor($credentials));

        $options          = [];
        $connectedAccount = $this->firstCredentialValue($credentials, ['connected_account_id', 'stripe_account', 'account_id']);

        if ($connectedAccount !== null) {
            $options['stripe_account'] = $connectedAccount;
        }

        try {
            $payout = Payout::create($payload, $options);
        } catch (ApiErrorException $e) {
            Log::error('Stripe payout failed', [
                'transaction_id' => $trxId,
                'status_code'    => $e->getHttpStatus(),
                'error'          => $e->getMessage(),
            ]);

            throw new Exception('Stripe payout failed: '.$e->getMessage(), previous: $e);
        }

        $response = $payout->toArray();

        return [
            'reference' => (string) ($response['id'] ?? $trxId),
            'status'    => strtolower((string) ($response['status'] ?? 'pending')),
            'data'      => $response,
        ];
    }

    public function handleIPN(Request $request): JsonResponse
    {
        // Retrieve the request's body and parse it
        $payload         = $request->getContent();
        $sig_header      = $request->headers->get('Stripe-Signature');
        $endpoint_secret = $this->credentials['webhook_secret'];

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $eventData = $this->stripeObjectToArray($event);
        $eventType = (string) data_get($eventData, 'type');

        // Handle the event based on its type
        switch ($eventType) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted(data_get($eventData, 'data.object'));
                break;

            case 'payout.paid':
                $this->handlePayoutCompleted(data_get($eventData, 'data.object'));
                break;

            case 'payout.failed':
            case 'payout.canceled':
                $this->handlePayoutFailed(data_get($eventData, 'data.object'));
                break;

            case 'payout.updated':
                $this->handlePayoutUpdated(data_get($eventData, 'data.object'));
                break;

            default:
                // Log or handle unexpected event types
                Log::info('Unhandled Stripe event type: '.$eventType);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleCheckoutSessionCompleted($session): void
    {
        $txn = data_get($session, 'client_reference_id');

        if (is_string($txn) && $txn !== '') {
            Transaction::completeTransaction($txn);
        }
    }

    private function handlePayoutCompleted(mixed $payout): void
    {
        $payoutData  = $this->stripeObjectToArray($payout);
        $transaction = $this->findPayoutTransaction($payoutData);

        if (! $transaction) {
            return;
        }

        $this->recordPayoutWebhook($transaction, $payoutData);

        if ($transaction->status === TrxStatus::PENDING) {
            Transaction::completeTransaction($transaction->trx_id, __('Stripe payout paid.'));
        }
    }

    private function handlePayoutFailed(mixed $payout): void
    {
        $payoutData  = $this->stripeObjectToArray($payout);
        $transaction = $this->findPayoutTransaction($payoutData);

        if (! $transaction) {
            return;
        }

        $this->recordPayoutWebhook($transaction, $payoutData);

        if ($transaction->status === TrxStatus::PENDING) {
            Transaction::cancelTransaction(
                $transaction->trx_id,
                (string) ($payoutData['failure_message'] ?? __('Stripe payout failed or was canceled.')),
                true
            );
        }
    }

    private function handlePayoutUpdated(mixed $payout): void
    {
        $payoutData = $this->stripeObjectToArray($payout);
        $status     = strtolower((string) ($payoutData['status'] ?? ''));

        if ($status === 'paid') {
            $this->handlePayoutCompleted($payout);
        } elseif (in_array($status, ['failed', 'canceled', 'cancelled'], true)) {
            $this->handlePayoutFailed($payout);
        }
    }

    /**
     * @param array<string, mixed> $payout
     */
    private function findPayoutTransaction(array $payout): ?TransactionModel
    {
        $payoutId = (string) ($payout['id'] ?? '');
        $trxId    = (string) data_get($payout, 'metadata.trx_id', '');

        if ($payoutId === '' && $trxId === '') {
            return null;
        }

        return TransactionModel::query()
            ->where(function ($query) use ($payoutId, $trxId) {
                if ($trxId !== '') {
                    $query->where('trx_id', $trxId);
                }

                if ($payoutId !== '') {
                    $query->orWhere('trx_reference', $payoutId)
                        ->orWhere('trx_data->stripe_withdraw->reference', $payoutId)
                        ->orWhere('trx_data->stripe_withdraw->data->id', $payoutId);
                }
            })
            ->latest('id')
            ->first();
    }

    /**
     * @param array<string, mixed> $payout
     */
    private function recordPayoutWebhook(TransactionModel $transaction, array $payout): void
    {
        $trxData                          = $transaction->trx_data ?? [];
        $trxData['stripe_payout_webhook'] = $payout;

        $transaction->update(['trx_data' => $trxData]);
    }

    /**
     * @param array<string, mixed> $credentials
     * @param array<int, string>   $keys
     */
    private function firstCredentialValue(array $credentials, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($credentials[$key]) && is_scalar($credentials[$key])) {
                $value = trim((string) $credentials[$key]);

                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function putIfPresent(array &$payload, string $key, ?string $value): void
    {
        if ($value !== null) {
            $payload[$key] = $value;
        }
    }

    /**
     * @param array<string, mixed> $credentials
     */
    private function statementDescriptor(array $credentials): ?string
    {
        $descriptor = $this->firstCredentialValue($credentials, ['statement_descriptor']);

        return $descriptor !== null ? substr($descriptor, 0, 22) : null;
    }

    private function amountToSubunit(float $amount, string $currency): int
    {
        return (int) round($amount * (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true) ? 1 : 100));
    }

    private function credential(string $key): string
    {
        if (! isset($this->credentials[$key]) || ! is_scalar($this->credentials[$key])) {
            return '';
        }

        $value = trim((string) $this->credentials[$key]);

        return $this->isPlaceholderValue($value) ? '' : $value;
    }

    private function isPlaceholderValue(string $value): bool
    {
        return in_array(strtolower($value), [
            '',
            'stripe_secret',
            'secret_key',
            'sk_test_xxx',
            'your_secret_key',
        ], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function stripeObjectToArray(mixed $value): array
    {
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode(json_encode($value), true);

        return is_array($decoded) ? $decoded : [];
    }
}
