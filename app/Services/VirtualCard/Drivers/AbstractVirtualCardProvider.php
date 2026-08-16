<?php

namespace App\Services\VirtualCard\Drivers;

use App\Exceptions\NotifyErrorException;
use App\Models\VirtualCard;

/**
 * Diagnostic result returned by every provider's `testConnection()`.
 *
 * Standardised so the admin's "Test Connection" UI can render any
 * provider's response without knowing which gateway it came from.
 *
 * @internal Just a documented array shape; using array<string,mixed>
 *           keeps it lightweight without forcing a new value object.
 *
 * Shape:
 *   [
 *     'ok'         => bool,                 // true if auth + reachability worked
 *     'mode'       => 'sandbox'|'live'|null,// resolved environment
 *     'message'    => string,               // human-readable summary
 *     'latency_ms' => int|null,             // round-trip time for the probe
 *     'details'    => array<string,mixed>,  // provider-specific extras (raw response, account id, etc.)
 *   ]
 */

/**
 * Default no-op base for providers that have not implemented every action yet.
 *
 * Concrete providers extend this class and override only the methods their
 * gateway actually supports. Capability flags in config/virtual_card.php
 * decide which actions the UI exposes — a provider that lacks an API for
 * freeze/topup/etc. simply hides those actions instead of failing at runtime.
 */
abstract class AbstractVirtualCardProvider implements VirtualCardProviderInterface
{
    public function topUpCard($amount, $cardID): array
    {
        throw new NotifyErrorException(__('Top up is not supported by this provider.'));
    }

    public function withdrawFromCard($amount, $cardID): array
    {
        throw new NotifyErrorException(__('Withdrawal is not supported by this provider.'));
    }

    public function getCardDetails(VirtualCard $card)
    {
        return [
            'message' => __('Card details are not available for this provider.'),
        ];
    }

    public function freezeCard(VirtualCard $card): array
    {
        // Default soft-freeze: caller (controller) flips the DB status.
        return ['status' => 'inactive', 'soft' => true];
    }

    public function unfreezeCard(VirtualCard $card): array
    {
        return ['status' => 'active', 'soft' => true];
    }

    /**
     * Lightweight health probe — pings the provider's auth/whoami endpoint
     * and reports whether credentials work and the API is reachable.
     *
     * Default implementation is "not implemented" so legacy providers
     * keep working; concrete providers override with a real probe.
     *
     * @return array{ok: bool, mode: ?string, message: string, latency_ms: ?int, details: array<string,mixed>}
     */
    public function testConnection(): array
    {
        return [
            'ok'         => false,
            'mode'       => null,
            'message'    => __('This provider has not implemented a connection test. Update the gateway credentials manually and try issuing a card to verify.'),
            'latency_ms' => null,
            'details'    => ['implemented' => false],
        ];
    }
}
