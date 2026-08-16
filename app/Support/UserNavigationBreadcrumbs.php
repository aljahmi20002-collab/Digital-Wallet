<?php

namespace App\Support;

use Illuminate\Support\Str;

final class UserNavigationBreadcrumbs
{
    /**
     * Resolve the user dashboard menu path for the given route name.
     *
     * @return array<int, string>
     */
    public static function forRoute(?string $routeName): array
    {
        if ($routeName === null || $routeName === '') {
            return [];
        }

        foreach (self::routeMap() as $pattern => $segments) {
            if (Str::is($pattern, $routeName)) {
                return $segments;
            }
        }

        return [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function routeMap(): array
    {
        return [
            'user.dashboard' => ['Dashboard Overview'],

            'user.deposit.history'    => ['Add & Withdraw Funds', 'Deposit History'],
            'user.deposit.*'          => ['Add & Withdraw Funds', 'Add Money'],
            'user.withdraw.account.*' => ['Add & Withdraw Funds', 'Withdraw Accounts'],
            'user.withdraw.*'         => ['Add & Withdraw Funds', 'Withdraw Funds'],

            'user.wallet-earn.plans'  => ['Wallet Earn', 'Earn Plans'],
            'user.wallet-earn.stakes' => ['Wallet Earn', 'My Stakes'],
            'user.wallet-earn.show'   => ['Wallet Earn', 'My Stakes'],
            'user.wallet-earn.*'      => ['Wallet Earn'],

            'user.p2p.offers.index'       => ['P2P Marketplace', 'P2P Market'],
            'user.p2p.offers.my'          => ['P2P Marketplace', 'My Trade Ads'],
            'user.p2p.offers.create'      => ['P2P Marketplace', 'Create Trade Ad'],
            'user.p2p.offers.edit'        => ['P2P Marketplace', 'Edit Trade Ad'],
            'user.p2p.offers.promotion.*' => ['P2P Marketplace', 'Promote Trade Ad'],
            'user.p2p.offers.*'           => ['P2P Marketplace', 'Trade Ad Details'],
            'user.p2p.orders.index'       => ['P2P Marketplace', 'Trade Orders'],
            'user.p2p.orders.*'           => ['P2P Marketplace', 'Trade Order Details'],
            'user.p2p.payment-accounts.*' => ['P2P Marketplace', 'Payment Accounts'],
            'user.p2p.advertisers.*'      => ['P2P Marketplace', 'Trader Profile'],

            'user.virtual-card.request.*'     => ['Virtual Cards', 'Card Requests'],
            'user.virtual-card.cardholders.*' => ['Virtual Cards', 'Cardholders'],
            'user.virtual-card.topup'         => ['Virtual Cards', 'Top Up Card'],
            'user.virtual-card.withdraw'      => ['Virtual Cards', 'Withdraw From Card'],
            'user.virtual-card.*'             => ['Virtual Cards', 'My Cards'],

            'user.send-money.*'     => ['Money Transfers', 'Send Money'],
            'user.request-money.*'  => ['Money Transfers', 'Request Payment'],
            'user.exchange-money.*' => ['Money Transfers', 'Currency Exchange'],

            'user.voucher.create'       => ['My Vouchers', 'Create Voucher'],
            'user.voucher.*'            => ['My Vouchers'],
            'user.merchant.*'           => ['Merchant Tools'],
            'user.payment-links.create' => ['Payment Links', 'Create Payment Link'],
            'user.payment-links.edit'   => ['Payment Links', 'Edit Payment Link'],
            'user.payment-links.*'      => ['Payment Links'],
            'user.transaction.*'        => ['Transaction History'],

            'user.subscription.plans'    => ['Subscriptions', 'All Plans'],
            'user.subscription.current'  => ['Subscriptions', 'My Subscription'],
            'user.subscription.history'  => ['Subscriptions', 'History'],
            'user.subscription.checkout' => ['Subscriptions', 'Checkout'],
            'user.subscription.*'        => ['Subscriptions'],

            'user.referral.*'        => ['Referral Program'],
            'user.support-ticket.*'  => ['Support Tickets'],
            'user.settings.*'        => ['Account Settings'],
            'user.notifications.*'   => ['Notifications'],
            'user.wallet.my-qr-code' => ['Wallets', 'My QR Code'],
            'user.wallet.*'          => ['Wallets'],
            'user.agent.*'           => ['Agent Services'],
            'user.rank.*'            => ['User Rank'],
        ];
    }
}
