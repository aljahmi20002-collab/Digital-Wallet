<?php

namespace App\Services\VirtualCard\Drivers;

use App\Models\VirtualCard;
use App\Models\VirtualCardRequest;

interface VirtualCardProviderInterface
{
    /**
     * Issue a card via the provider. Returns normalized card payload for DB save.
     */
    public function issueCard(VirtualCardRequest $request): array;

    /**
     * Top up a provider card. Returns provider transaction payload.
     */
    public function topUpCard($amount, $cardID): array;

    /**
     * Withdraw from a provider card. Returns provider transaction payload.
     */
    public function withdrawFromCard($amount, $cardID): array;

    /**
     * Fetch live card details (PAN/CVV/etc) from the provider.
     *
     * Implementations may return either a normalized array or an HTML
     * fragment string (rendered server-side). The frontend handler decides
     * how to display it based on shape.
     *
     * @return array|string
     */
    public function getCardDetails(VirtualCard $card);

    /**
     * Freeze (block) a provider card. Returns the new normalized status.
     */
    public function freezeCard(VirtualCard $card): array;

    /**
     * Unfreeze (re-activate) a provider card. Returns the new normalized status.
     */
    public function unfreezeCard(VirtualCard $card): array;
}
