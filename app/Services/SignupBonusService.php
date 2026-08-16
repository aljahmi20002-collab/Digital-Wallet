<?php

namespace App\Services;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\TemplateNotification;
use Throwable;
use Transaction as TransactionServiceFacade;
use Wallet;

class SignupBonusService
{
    /**
     * Attempt to credit the signup bonus to the given user.
     *
     * Returns true when a bonus was actually credited; false when the
     * program is disabled, the user is ineligible, or the role-specific
     * amount is zero. Safe to call multiple times — the
     * `signup_bonus_awarded_at` column prevents double-crediting.
     */
    public function award(User $user): bool
    {
        if (! $this->isProgramEnabled()) {
            return false;
        }

        if ($user->signup_bonus_awarded_at !== null) {
            return false;
        }

        if ($this->requiresEmailVerification() && ! $user->hasVerifiedEmail()) {
            return false;
        }

        $amount = $this->amountForRole($user->role);
        if ($amount <= 0) {
            return false;
        }

        $wallet = $user->defaultWallet;
        if (! $wallet) {
            return false;
        }

        $transactionData = new TransactionData(
            user_id: $user->id,
            trx_type: TrxType::SIGNUP_BONUS,
            amount: $amount,
            amount_flow: AmountFlow::PLUS,
            net_amount: $amount,
            payable_amount: $amount,
            payable_currency: siteCurrency(),
            wallet_reference: $wallet->uuid,
            description: __('Welcome bonus for joining as :role', ['role' => $user->role->title()]),
            status: TrxStatus::COMPLETED,
        );

        TransactionServiceFacade::create($transactionData);
        Wallet::addMoney($wallet, $amount);

        $user->forceFill(['signup_bonus_awarded_at' => now()])->save();

        $this->notify($user, $amount);

        return true;
    }

    /**
     * Whether the master switch for signup bonuses is on.
     */
    public function isProgramEnabled(): bool
    {
        return (bool) Setting::get('signup_bonus_enabled');
    }

    /**
     * Whether the bonus must wait until the user verifies their email.
     */
    public function requiresEmailVerification(): bool
    {
        return (bool) Setting::get('signup_bonus_require_email_verified');
    }

    /**
     * Get the configured bonus amount for the given role.
     */
    public function amountForRole(UserRole $role): float
    {
        $key = match ($role) {
            UserRole::USER     => 'signup_bonus_user_amount',
            UserRole::MERCHANT => 'signup_bonus_merchant_amount',
            UserRole::AGENT    => 'signup_bonus_agent_amount',
        };

        return (float) Setting::get($key, 'signup_bonus_settings', 0);
    }

    /**
     * Send the in-app / email notification. Best-effort — a failed
     * notification must not roll back a successful credit.
     */
    protected function notify(User $user, float $amount): void
    {
        try {
            $user->notify(new TemplateNotification(
                identifier: 'signup_bonus_credited',
                data: [
                    'amount' => $amount.' '.siteCurrency(),
                    'name'   => $user->name,
                ],
                action: route('user.transaction.index'),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
