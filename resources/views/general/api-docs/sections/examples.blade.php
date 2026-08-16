<!-- Integration Examples Section -->
<section id="integration-examples" class="content-section">
    <h2>{{ __('Integration Examples') }}</h2>
    <p>{{ __('Production-oriented integration examples for popular platforms and frameworks. Keep gateway selection in Merchant API Config, then sign each API request from your server.') }}</p>

    <!-- Environment Configuration Note -->
    <div class="alert alert-warning mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>{{ __('Environment Configuration') }}:</strong> {{ __('Replace {environment} with sandbox or production, and use corresponding credentials - test_ prefix for sandbox, no prefix for production in your configuration files.') }}
    </div>

    <div class="api-alert api-alert-info api-alert-modern">
        <strong><i class="fas fa-wallet me-2"></i>{{ __('Latest Checkout Rules') }}</strong>
        {{ __('The requested currency must match the merchant setup and active wallet. Gateway visibility is controlled from Merchant API Config; allow_payment_methods only narrows the already eligible gateway list.') }}
    </div>

    <div class="api-alert api-alert-warning api-alert-modern">
        <strong><i class="fas fa-signature me-2"></i>{{ __('Signature Required') }}</strong>
        {{ __('Examples should include X-Timestamp and X-Signature generated with the API Secret for the selected sandbox or production environment. See Authentication for the exact signature payload.') }}
    </div>

    <div class="language-tabs">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#example-laravel">
                    <i class="fa-brands fa-php text-primary me-1" aria-hidden="true"></i>{{ __('PHP (Laravel)') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#example-nodejs">
                    <i class="fa-brands fa-node-js text-success me-1" aria-hidden="true"></i>{{ __('Node.js') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#example-python">
                    <i class="fa-brands fa-python text-primary me-1" aria-hidden="true"></i>{{ __('Python') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#example-curl">
                    <i class="fa-solid fa-terminal text-dark me-1" aria-hidden="true"></i>{{ __('cURL') }}
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="example-laravel">
                <div class="code-block">
                    <pre><code class="php">&lt;?php
// Laravel Integration Service
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class {{ setting('site_title') }}Service
{
    private string $baseUrl;
    private string $merchantKey;
    private string $apiKey;
    private string $environment;

    public function __construct()
    {
        $this->baseUrl = config('{{ strtolower(setting("site_title")) }}.base_url');
        $this->merchantKey = config('{{ strtolower(setting("site_title")) }}.merchant_key');
        $this->apiKey = config('{{ strtolower(setting("site_title")) }}.api_key');
        $this->environment = config('{{ strtolower(setting("site_title")) }}.environment'); // 'sandbox' or 'production'
    }

    public function initiatePayment(array $paymentData): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Environment' => $this->environment,
                'X-Merchant-Key' => $this->merchantKey,
                'X-API-Key' => $this->apiKey,
            ])->post("{$this->baseUrl}/api/v1/initiate-payment", $paymentData);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('{{ setting("site_title") }} API Error: Payment initiation failed');
        } catch (Exception $e) {
            throw new Exception('{{ setting("site_title") }} API Error: ' . $e->getMessage());
        }
    }

    public function verifyPayment(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Environment' => $this->environment,
                'X-Merchant-Key' => $this->merchantKey,
                'X-API-Key' => $this->apiKey,
            ])->get("{$this->baseUrl}/api/v1/verify-payment/{$transactionId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('{{ setting("site_title") }} API Error: Payment verification failed');
        } catch (Exception $e) {
            throw new Exception('{{ setting("site_title") }} API Error: ' . $e->getMessage());
        }
    }
}

// Configuration (config/{{ strtolower(setting("site_title")) }}.php)
return [
    'base_url' => env('{{ strtoupper(setting("site_title")) }}_BASE_URL', '{{ request()->getSchemeAndHttpHost() }}'),
    'environment' => env('{{ strtoupper(setting("site_title")) }}_ENVIRONMENT', 'sandbox'), // sandbox or production
    'merchant_key' => env('{{ strtoupper(setting("site_title")) }}_MERCHANT_KEY'), // Use appropriate prefix
    'api_key' => env('{{ strtoupper(setting("site_title")) }}_API_KEY'), // Use appropriate prefix
];

