<?php

namespace App\Enums;

use App\Services\FeatureManager;
use Throwable;
use ValueError;

enum TrxType: string
{
    case DEPOSIT               = 'deposit';
    case SEND_MONEY            = 'send_money';
    case RECEIVE_MONEY         = 'receive_money';
    case REQUEST_MONEY         = 'request_money';
    case EXCHANGE_MONEY        = 'exchange_money';
    case VOUCHER               = 'voucher';
    case GIFT_CARD             = 'gift_card';
    case GIFT_CARD_REDEEM      = 'gift_card_redeem';
    case PAYMENT               = 'payment';
    case RECEIVE_PAYMENT       = 'receive_payment';
    case ADD_BALANCE           = 'add_balance';
    case SUBTRACT_BALANCE      = 'subtract_balance';
    case WITHDRAW              = 'withdraw';
    case MOBILE_RECHARGE       = 'mobile_recharge';
    case REFERRAL_REWARD       = 'referral_reward';
    case REWARD                = 'reward';
    case SIGNUP_BONUS          = 'signup_bonus';
    case CARD_TOPUP            = 'card_topup';
    case CARD_WITHDRAW         = 'card_withdraw';
    case P2P_ESCROW            = 'p2p_escrow';
    case P2P_RELEASE           = 'p2p_release';
    case P2P_REFUND            = 'p2p_refund';
    case P2P_PROMOTION         = 'p2p_promotion';
    case WALLET_EARN_STAKE     = 'wallet_earn_stake';
    case WALLET_EARN_REWARD    = 'wallet_earn_reward';
    case WALLET_EARN_PRINCIPAL = 'wallet_earn_principal';
    case SUBSCRIPTION          = 'subscription';
    case SUBSCRIPTION_RENEWAL  = 'subscription_renewal';
    case REFUND                = 'refund';
    case AGENT_CASH_IN         = 'agent_cash_in';
    case AGENT_CASH_OUT        = 'agent_cash_out';
    case AGENT_COMMISSION      = 'agent_commission';

    /**
     * Get all transaction types as an array for dropdowns.
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn ($case) => $case->value, self::cases()),
            array_map(fn ($case) => __(str_replace('_', ' ', ucfirst($case->value))), self::cases())
        );
    }

    public function label()
    {
        return match ($this) {
            self::DEPOSIT               => __('Deposit'),
            self::SEND_MONEY            => __('Send Money'),
            self::RECEIVE_MONEY         => __('Receive Money'),
            self::REQUEST_MONEY         => __('Request Money'),
            self::EXCHANGE_MONEY        => __('Exchange Money'),
            self::VOUCHER               => __('Voucher'),
            self::GIFT_CARD             => __('Gift Card'),
            self::GIFT_CARD_REDEEM      => __('Gift Card Redeem'),
            self::PAYMENT               => __('Payment'),
            self::RECEIVE_PAYMENT       => __('Receive Payment'),
            self::ADD_BALANCE           => __('Add Balance'),
            self::SUBTRACT_BALANCE      => __('Subtract Balance'),
            self::WITHDRAW              => __('Withdraw'),
            self::MOBILE_RECHARGE       => __('Mobile Recharge'),
            self::REFERRAL_REWARD       => __('Referral Reward'),
            self::REWARD                => __('Reward'),
            self::SIGNUP_BONUS          => __('Signup Bonus'),
            self::CARD_TOPUP            => __('Card Topup'),
            self::CARD_WITHDRAW         => __('Card Withdraw'),
            self::P2P_ESCROW            => __('P2P Escrow Hold'),
            self::P2P_RELEASE           => __('P2P Release'),
            self::P2P_REFUND            => __('P2P Escrow Refund'),
            self::P2P_PROMOTION         => __('P2P Promotion'),
            self::WALLET_EARN_STAKE     => __('Wallet Earn Stake'),
            self::WALLET_EARN_REWARD    => __('Wallet Earn Reward'),
            self::WALLET_EARN_PRINCIPAL => __('Wallet Earn Principal'),
            self::SUBSCRIPTION          => __('Subscription'),
            self::SUBSCRIPTION_RENEWAL  => __('Subscription Renewal'),
            self::REFUND                => __('Refund'),
            self::AGENT_CASH_IN         => __('Agent Cash In'),
            self::AGENT_CASH_OUT        => __('Agent Cash Out'),
            self::AGENT_COMMISSION      => __('Agent Commission'),
            default                     => __('Unknown'),
        };
    }

    /**
     * Convert the enum value to a kebab-case (hyphenated) string.
     */
    public function kebabCase(): string
    {
        return str_replace('_', '-', $this->value);
    }

    /**
     * Returns the badge color for the current transaction type.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::DEPOSIT, self::ADD_BALANCE , self::CARD_TOPUP => 'info',
            self::RECEIVE_MONEY, self::WITHDRAW , self::CARD_WITHDRAW => 'primary',
            self::MOBILE_RECHARGE       => 'info',
            self::REQUEST_MONEY         => 'danger',
            self::EXCHANGE_MONEY        => 'success',
            self::PAYMENT               => 'warning',
            self::P2P_ESCROW            => 'secondary',
            self::P2P_RELEASE           => 'success',
            self::P2P_REFUND            => 'info',
            self::P2P_PROMOTION         => 'warning',
            self::WALLET_EARN_STAKE     => 'primary',
            self::WALLET_EARN_REWARD    => 'success',
            self::WALLET_EARN_PRINCIPAL => 'info',
            self::SUBSCRIPTION          => 'primary',
            self::SUBSCRIPTION_RENEWAL  => 'primary',
            self::REFUND                => 'info',
            self::AGENT_CASH_IN         => 'success',
            self::AGENT_CASH_OUT        => 'primary',
            self::AGENT_COMMISSION      => 'success',
            self::GIFT_CARD             => 'primary',
            self::GIFT_CARD_REDEEM      => 'success',
            self::SIGNUP_BONUS          => 'success',
            default                     => 'secondary',
        };
    }

    /**
     * Accepts a string or an array (uses the first element if array)
     * and returns the badge color. Returns 'secondary' on invalid type.
     */
    public static function getBadgesColor(string|array $type): string
    {
        if (is_array($type)) {
            $type = $type[0];
        }

        try {
            return self::from($type)->badgeColor();
        } catch (ValueError) {
            return 'secondary';
        }
    }

