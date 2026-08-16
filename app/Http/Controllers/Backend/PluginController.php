<?php

namespace App\Http\Controllers\Backend;

use App\Models\Plugin;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Throwable;

class PluginController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|pluginType|edit|update|test' => 'plugins-manage',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return $this->getPluginsView();
    }

    public function pluginType(string $plugin_type): View
    {
        return $this->getPluginsView($plugin_type);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $plugin              = Plugin::find($id);
        $plugin->credentials = json_decode($plugin->credentials, true);
        $plugin->credentials = is_array($plugin->credentials) ? $plugin->credentials : [];

        return view('backend.settings.plugin.partials.__manage_append', compact('plugin'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'credentials' => 'required|array',
            'fields'      => 'nullable|array',
        ]);

        // Handle optional fields and encode them to JSON if present
        if (isset($validatedData['fields'])) {
            $validatedData['credentials']['fields'] = $validatedData['fields'];
        }

        // Encode the credentials array to JSON
        $credentials = json_encode($validatedData['credentials']);

        // Find and update the plugin
        $plugin = Plugin::findOrFail($id);
        $plugin->update([
            'credentials' => $credentials,
            'status'      => $request->input('status', 0), // Set status to 0 if not provided
        ]);

        if ($plugin->type == 'notification') {
            Artisan::call('config:cache');
        }

        // Notify success and redirect back
        notifyEvs('success', __('Info Updated Successfully'));

        return redirect()->back();
    }

    public function test(Plugin $plugin, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credentials' => ['required', 'array'],
            'fields'      => ['nullable', 'array'],
        ]);

        $credentials = $validated['credentials'];

        if (isset($validated['fields'])) {
            $credentials['fields'] = $validated['fields'];
        }

        $missingKeys = $this->missingCredentialKeys($credentials);

        if ($missingKeys !== []) {
            return response()->json([
                'status'  => 'error',
                'message' => __('Missing credentials: :fields', ['fields' => implode(', ', $missingKeys)]),
            ], 422);
        }

        try {
            return response()->json($this->testPluginCredentials($plugin, $credentials));
        } catch (Throwable $exception) {
            return response()->json([
                'status'  => 'error',
                'message' => __('Connection test failed: :message', ['message' => $exception->getMessage()]),
            ], 422);
        }
    }

    private function getPluginsView(?string $selectedPluginType = null): View
    {
        $plugins = Plugin::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('backend.settings.plugin.index', compact('plugins', 'selectedPluginType'));
    }

    private function missingCredentialKeys(array $credentials): array
    {
        $missing = [];

        foreach ($credentials as $key => $value) {
            if ($key === 'fields' || $value === '0' || $value === '1' || is_bool($value)) {
                continue;
            }

            if ($this->isBlankCredential($value)) {
                $missing[] = ucwords(str_replace('_', ' ', (string) $key));
            }
        }

        return $missing;
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
                'access_token',
                'account_sid',
                'auth_token',
                'page_id',
                'property_id',
                'widget_id',
                'recaptcha_key',
                'recaptcha_secret',
                'pusher_app_id',
                'pusher_app_key',
                'pusher_app_secret',
                'pusher_app_cluster',
            ], true);
    }

    private function testPluginCredentials(Plugin $plugin, array $credentials): array
    {
        return match ($plugin->code) {
            'google-recaptcha' => $this->testRecaptchaCredentials($credentials),
            'ipinfo'           => $this->testIpinfoCredentials($credentials),
            'currencylayer'    => $this->testCurrencylayerCredentials($credentials),
            'coinmarketcap'    => $this->testCoinmarketcapCredentials($credentials),
            'pusher'           => $this->testPusherCredentials($credentials),
            'twilio'           => $this->testTwilioCredentials($credentials),
            default            => [
                'status'  => 'warning',
                'message' => __('Automated API check is not available for :plugin yet. Required credential fields are filled.', [
                    'plugin' => $plugin->name,
                ]),
            ],
        };
    }

    private function testRecaptchaCredentials(array $credentials): array
    {
        $response = Http::asForm()
            ->timeout(15)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => (string) ($credentials['recaptcha_secret'] ?? ''),
                'response' => 'digikash-credential-check',
            ]);

        $errors = $response->json('error-codes', []);

        if (is_array($errors) && in_array('invalid-input-secret', $errors, true)) {
            return [
                'status'  => 'error',
                'message' => __('Google reCAPTCHA rejected the secret key.'),
            ];
        }

        return [
            'status'  => 'success',
            'message' => __('Google reCAPTCHA secret responded correctly. Site key is present.'),
        ];
    }

    private function testIpinfoCredentials(array $credentials): array
    {
        $response = Http::acceptJson()
            ->timeout(15)
            ->get('https://ipinfo.io/json', [
                'token' => (string) ($credentials['access_token'] ?? ''),
            ]);

        return $this->pluginTestResponse($response, 'IPinfo');
    }

    private function testCurrencylayerCredentials(array $credentials): array
    {
        $response = Http::acceptJson()
            ->timeout(15)
            ->get('http://api.currencylayer.com/live', [
                'access_key' => (string) ($credentials['api_key'] ?? ''),
                'currencies' => 'USD',
            ]);

        if ($response->json('success') === true) {
            return [
                'status'  => 'success',
                'message' => __('Currencylayer credential validation passed. Live rates endpoint responded.'),
            ];
        }

        return [
            'status'  => 'error',
            'message' => __('Currencylayer credential validation failed: :message', [
                'message' => $response->json('error.info') ?: $this->responseMessage($response),
            ]),
        ];
    }

    private function testCoinmarketcapCredentials(array $credentials): array
    {
        $response = Http::acceptJson()
            ->withHeaders(['X-CMC_PRO_API_KEY' => (string) ($credentials['api_key'] ?? '')])
            ->timeout(15)
            ->get('https://pro-api.coinmarketcap.com/v1/cryptocurrency/map', [
                'listing_status' => 'active',
                'start'          => 1,
                'limit'          => 1,
            ]);

        return $this->pluginTestResponse($response, 'CoinMarketCap');
    }

    private function testPusherCredentials(array $credentials): array
    {
        $appId   = trim((string) ($credentials['pusher_app_id'] ?? ''));
        $key     = trim((string) ($credentials['pusher_app_key'] ?? ''));
        $secret  = trim((string) ($credentials['pusher_app_secret'] ?? ''));
        $cluster = trim((string) ($credentials['pusher_app_cluster'] ?? 'mt1'));
        $path    = '/apps/'.$appId.'/channels';
        $params  = [
            'auth_key'       => $key,
            'auth_timestamp' => time(),
            'auth_version'   => '1.0',
        ];

        ksort($params);

        $query                    = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $params['auth_signature'] = hash_hmac('sha256', "GET\n{$path}\n{$query}", $secret);

        $response = Http::acceptJson()
            ->timeout(15)
            ->get('https://api-'.$cluster.'.pusher.com'.$path, $params);

        return $this->pluginTestResponse($response, 'Pusher');
    }

    private function testTwilioCredentials(array $credentials): array
    {
        $accountSid = trim((string) ($credentials['account_sid'] ?? ''));

        $response = Http::acceptJson()
            ->withBasicAuth($accountSid, (string) ($credentials['auth_token'] ?? ''))
            ->timeout(15)
            ->get('https://api.twilio.com/2010-04-01/Accounts/'.$accountSid.'.json');

        return $this->pluginTestResponse($response, 'Twilio');
    }

    private function pluginTestResponse(Response $response, string $provider): array
    {
        if ($response->successful()) {
            return [
                'status'  => 'success',
                'message' => __(':provider credential validation passed. API connection is reachable.', [
                    'provider' => $provider,
                ]),
            ];
        }

        return [
            'status'  => 'error',
            'message' => __(':provider credential validation failed (:status): :message', [
                'provider' => $provider,
                'status'   => $response->status(),
                'message'  => $this->responseMessage($response),
            ]),
        ];
    }

    private function responseMessage(Response $response): string
    {
        $message = $response->json('message')
            ?? $response->json('error')
            ?? $response->json('error.message')
            ?? $response->json('status.error_message');

        if (is_array($message)) {
            $message = implode(', ', array_filter($message));
        }

        $body = trim((string) ($message ?: $response->body()));

        return $body !== '' ? $body : __('Provider rejected the credential check.');
    }
}
