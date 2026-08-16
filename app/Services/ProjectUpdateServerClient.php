<?php

namespace App\Services;

use App\Exceptions\NotifyErrorException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProjectUpdateServerClient
{
    /**
     * @return array<string, mixed>
     */
    public function activate(string $purchaseCode, ?string $domain = null): array
    {
        return $this->post('licenses/activate', [
            'purchase_code' => $purchaseCode,
            'domain'        => $domain ?: request()->getHost(),
            'product_slug'  => config('project_updater.product_slug'),
            'item_id'       => config('project_updater.item_id'),
            'version'       => config('app.version'),
            'channel'       => config('project_updater.channel'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function check(?string $licenseToken): array
    {
        return $this->post('updates/check', [
            'license_token' => $licenseToken,
            'domain'        => request()->getHost(),
            'product_slug'  => config('project_updater.product_slug'),
            'item_id'       => config('project_updater.item_id'),
            'version'       => config('app.version'),
            'channel'       => config('project_updater.channel'),
        ]);
    }

    public function download(string $url): string
    {
        $response = Http::timeout((int) config('project_updater.timeout', 20))
            ->retry((int) config('project_updater.retries', 2), 500, throw: false)
            ->accept('application/zip')
            ->get($this->absoluteUrl($url));

        if ($response->failed()) {
            throw new NotifyErrorException(__('The update package could not be downloaded. Server returned :status.', [
                'status' => $response->status(),
            ]));
        }

        return $response->body();
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function post(string $endpoint, array $payload): array
    {
        $serverUrl = config('project_updater.server_url');

        if (blank($serverUrl)) {
            throw new NotifyErrorException(__('Project updater server URL is not configured.'));
        }

        $url = $this->absoluteUrl($endpoint);

        Log::channel('single')->info('[updater] outgoing request', [
            'endpoint' => $endpoint,
            'url'      => $url,
            'payload'  => $this->safePayload($payload),
        ]);

        try {
            $response = Http::timeout((int) config('project_updater.timeout', 20))
                ->retry((int) config('project_updater.retries', 2), 500, throw: false)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::channel('single')->error('[updater] connection failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            throw new NotifyErrorException(__('Project updater server is unreachable right now.'));
        }

        Log::channel('single')->info('[updater] response', [
            'url'     => $url,
            'status'  => $response->status(),
            'body'    => Str::limit((string) $response->body(), 2000),
            'headers' => $response->headers(),
        ]);

        if ($response->status() === 429) {
            throw new NotifyErrorException(__('Updater rate limit reached. Try again after :seconds seconds.', [
                'seconds' => $response->header('Retry-After', 'a few'),
            ]));
        }

        if ($response->failed()) {
            $message = $response->json('message') ?: __('Project updater request failed.');

            Log::channel('single')->error('[updater] request failed', [
                'url'     => $url,
                'status'  => $response->status(),
                'message' => $message,
            ]);

            throw new NotifyErrorException($message);
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new NotifyErrorException(__('Project updater returned an invalid response.'));
        }

        if (($data['success'] ?? true) === false) {
            throw new NotifyErrorException((string) ($data['message'] ?? __('Project updater rejected the request.')));
        }

        return $data;
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function safePayload(array $payload): array
    {
        if (isset($payload['purchase_code']) && is_string($payload['purchase_code'])) {
            $payload['purchase_code'] = Str::mask($payload['purchase_code'], '*', 4, max(0, strlen($payload['purchase_code']) - 8));
        }

        if (isset($payload['license_token']) && is_string($payload['license_token'])) {
            $payload['license_token'] = Str::limit($payload['license_token'], 8, '…');
        }

        return $payload;
    }

    private function absoluteUrl(string $endpoint): string
    {
        if (Str::startsWith($endpoint, ['http://', 'https://'])) {
            return $endpoint;
        }

        return rtrim((string) config('project_updater.server_url'), '/').'/api/v1/'.ltrim($endpoint, '/');
    }
}
