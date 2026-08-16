<?php

namespace App\Http\Controllers\Backend;

use App\Models\PaymentGateway;
use App\Traits\FileManageTrait;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class PaymentGatewayController extends BaseController
{
    use FileManageTrait;

    public static function permissions(): array
    {
        return [
            'index'            => 'payment-gateway-list',
            'edit|update|test' => 'payment-gateway-configure',
        ];
    }

    public function index()
    {
        $paymentGateways = PaymentGateway::paginate(12);
        $gatewayStats    = $this->gatewayStats();

        return view('backend.payment_gateway.index', compact('paymentGateways', 'gatewayStats'));
    }

    private function gatewayStats(): array
    {
        $gateways = PaymentGateway::query()
            ->select(['id', 'code', 'status', 'withdraw_field', 'currencies'])
            ->get();

        return [
            'active'     => $gateways->where('status', true)->count(),
            'withdraw'   => $gateways->filter(fn (PaymentGateway $gateway): bool => $gateway->withdraw_available)->count(),
            'currencies' => $gateways
                ->flatMap(fn (PaymentGateway $gateway): array => $gateway->currencies ?? [])
                ->filter()
                ->map(fn (mixed $currency): string => strtoupper((string) $currency))
                ->unique()
                ->count(),
        ];
    }

    public function edit($id)
    {
        $paymentGateway = PaymentGateway::getById($id);

        return view('backend.payment_gateway.edit', compact('paymentGateway'))->render();
    }

    public function update($id, Request $request)
    {

        $validated = $request->validate([
            'name'        => 'required',
            'credentials' => 'required',
            'status'      => 'boolean',
        ]);

        $paymentGateway           = PaymentGateway::with(['depositMethods', 'withdrawMethods'])->find($id);
        $validated['status']      = $request->boolean('status');
        $validated['credentials'] = $this->normalizeGatewayCredentials(
            $paymentGateway->code,
            $validated['credentials']
        );

        if (! $validated['status']) {
            $paymentGateway->depositMethods()->update(['status' => false]);
            $paymentGateway->withdrawMethods()->update(['status' => false]);
        }

        $paymentGateway->update($validated);

        notifyEvs('success', __('Payment Gateway Updated Successfully'));

        return redirect()->back();
    }

    public function test(PaymentGateway $gateway, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credentials' => ['required', 'array'],
            'test_mode'   => ['nullable', 'in:current,live,sandbox'],
        ]);

        $credentials = $this->normalizeGatewayCredentials($gateway->code, $validated['credentials']);
        $testMode    = $validated['test_mode'] ?? 'current';
        $credentials = $this->credentialsForTestMode($credentials, $testMode);
        $credentials = $this->credentialsForGatewayTest($gateway, $credentials);
        $missingKeys = $this->missingCredentialKeys($gateway->code, $credentials);

        if ($missingKeys !== []) {
            return response()->json([
                'status'  => 'error',
                'message' => __('Missing credentials: :fields', ['fields' => implode(', ', $missingKeys)]),
            ], 422);
        }

        try {
            return response()->json($this->testGatewayCredentials($gateway->code, $credentials, $testMode));
        } catch (Throwable $exception) {
            return response()->json([
                'status'  => 'error',
                'message' => __('Gateway credential test failed: :message', ['message' => $exception->getMessage()]),
            ], 422);
        }
    }

    private function normalizeGatewayCredentials(string $code, array $credentials): array
    {
        if ($code !== 'bitnob') {
            return $credentials;
        }

        unset($credentials['webhook_url']);

        if ($this->isBlankCredential($credentials['hmac_key'] ?? null) && ! $this->isBlankCredential($credentials['client_secret'] ?? null)) {
            $credentials['hmac_key'] = $credentials['client_secret'];
        }

        unset($credentials['client_secret']);

        return $credentials;
    }

    private function credentialsForTestMode(array $credentials, string $testMode): array
    {
        if ($testMode === 'sandbox') {
            $credentials['sandbox'] = '1';
        }

        if ($testMode === 'live') {
            $credentials['sandbox'] = '0';
        }

        return $credentials;
    }

    private function credentialsForGatewayTest(PaymentGateway $gateway, array $credentials): array
    {
        if ($gateway->code === 'paymob' && blank($credentials['currency'] ?? null)) {
            $credentials['currency'] = $this->paymobTestCurrency($gateway, $credentials);
        }

        return $credentials;
    }

    private function isBlankCredential(mixed $value): bool
    {
        if (! is_scalar($value)) {
            return true;
        }

        $normalized = strtolower(trim((string) $value));

        return $normalized === ''
            || in_array($normalized, [
                'api_key',
                'client_id',
                'client_secret',
                'hmac_key',
                'hmac_secret',
                'integration_id',
                'public_key',
                'payment_methods',
                'secret',
                'secret_key',
                'stripe_secret',
                'your_api_key',
                'your_client_id',
                'your_secret_key',
            ], true);
    }

    private function missingCredentialKeys(string $code, array $credentials): array
    {
        if ($code === 'paymob') {
            return $this->missingPaymobCredentialKeys($credentials);
        }

        $missing = [];

        foreach ($credentials as $key => $value) {
            if ($value === '0' || $value === '1' || is_bool($value)) {
                continue;
            }

            if ($this->isBlankCredential($value)) {
                $missing[] = ucwords(str_replace('_', ' ', (string) $key));
            }
        }

        return $missing;
    }

    private function testGatewayCredentials(string $code, array $credentials, string $testMode): array
    {
        return match ($code) {
            'stripe'      => $this->testStripeCredentials($credentials, $testMode),
            'paypal'      => $this->testPaypalCredentials($credentials, $testMode),
            'paystack'    => $this->testPaystackCredentials($credentials, $testMode),
            'moneroo'     => $this->testMonerooCredentials($credentials, $testMode),
            'paymob'      => $this->testPaymobCredentials($credentials, $testMode),
            'flutterwave' => $this->testFlutterwaveCredentials($credentials, $testMode),
            'mollie'      => $this->testMollieCredentials($credentials, $testMode),
            'coinbase'    => $this->testCoinbaseCredentials($credentials, $testMode),
            'nowpayments' => $this->testNowpaymentsCredentials($credentials, $testMode),
            default       => [
                'status'  => 'warning',
                'message' => __('Credential validation is not automated for :gateway yet. Required fields are filled for :mode mode.', [
                    'gateway' => title($code),
                    'mode'    => $this->testModeLabel($credentials, $testMode),
                ]),
            ],
        };
    }

    private function testStripeCredentials(array $credentials, string $testMode): array
    {
        $response = Http::withToken((string) ($credentials['stripe_secret'] ?? $credentials['secret_key'] ?? ''))
            ->acceptJson()
            ->timeout(15)
            ->get('https://api.stripe.com/v1/balance');

        return $this->gatewayTestResponse($response, 'Stripe', $this->testModeLabel($credentials, $testMode));
    }

    private function testPaypalCredentials(array $credentials, string $testMode): array
    {
        $baseUrl = (string) ($credentials['sandbox'] ?? '0') === '1'
            ? 'https://api.sandbox.paypal.com'
            : 'https://api.paypal.com';

        $response = Http::asForm()
            ->withBasicAuth((string) ($credentials['client_id'] ?? ''), (string) ($credentials['client_secret'] ?? ''))
            ->acceptJson()
            ->timeout(15)
            ->post($baseUrl.'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        return $this->gatewayTestResponse($response, 'PayPal', $this->testModeLabel($credentials, $testMode));
    }

    private function testPaystackCredentials(array $credentials, string $testMode): array
    {
        $response = Http::withToken((string) ($credentials['secret_key'] ?? ''))
            ->acceptJson()
            ->timeout(15)
            ->get('https://api.paystack.co/bank');

        return $this->gatewayTestResponse($response, 'Paystack', $this->testModeLabel($credentials, $testMode));
    }

    private function testMonerooCredentials(array $credentials, string $testMode): array
    {
        $mode  = $this->testModeLabel($credentials, $testMode);
        $token = $this->firstNonBlankCredential($credentials, ['api_key', 'secret_key', 'api_secret']);

        if ($token === '') {
            return [
                'status'  => 'error',
                'message' => __('Missing credentials: :fields', ['fields' => __('API Key')]),
            ];
        }

        $probeId  = 'DK_HEALTH_'.now()->format('YmdHis');
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(15)
            ->connectTimeout(10)
            ->get("https://api.moneroo.io/v1/payments/{$probeId}");

        if ($response->successful() || $response->status() === 404) {
            return [
                'status'  => 'success',
                'message' => __('Moneroo :mode credential validation passed. API accepted the bearer token; the test transaction lookup was safely ignored.', [
                    'mode' => $mode,
                ]),
            ];
        }

        return [
            'status'  => 'error',
            'message' => __('Moneroo :mode credential validation failed (:status): :message', [
                'mode'    => $mode,
                'status'  => $response->status(),
                'message' => $this->extractGatewayError($response),
            ]),
        ];
    }

    private function testPaymobCredentials(array $credentials, string $testMode): array
    {
        $credentials = $this->normalizePaymobCredentials($credentials);
        $mode        = $this->testModeLabel($credentials, $testMode);

        $response = Http::timeout(20)
            ->connectTimeout(10)
            ->acceptJson()
            ->asJson()
            ->withToken($credentials['secret_key'], 'Token')
            ->post($credentials['base_url'].'/v1/intention/', $this->paymobCredentialTestPayload($credentials, $mode));

        if ($response->successful() && filled($response->json('client_secret'))) {
            return [
                'status'  => 'success',
                'message' => __('Paymob :mode credential validation passed. A test intention was accepted; no customer charge was created.', [
                    'mode' => $mode,
                ]),
            ];
        }

        return $this->paymobTestResponse($response, $mode);
    }

    private function testFlutterwaveCredentials(array $credentials, string $testMode): array
    {
        $response = Http::withToken((string) ($credentials['secret_key'] ?? ''))
            ->acceptJson()
            ->timeout(15)
            ->get('https://api.flutterwave.com/v3/banks/NG');

        return $this->gatewayTestResponse($response, 'Flutterwave', $this->testModeLabel($credentials, $testMode));
    }

    private function testMollieCredentials(array $credentials, string $testMode): array
    {
        $response = Http::withToken((string) ($credentials['api_key'] ?? ''))
            ->acceptJson()
            ->timeout(15)
            ->get('https://api.mollie.com/v2/methods');

        return $this->gatewayTestResponse($response, 'Mollie', $this->testModeLabel($credentials, $testMode));
    }

    private function testCoinbaseCredentials(array $credentials, string $testMode): array
    {
        $response = Http::withHeaders([
            'X-CC-Api-Key' => (string) ($credentials['api_key'] ?? ''),
            'X-CC-Version' => '2018-03-22',
        ])
            ->acceptJson()
            ->timeout(15)
            ->get('https://api.commerce.coinbase.com/checkouts');

        return $this->gatewayTestResponse($response, 'Coinbase Commerce', $this->testModeLabel($credentials, $testMode));
    }

    private function testNowpaymentsCredentials(array $credentials, string $testMode): array
    {
        $sandbox  = (string) ($credentials['sandbox'] ?? '0') === '1';
        $baseUrl  = $sandbox ? 'https://api-sandbox.nowpayments.io' : 'https://api.nowpayments.io';
        $response = Http::withHeaders(['x-api-key' => (string) ($credentials['api_key'] ?? '')])
            ->acceptJson()
            ->timeout(15)
            ->get($baseUrl.'/v1/merchant/coins');

        return $this->gatewayTestResponse($response, 'NOWPayments', $this->testModeLabel($credentials, $testMode));
    }

    private function firstNonBlankCredential(array $credentials, array $keys): string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $credentials) || $this->isBlankCredential($credentials[$key])) {
                continue;
            }

            return trim((string) $credentials[$key]);
        }

        return '';
    }

    private function missingPaymobCredentialKeys(array $credentials): array
    {
        $normalized = $this->normalizePaymobCredentials($credentials);
        $missing    = [];

        foreach (['secret_key', 'public_key', 'base_url'] as $key) {
            if ($this->isBlankCredential($normalized[$key] ?? null)) {
                $missing[] = ucwords(str_replace('_', ' ', $key));
            }
        }

        if ($normalized['payment_methods'] === []) {
            $missing[] = __('Payment Methods');
        }

        return $missing;
    }

    /**
     * @return array{secret_key: string, public_key: string, payment_methods: array<int, int|string>, base_url: string, currency: string, sandbox: string|int|bool}
     */
    private function normalizePaymobCredentials(array $credentials): array
    {
        return [
            'secret_key'      => trim((string) ($credentials['secret_key'] ?? $credentials['api_key'] ?? '')),
            'public_key'      => trim((string) ($credentials['public_key'] ?? '')),
            'payment_methods' => $this->normalizePaymobPaymentMethods(
                $credentials['payment_methods']
                ?? $credentials['integration_ids']
                ?? $credentials['integration_id']
                ?? $credentials['card_integration_id']
                ?? null
            ),
            'base_url' => rtrim(trim((string) ($credentials['base_url'] ?? 'https://ksa.paymob.com')), '/'),
            'currency' => strtoupper(trim((string) ($credentials['currency'] ?? ''))),
            'sandbox'  => $credentials['sandbox'] ?? '0',
        ];
    }

    /**
     * @return array<int, int|string>
     */
    private function normalizePaymobPaymentMethods(mixed $paymentMethods): array
    {
        if (is_string($paymentMethods)) {
            $paymentMethods = preg_split('/[\s,|]+/', $paymentMethods, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (is_numeric($paymentMethods)) {
            $paymentMethods = [$paymentMethods];
        }

        if (! is_array($paymentMethods)) {
            return [];
        }

        return collect($paymentMethods)
            ->map(static fn (mixed $method): int|string => is_numeric($method) ? (int) $method : trim((string) $method))
            ->filter(fn (int|string $method): bool => $method !== '' && $method !== 0 && ! $this->isBlankCredential($method))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function paymobCredentialTestPayload(array $credentials, string $mode): array
    {
        $reference = 'DK_HEALTH_'.now()->format('YmdHis');
        $amount    = 1000;

        return [
            'amount'          => $amount,
            'currency'        => $credentials['currency'] !== '' ? $credentials['currency'] : 'EGP',
            'payment_methods' => $credentials['payment_methods'],
            'items'           => [
                [
                    'name'        => 'DigiKash Credential Health Check',
                    'amount'      => $amount,
                    'description' => 'DigiKash credential validation',
                    'quantity'    => 1,
                ],
            ],
            'billing_data' => [
                'apartment'       => 'NA',
                'email'           => 'credential-check@example.com',
                'floor'           => 'NA',
                'first_name'      => 'DigiKash',
                'street'          => 'NA',
                'building'        => 'NA',
                'phone_number'    => '+966500000000',
                'shipping_method' => 'NA',
                'postal_code'     => 'NA',
                'city'            => 'NA',
                'country'         => 'NA',
                'last_name'       => 'Admin',
                'state'           => 'NA',
            ],
            'special_reference' => $reference,
            'notification_url'  => route('ipn.handle', ['gateway' => 'paymob']),
            'redirection_url'   => route('status.callback', ['gateway' => 'paymob', 'trx' => $reference]),
            'expiration'        => 600,
            'extras'            => [
                'credential_check' => true,
                'mode'             => $mode,
            ],
        ];
    }

    private function paymobTestCurrency(PaymentGateway $gateway, array $credentials): string
    {
        $currencies = collect($gateway->currencies ?? [])
            ->map(fn (mixed $currency): string => strtoupper((string) $currency))
            ->filter()
            ->values();

        $baseUrl = strtolower((string) ($credentials['base_url'] ?? ''));

        if (str_contains($baseUrl, 'ksa') && $currencies->contains('SAR')) {
            return 'SAR';
        }

        if (str_contains($baseUrl, 'oman') && $currencies->contains('OMR')) {
            return 'OMR';
        }

        if ((str_contains($baseUrl, 'uae') || str_contains($baseUrl, 'paymob.ae')) && $currencies->contains('AED')) {
            return 'AED';
        }

        return (string) ($currencies->first() ?: 'EGP');
    }

    private function paymobTestResponse(Response $response, string $mode): array
    {
        $message = $this->extractPaymobError($response);

        return [
            'status'  => 'error',
            'message' => __('Paymob :mode credential validation failed (:status): :message', [
                'mode'    => $mode,
                'status'  => $response->status(),
                'message' => $message,
            ]),
        ];
    }

    private function extractPaymobError(Response $response): string
    {
        $json = $response->json();

        if (is_array($json)) {
            foreach (['detail', 'message', 'error'] as $key) {
                if (isset($json[$key]) && is_scalar($json[$key])) {
                    return (string) $json[$key];
                }
            }

            if (isset($json['errors']) && is_array($json['errors'])) {
                return collect($json['errors'])
                    ->map(function (mixed $messages, string|int $field): string {
                        return is_array($messages)
                            ? $field.': '.implode(', ', array_map('strval', $messages))
                            : $field.': '.(string) $messages;
                    })
                    ->implode(' | ');
            }
        }

        $body = trim($response->body());

        return $body !== '' ? $body : __('Provider rejected the credential check.');
    }

    private function gatewayTestResponse(Response $response, string $gateway, string $mode): array
    {
        if ($response->successful()) {
            return [
                'status'  => 'success',
                'message' => __(':gateway :mode credential validation passed. API credentials look valid.', [
                    'gateway' => $gateway,
                    'mode'    => $mode,
                ]),
            ];
        }

        return [
            'status'  => 'error',
            'message' => __(':gateway :mode credential validation failed (:status): :message', [
                'gateway' => $gateway,
                'mode'    => $mode,
                'status'  => $response->status(),
                'message' => $this->extractGatewayError($response),
            ]),
        ];
    }

    private function extractGatewayError(Response $response): string
    {
        $message = $response->json('message')
            ?? $response->json('error.message')
            ?? $response->json('error')
            ?? $response->json('detail');

        if (is_scalar($message) && trim((string) $message) !== '') {
            return (string) $message;
        }

        $body = trim($response->body());

        return $body !== '' ? $body : __('Provider rejected the credential check.');
    }

    private function testModeLabel(array $credentials, string $testMode): string
    {
        if ($testMode === 'current') {
            return (string) ($credentials['sandbox'] ?? '0') === '1' ? __('sandbox') : __('live');
        }

        return $testMode === 'sandbox' ? __('sandbox') : __('live');
    }

    public function gatewayCurrency($gateway_id)
    {
        $paymentGateway      = PaymentGateway::getById($gateway_id);
        $supportedCurrencies = $paymentGateway->currencies;

        return [
            'view' => view('backend.payment_gateway.partial._currencies_list', compact('supportedCurrencies'))->render(),
        ];
    }
}
