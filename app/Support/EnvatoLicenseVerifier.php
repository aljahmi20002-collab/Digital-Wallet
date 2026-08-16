<?php

namespace App\Support;

use App\Services\ProjectUpdateServerClient;
use Illuminate\Support\Str;

class EnvatoLicenseVerifier
{
    public function __construct(private readonly ProjectUpdateServerClient $client) {}

    /**
     * @return array{ok: bool, status: string, message: string, checks: list<array{label: string, status: string, detail: string}>, license?: array<string, mixed>}
     */
    public function verify(string $purchaseCode, ?string $appUrl = null): array
    {
        $purchaseCode = Str::lower(trim($purchaseCode));
        $domain       = $this->domainFromUrl($appUrl);

        if (! $this->hasValidPurchaseCodeFormat($purchaseCode)) {
            return $this->failure(
                __('Invalid Envato purchase code format.'),
                __('Purchase code'),
                __('Copy the code from the Envato license certificate and try again.')
            );
        }

        $data    = $this->client->activate($purchaseCode, $domain);
        $license = $data['license'] ?? $data;

        if (! is_array($license)) {
            return $this->failure(
                __('License activation response was invalid.'),
                __('Updater server'),
                __('The update server did not return a usable license token.')
            );
        }

        return [
            'ok'      => true,
            'status'  => 'success',
            'message' => __('Envato license verified. You can continue the installation.'),
            'checks'  => [
                [
                    'label'  => __('Purchase code'),
                    'status' => 'success',
                    'detail' => __('The update server verified this purchase with Envato.'),
                ],
                [
                    'label'  => __('Buyer'),
                    'status' => 'success',
                    'detail' => (string) ($license['buyer_username'] ?? $license['buyer'] ?? __('Verified buyer')),
                ],
                [
                    'label'  => __('License token'),
                    'status' => 'success',
                    'detail' => __('Updater access token received for this domain.'),
                ],
            ],
            'license' => [
                'purchase_code'   => $purchaseCode,
                'license_token'   => $license['token']           ?? $license['license_token'] ?? null,
                'buyer_username'  => $license['buyer_username']  ?? $license['buyer'] ?? null,
                'item_id'         => $license['item_id']         ?? config('project_updater.item_id'),
                'domain'          => $license['domain']          ?? $domain ?? request()->getHost(),
                'status'          => $license['status']          ?? 'active',
                'supported_until' => $license['supported_until'] ?? $license['support_until'] ?? null,
                'metadata'        => $license,
            ],
        ];
    }

    private function hasValidPurchaseCodeFormat(string $purchaseCode): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $purchaseCode) === 1;
    }

    private function domainFromUrl(?string $appUrl): ?string
    {
        if (! is_string($appUrl) || trim($appUrl) === '') {
            return null;
        }

        $host = parse_url($appUrl, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }

    /**
     * @return array{ok: bool, status: string, message: string, checks: list<array{label: string, status: string, detail: string}>}
     */
    private function failure(string $message, string $label, string $detail): array
    {
        return [
            'ok'      => false,
            'status'  => 'error',
            'message' => $message,
            'checks'  => [
                [
                    'label'  => $label,
                    'status' => 'error',
                    'detail' => $detail,
                ],
            ],
        ];
    }
}