    /**
     * Mapping of rank-eligible transaction types to their underlying
     * feature_catalog feature keys. Each entry must point to a key
     * defined in config/feature_catalog.php so the rank UI can stay in
     * sync with the Feature Management toggles.
     *
     * @return array<string, string>
     */
    public static function rankFeatureMap(): array
    {
        return [
            self::DEPOSIT->value           => 'deposit_money',
            self::WITHDRAW->value          => 'withdraw_money',
            self::SEND_MONEY->value        => 'send_money',
            self::REQUEST_MONEY->value     => 'request_money',
            self::EXCHANGE_MONEY->value    => 'exchange_money',
            self::WALLET_EARN_STAKE->value => 'wallet_earn',
            self::MOBILE_RECHARGE->value   => 'mobile_recharge',
            self::PAYMENT->value           => 'merchant_payment',
            self::AGENT_CASH_IN->value     => 'agent_program',
            self::SUBSCRIPTION->value      => 'subscription_system',
            self::P2P_ESCROW->value        => 'p2p_marketplace',
            self::CARD_TOPUP->value        => 'virtual_card',
            self::REFERRAL_REWARD->value   => 'referral_program',
            self::VOUCHER->value           => 'vouchers',
            self::GIFT_CARD->value         => 'gift_cards',
        ];
    }

    /**
     * Returns the rank-eligible transaction types whose feature is
     * currently active. The list is driven by the Feature Management
     * catalog so disabling a feature (e.g. P2P, Gift Cards) immediately
     * removes it from the rank create/edit "Allow Transaction Type"
     * checkboxes.
     *
     * Falls back to the catalog config when the features table has not
     * yet been synced (fresh install or migration not run).
     *
     * @return array<int, self>
     */
    public static function userRankSupport(): array
    {
        $manager  = self::resolveFeatureManager();
        $catalog  = (array) config('feature_catalog.features', []);
        $resolved = [];

        foreach (self::rankFeatureMap() as $trxValue => $featureKey) {
            $isEnabled = $manager !== null && $manager->find($featureKey) !== null
                ? $manager->isEnabled($featureKey)
                : (bool) data_get($catalog, $featureKey.'.is_enabled', false);

            if (! $isEnabled) {
                continue;
            }

            $case = self::tryFrom($trxValue);

            if ($case instanceof self) {
                $resolved[] = $case;
            }
        }

        return $resolved;
    }

    /**
     * Resolve the FeatureManager from the container, returning null
     * when the service container has not booted yet or the singleton is
     * unavailable (avoids hard failures in console/test bootstrapping).
     */
    private static function resolveFeatureManager(): ?FeatureManager
    {
        try {
            return app(FeatureManager::class);
        } catch (Throwable) {
            return null;
        }
    }

    public function icon()
    {
        return match ($this) {
            self::DEPOSIT               => 'deposit',
            self::SEND_MONEY            => 'send-money',
            self::RECEIVE_MONEY         => 'receive-money',
            self::REQUEST_MONEY         => 'request-money-1',
            self::EXCHANGE_MONEY        => 'exchange-money',
            self::VOUCHER               => 'voucher',
            self::GIFT_CARD             => 'gift',
            self::GIFT_CARD_REDEEM      => 'gift',
            self::PAYMENT               => 'payment',
            self::RECEIVE_PAYMENT       => 'receive-payment-1',
            self::ADD_BALANCE           => 'add-balance',
            self::SUBTRACT_BALANCE      => 'subtract-balance',
            self::WITHDRAW              => 'withdraw',
            self::MOBILE_RECHARGE       => 'mobile-recharge',
            self::REFERRAL_REWARD       => 'referral-reward',
            self::REWARD                => 'reward',
            self::SIGNUP_BONUS          => 'reward',
            self::CARD_TOPUP            => 'card',
            self::CARD_WITHDRAW         => 'card-approved',
            self::P2P_ESCROW            => 'lock',
            self::P2P_RELEASE           => 'unlock',
            self::P2P_REFUND            => 'refund',
            self::P2P_PROMOTION         => 'star',
            self::WALLET_EARN_STAKE     => 'lock',
            self::WALLET_EARN_REWARD    => 'trending-up',
            self::WALLET_EARN_PRINCIPAL => 'wallet-receive',
            self::SUBSCRIPTION          => 'subscription',
            self::SUBSCRIPTION_RENEWAL  => 'subscription',
            self::REFUND                => 'refund',
            self::AGENT_CASH_IN         => 'receive-money',
            self::AGENT_CASH_OUT        => 'send-money',
            self::AGENT_COMMISSION      => 'reward',

            default => 'unknown',
        };
    }
}