// Usage in Controller
class PaymentController extends Controller
{
    public function initiatePayment(Request $request, {{ setting('site_title') }}Service ${{ strtolower(setting("site_title")) }})
    {
        $paymentData = [
            'payment_amount' => $request->amount,
            'currency_code' => 'USD',
            'ref_trx' => 'ORDER_' . time(),
            'description' => $request->description,
            'success_redirect' => route('payment.success'),
            'cancel_redirect' => route('payment.cancelled'),
            'ipn_url' => route('webhooks.{{ strtolower(setting("site_title")) }}'),
            'allow_payment_methods' => ['stripe', 'paypal'],
        ];

        try {
            $result = ${{ strtolower(setting("site_title")) }}->initiatePayment($paymentData);
            return redirect($result['payment_url']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}</code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="example-nodejs">
                <div class="code-block">
                    <pre><code class="javascript">// Node.js Integration Service
const axios = require('axios');

class {{ setting('site_title') }}Service {
    constructor() {
        this.baseUrl = process.env.{{ strtoupper(setting("site_title")) }}_BASE_URL || '{{ request()->getSchemeAndHttpHost() }}';
        this.environment = process.env.{{ strtoupper(setting("site_title")) }}_ENVIRONMENT || 'sandbox'; // sandbox or production
        this.merchantKey = process.env.{{ strtoupper(setting("site_title")) }}_MERCHANT_KEY; // Use appropriate prefix
        this.apiKey = process.env.{{ strtoupper(setting("site_title")) }}_API_KEY; // Use appropriate prefix
    }

    async initiatePayment(paymentData) {
        try {
            const response = await axios.post(`${this.baseUrl}/api/v1/initiate-payment`, paymentData, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Environment': this.environment,
                    'X-Merchant-Key': this.merchantKey,
                    'X-API-Key': this.apiKey
                }
            });

            return response.data;
        } catch (error) {
            throw new Error(`{{ setting("site_title") }} API Error: ${error.message}`);
        }
    }

    async verifyPayment(transactionId) {
        try {
            const response = await axios.get(`${this.baseUrl}/api/v1/verify-payment/${transactionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Environment': this.environment,
                    'X-Merchant-Key': this.merchantKey,
                    'X-API-Key': this.apiKey
                }
            });

            return response.data;
        } catch (error) {
            throw new Error(`{{ setting("site_title") }} API Error: ${error.message}`);
        }
    }
}

// Express.js Route Example
const express = require('express');
const app = express();
const {{ strtolower(setting("site_title")) }} = new {{ setting('site_title') }}Service();

app.post('/initiate-payment', async (req, res) => {
    const paymentData = {
        payment_amount: req.body.amount,
        currency_code: 'USD',
        ref_trx: `ORDER_${Date.now()}`,
        description: req.body.description,
        success_redirect: `${req.protocol}://${req.get('host')}/payment/success`,
        cancel_redirect: `${req.protocol}://${req.get('host')}/payment/cancelled`,
        ipn_url: `${req.protocol}://${req.get('host')}/webhooks/{{ strtolower(setting("site_title")) }}`,
        allow_payment_methods: ['stripe', 'paypal'],
    };

    try {
        const result = await {{ strtolower(setting("site_title")) }}.initiatePayment(paymentData);
        res.redirect(result.payment_url);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

module.exports = {{ setting('site_title') }}Service;</code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="example-python">
                <div class="code-block">
                    <pre><code class="python"># Python/Django Integration Service
import os
import requests
from django.conf import settings

class {{ setting('site_title') }}Service:
    def __init__(self):
        self.base_url = getattr(settings, '{{ strtoupper(setting("site_title")) }}_BASE_URL', '{{ request()->getSchemeAndHttpHost() }}')
        self.environment = getattr(settings, '{{ strtoupper(setting("site_title")) }}_ENVIRONMENT', 'sandbox')  # sandbox or production
        self.merchant_key = getattr(settings, '{{ strtoupper(setting("site_title")) }}_MERCHANT_KEY')  # Use appropriate prefix
        self.api_key = getattr(settings, '{{ strtoupper(setting("site_title")) }}_API_KEY')  # Use appropriate prefix

    def initiate_payment(self, payment_data):
        try:
            headers = {
                'Content-Type': 'application/json',
                'X-Environment': self.environment,
                'X-Merchant-Key': self.merchant_key,
                'X-API-Key': self.api_key
            }

            response = requests.post(
                f"{self.base_url}/api/v1/initiate-payment",
                headers=headers,
                json=payment_data,
                timeout=30
            )

            response.raise_for_status()
            return response.json()

        except requests.RequestException as e:
            raise Exception(f'{{ setting("site_title") }} API Error: {str(e)}')

    def verify_payment(self, transaction_id):
        try:
            headers = {
                'Accept': 'application/json',
                'X-Environment': self.environment,
                'X-Merchant-Key': self.merchant_key,
                'X-API-Key': self.api_key
            }

            response = requests.get(
                f"{self.base_url}/api/v1/verify-payment/{transaction_id}",
                headers=headers,
                timeout=30
            )

            response.raise_for_status()
            return response.json()

        except requests.RequestException as e:
            raise Exception(f'{{ setting("site_title") }} API Error: {str(e)}')

# Django Settings Configuration
{{ strtoupper(setting("site_title")) }}_BASE_URL = '{{ request()->getSchemeAndHttpHost() }}'
{{ strtoupper(setting("site_title")) }}_ENVIRONMENT = 'sandbox'  # Change to 'production' for live
{{ strtoupper(setting("site_title")) }}_MERCHANT_KEY = os.environ.get('{{ strtoupper(setting("site_title")) }}_MERCHANT_KEY')  # Use appropriate prefix
{{ strtoupper(setting("site_title")) }}_API_KEY = os.environ.get('{{ strtoupper(setting("site_title")) }}_API_KEY')  # Use appropriate prefix

# Django View Example
from django.shortcuts import redirect
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
import json

{{ strtolower(setting("site_title")) }} = {{ setting('site_title') }}Service()

@csrf_exempt
def initiate_payment(request):
    if request.method == 'POST':
        data = json.loads(request.body)
        
        payment_data = {
            'payment_amount': data['amount'],
            'currency_code': 'USD',
            'ref_trx': f'ORDER_{int(time.time())}',
            'description': data['description'],
            'success_redirect': request.build_absolute_uri('/payment/success/'),
            'cancel_redirect': request.build_absolute_uri('/payment/cancelled/'),
            'ipn_url': request.build_absolute_uri('/webhooks/{{ strtolower(setting("site_title")) }}/'),
            'allow_payment_methods': ['stripe', 'paypal'],
        }

        try:
            result = {{ strtolower(setting("site_title")) }}.initiate_payment(payment_data)
            return redirect(result['payment_url'])
        except Exception as e:
            return JsonResponse({'error': str(e)}, status=500)</code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="example-curl">
                <div class="code-block">
                    <pre><code class="bash"># Environment Variables Setup
export {{ strtoupper(setting("site_title")) }}_ENVIRONMENT="sandbox"  # or "production"
export {{ strtoupper(setting("site_title")) }}_MERCHANT_KEY="test_merchant_your_key"  # or "merchant_your_key" for production
export {{ strtoupper(setting("site_title")) }}_API_KEY="test_your_api_key"  # or "your_api_key" for production

# Initiate Payment
curl -X POST "{{ request()->getSchemeAndHttpHost() }}/api/v1/initiate-payment" \
  -H "Content-Type: application/json" \
  -H "X-Environment: ${{ strtoupper(setting("site_title")) }}_ENVIRONMENT" \
  -H "X-Merchant-Key: ${{ strtoupper(setting("site_title")) }}_MERCHANT_KEY" \
  -H "X-API-Key: ${{ strtoupper(setting("site_title")) }}_API_KEY" \
  -d '{
    "payment_amount": 250.00,
    "currency_code": "USD",
    "ref_trx": "ORDER_12345",
    "description": "Premium Subscription",
    "success_redirect": "https://yoursite.com/payment/success",
    "cancel_redirect": "https://yoursite.com/payment/cancelled",
    "ipn_url": "https://yoursite.com/api/webhooks/{{ strtolower(setting("site_title")) }}",
    "allow_payment_methods": ["stripe", "paypal"]
  }'

# Verify Payment
curl -X GET "{{ request()->getSchemeAndHttpHost() }}/api/v1/verify-payment/TXNQ5V8K2L9N3XM1" \
  -H "Accept: application/json" \
  -H "X-Environment: ${{ strtoupper(setting("site_title")) }}_ENVIRONMENT" \
  -H "X-Merchant-Key: ${{ strtoupper(setting("site_title")) }}_MERCHANT_KEY" \
  -H "X-API-Key: ${{ strtoupper(setting("site_title")) }}_API_KEY"

# Environment-specific credential examples:
# Sandbox: test_merchant_xxxxx, test_api_key_xxxxx
# Production: merchant_xxxxx, api_key_xxxxx</code></pre>
                </div>
            </div>
        </div>
    </div>
</section>
