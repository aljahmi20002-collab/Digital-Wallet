<?php

namespace App\Services;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\BillingCycle;
use App\Enums\MethodType;
use App\Enums\SubscriptionStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Models\NotificationTemplate;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTransaction;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Wallet;
use App\Notifications\TemplateNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(
        private TransactionService $transactionService,
        private WalletService $walletService,
    ) {}

    /**
     * Subscribe a user to a plan for a specific billing cycle.
     *
     * @throws NotifyErrorException|\Throwable
     */
    public function subscribe(User $user, SubscriptionPlan $plan, BillingCycle $billingCycle): UserSubscription
    {
        if (! $plan->status) {
            throw new NotifyErrorException(__('This plan is currently unavailable.'));
        }

        $plan->loadMissing('prices');
        $price = $this->priceForCycle($plan, $billingCycle);

        if ($price === null) {
            throw new NotifyErrorException(__('The selected billing cycle is not available for this plan.'));
        }

        $currencyCode = siteCurrency('code');

        return DB::transaction(function () use ($user, $plan, $billingCycle, $price, $currencyCode) {
            User::query()->whereKey($user->id)->lockForUpdate()->first();

            $existingActive = $user->subscriptions()
                ->whereIn('status', [
                    SubscriptionStatus::Active->value,
                    SubscriptionStatus::Trial->value,
                    SubscriptionStatus::Grace->value,
                ])
                ->lockForUpdate()
                ->first();

            if ($existingActive) {
                return $this->switchPlan($user, $existingActive, $plan, $billingCycle);
            }

            $now    = Carbon::now();
            $wallet = $this->lockedDefaultWallet($user);

            if (! $wallet) {
                throw new NotifyErrorException(__('No wallet found. Please create a wallet first.'));
            }

            $startsTrial = $this->shouldStartTrial($plan, $price);

            if ($startsTrial || $price <= 0) {
                return $this->activateSubscription(
                    $user,
                    $plan,
                    $billingCycle,
                    $wallet->uuid,
                    0,
                    $currencyCode,
                    $now,
                    startTrial: $startsTrial
                );
            }

            if ($wallet->balance < $price) {
                throw new NotifyErrorException(__('Insufficient wallet balance. You need :amount to subscribe to this plan.', [
                    'amount' => $currencyCode.' '.number_format($price, 2),
                ]));
            }

            $this->walletService->subtractMoney($wallet, $price);

            $trxData = new TransactionData(
                user_id: $user->id,
                trx_type: TrxType::SUBSCRIPTION,
                amount: $price,
                amount_flow: AmountFlow::MINUS,
                fee: 0,
                currency: $currencyCode,
                provider: 'wallet',
                processing_type: MethodType::AUTOMATIC,
                net_amount: $price,
                payable_amount: $price,
                payable_currency: $currencyCode,
                wallet_reference: $wallet->uuid,
                trx_data: [
                    'plan_id'       => $plan->id,
                    'plan_name'     => $plan->name,
                    'billing_cycle' => $billingCycle->value,
                ],
                description: __('Subscription: :plan (:cycle)', ['plan' => $plan->name, 'cycle' => $billingCycle->label()]),
                status: TrxStatus::COMPLETED,
            );

            $transaction = $this->transactionService->create($trxData);

            return $this->activateSubscription($user, $plan, $billingCycle, $wallet->uuid, $price, $currencyCode, $now, $transaction->trx_id);
        });
    }

    /**
     * Switch from an existing subscription to a different plan / billing cycle.
     * Charges only the prorated difference based on remaining days of the current period.
     *
     * @throws NotifyErrorException|\Throwable
     */
    public function switchPlan(User $user, UserSubscription $current, SubscriptionPlan $newPlan, BillingCycle $newCycle): UserSubscription
    {
        if (! $newPlan->status) {
            throw new NotifyErrorException(__('This plan is currently unavailable.'));
        }

        $currentCycle = $current->billing_cycle instanceof BillingCycle
            ? $current->billing_cycle
            : ($current->billing_cycle ? BillingCycle::from($current->billing_cycle) : null);

        if ((int) $current->subscription_plan_id === (int) $newPlan->id
            && $currentCycle?->value             === $newCycle->value) {
            throw new NotifyErrorException(__('You are already on this plan and billing cycle.'));
        }

        if ($currentCycle?->isLifetime()) {
            throw new NotifyErrorException(__('Lifetime subscriptions cannot be switched.'));
        }

        $current->loadMissing('plan');
        $newPlan->loadMissing('prices');
        $proration    = $this->calculateProration($current, $newPlan, $newCycle);
        $charge       = (float) $proration['charge'];
        $credit       = (float) $proration['credit'];
        $newPrice     = (float) $proration['new_plan_price'];
        $currencyCode = $current->currency_code ?? siteCurrency('code');
        $previousPlan = $current->plan?->name   ?? __('Previous plan');

        return DB::transaction(function () use ($user, $current, $newPlan, $newCycle, $charge, $credit, $newPrice, $currencyCode, $previousPlan) {
            $now    = Carbon::now();
            $wallet = $this->lockedDefaultWallet($user);

            if (! $wallet) {
                throw new NotifyErrorException(__('No wallet found. Please create a wallet first.'));
            }

            if ($charge > 0 && $wallet->balance < $charge) {
                throw new NotifyErrorException(__('Insufficient wallet balance. You need :amount to switch to this plan.', [
                    'amount' => $currencyCode.' '.number_format($charge, 2),
                ]));
            }

            // End the current subscription right now (replaced by the new one).
            $current->update([
                'status'             => SubscriptionStatus::Cancelled->value,
                'cancelled_at'       => $now,
                'current_period_end' => $now,
                'notes'              => __('Switched to :plan', ['plan' => $newPlan->name]),
            ]);

            $trxId = null;

            if ($charge > 0) {
                $this->walletService->subtractMoney($wallet, $charge);

                $trxData = new TransactionData(
                    user_id: $user->id,
                    trx_type: TrxType::SUBSCRIPTION,
                    amount: $charge,
                    amount_flow: AmountFlow::MINUS,
                    fee: 0,
                    currency: $currencyCode,
                    provider: 'wallet',
                    processing_type: MethodType::AUTOMATIC,
                    net_amount: $charge,
                    payable_amount: $charge,
                    payable_currency: $currencyCode,
                    wallet_reference: $wallet->uuid,
                    trx_data: [
                        'plan_id'          => $newPlan->id,
                        'plan_name'        => $newPlan->name,
                        'billing_cycle'    => $newCycle->value,
                        'previous_plan_id' => $current->subscription_plan_id,
                        'prorated_credit'  => $credit,
                        'new_plan_price'   => $newPrice,
                    ],
                    description: __('Plan switch: :from -> :to (prorated)', [
                        'from' => $current->plan?->name ?? __('Plan'),
                        'to'   => $newPlan->name,
                    ]),
                    status: TrxStatus::COMPLETED,
                );

                $transaction = $this->transactionService->create($trxData);
                $trxId       = $transaction->trx_id;
            }

            return $this->activateSubscription(
                $user,
                $newPlan,
                $newCycle,
                $wallet->uuid,
                $charge,
                $currencyCode,
                $now,
                $trxId,
                notificationIdentifier: 'subscription_user_plan_switched',
                notificationExtra: [
                    'previous_plan' => $previousPlan,
                    'credit'        => $this->formatNotificationAmount($credit, $currencyCode),
                    'charge'        => $this->formatNotificationAmount($charge, $currencyCode),
                ]
            );
        });
    }

    /**
     * Compute prorated upgrade/downgrade math based on days remaining of the current cycle.
     *
     * @return array{credit: float, charge: float, new_plan_price: float, remaining_days: int, total_days: int}
     */
    public function calculateProration(UserSubscription $current, SubscriptionPlan $newPlan, BillingCycle $newCycle): array
    {
        $newPrice = (float) ($this->priceForCycle($newPlan, $newCycle) ?? 0);

        $start = $current->current_period_start;
        $end   = $current->current_period_end;
        $paid  = (float) $current->amount_paid;

        // No proration possible (trial / lifetime / free / missing dates) — just charge full new price.
        if ($paid <= 0 || ! $start || ! $end || ! now()->isBefore($end)) {
            return [
                'credit'         => 0.0,
                'charge'         => max(0.0, $newPrice),
                'new_plan_price' => $newPrice,
                'remaining_days' => 0,
                'total_days'     => 0,
            ];
        }

        $totalSeconds     = max(1, $start->diffInSeconds($end, false));
        $remainingSeconds = max(0, now()->diffInSeconds($end, false));
        $ratio            = $totalSeconds > 0 ? $remainingSeconds / $totalSeconds : 0;
        $credit           = round($paid * $ratio, 2);
        $charge           = max(0.0, round($newPrice - $credit, 2));

        return [
            'credit'         => $credit,
            'charge'         => $charge,
            'new_plan_price' => $newPrice,
            'remaining_days' => (int) ceil($remainingSeconds / 86400),
            'total_days'     => (int) ceil($totalSeconds / 86400),
        ];
    }

    /**
     * Renew an existing subscription using its stored billing cycle.
     *
     * @throws NotifyErrorException|\Throwable
     */
    public function renew(UserSubscription $subscription): UserSubscription
    {
        $user         = $subscription->user;
        $plan         = $subscription->plan;
        $billingCycle = $this->subscriptionBillingCycle($subscription);

        if ($billingCycle->isLifetime()) {
            throw new NotifyErrorException(__('Lifetime subscriptions do not need renewal.'));
        }

        if (! in_array($subscription->status, [
            SubscriptionStatus::Active,
            SubscriptionStatus::Trial,
            SubscriptionStatus::Grace,
            SubscriptionStatus::Expired,
        ], true)) {
            throw new NotifyErrorException(__('Only active or expired subscriptions can be renewed.'));
        }

        $plan->loadMissing('prices');
        $price = $this->priceForCycle($plan, $billingCycle);

        if ($price === null) {
            throw new NotifyErrorException(__('The selected billing cycle is not available for this plan.'));
        }

        $currencyCode = $subscription->currency_code ?? siteCurrency('code');

        return DB::transaction(function () use ($user, $plan, $billingCycle, $subscription, $price, $currencyCode) {
            $wallet = $this->lockedDefaultWallet($user);

            if (! $wallet) {
                throw new NotifyErrorException(__('No wallet found.'));
            }

            if ($price > 0 && $wallet->balance < $price) {
                throw new NotifyErrorException(__('Insufficient wallet balance for renewal.'));
            }

            $trxId = null;

            if ($price > 0) {
                $this->walletService->subtractMoney($wallet, $price);

                $trxData = new TransactionData(
                    user_id: $user->id,
                    trx_type: TrxType::SUBSCRIPTION_RENEWAL,
                    amount: $price,
                    amount_flow: AmountFlow::MINUS,
                    fee: 0,
                    currency: $currencyCode,
                    provider: 'wallet',
                    processing_type: MethodType::AUTOMATIC,
                    net_amount: $price,
                    payable_amount: $price,
                    payable_currency: $currencyCode,
                    wallet_reference: $wallet->uuid,
                    trx_data: [
                        'plan_id'              => $plan->id,
                        'plan_name'            => $plan->name,
                        'user_subscription_id' => $subscription->id,
                    ],
                    description: __('Subscription Renewal: :plan', ['plan' => $plan->name]),
                    status: TrxStatus::COMPLETED,
                );

                $transaction = $this->transactionService->create($trxData);
                $trxId       = $transaction->trx_id;
            }

            $baseDate = ($subscription->current_period_end && now()->isBefore($subscription->current_period_end))
                ? $subscription->current_period_end
                : now();
            $periodEnd = $billingCycle->calculateEndDate(Carbon::parse($baseDate));

            $subscription->update([
                'status'               => SubscriptionStatus::Active->value,
                'current_period_start' => $baseDate,
                'current_period_end'   => $periodEnd,
                'trial_ends_at'        => null,
                'grace_ends_at'        => null,
                'cancelled_at'         => null,
                'cancelled_by_admin'   => false,
                'amount_paid'          => $price,
                'wallet_reference'     => $wallet->uuid,
            ]);

            SubscriptionTransaction::create([
                'user_subscription_id' => $subscription->id,
                'user_id'              => $user->id,
                'subscription_plan_id' => $plan->id,
                'trx_id'               => $trxId,
                'type'                 => 'renewal',
                'amount'               => $price,
                'currency_code'        => $currencyCode,
                'status'               => 'completed',
            ]);

            $renewed = $subscription->fresh();

            $this->notifySubscriptionUser($renewed, 'subscription_user_renewed', [
                'amount' => $this->formatNotificationAmount($price, $currencyCode),
                'trx'    => $trxId ?? __('N/A'),
            ]);

            return $renewed;
        });
    }

    /**
     * Cancel a subscription.
     *
     * @throws NotifyErrorException
     */
    public function cancel(UserSubscription $subscription, bool $byAdmin = false): UserSubscription
    {
        if (! $subscription->isActive()) {
            throw new NotifyErrorException(__('This subscription is not active.'));
        }

        if ($subscription->cancelled_at !== null) {
            throw new NotifyErrorException(__('This subscription is already scheduled for cancellation.'));
        }

        $policy            = $subscription->plan->cancellation_policy;
        $cancelAtPeriodEnd = $policy === 'end_of_period'
            && $subscription->current_period_end !== null
            && now()->isBefore($subscription->current_period_end);

        $subscription->update([
            'status'             => $cancelAtPeriodEnd ? $subscription->status->value : SubscriptionStatus::Cancelled->value,
            'cancelled_at'       => now(),
            'cancelled_by_admin' => $byAdmin,
            'auto_renew'         => false,
            'current_period_end' => $cancelAtPeriodEnd ? $subscription->current_period_end : now(),
        ]);

        $cancelled = $subscription->fresh();

        $this->notifySubscriptionUser($cancelled, 'subscription_user_cancelled', [
            'cancelled_by' => $byAdmin ? __('Admin') : __('User'),
        ]);

        return $cancelled;
    }

    /**
     * Manually activate a subscription (admin action).
     *
     * @throws NotifyErrorException
     */
    public function adminActivate(UserSubscription $subscription): UserSubscription
    {
        $plan         = $subscription->plan;
        $now          = Carbon::now();
        $billingCycle = $this->subscriptionBillingCycle($subscription);

        $subscription->update([
            'status'               => SubscriptionStatus::Active->value,
            'started_at'           => $subscription->started_at ?? $now,
            'current_period_start' => $now,
            'current_period_end'   => $billingCycle->calculateEndDate($now),
            'grace_ends_at'        => null,
            'cancelled_at'         => null,
            'cancelled_by_admin'   => false,
        ]);

        $activated = $subscription->fresh();

        $this->notifySubscriptionUser($activated, 'subscription_user_admin_activated');

        return $activated;
    }

    /**
     * Process expired subscriptions (called by scheduler command).
     */
    public function processExpiries(): int
    {
        $count = $this->processExpiredTrials();

        $expired = UserSubscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<', now())
            ->with('plan')
            ->get();

        foreach ($expired as $sub) {
            if ($sub->cancelled_at !== null) {
                $sub->update([
                    'status' => SubscriptionStatus::Cancelled->value,
                ]);
            } else {
                $this->moveToGraceOrExpire($sub);
            }
            $count++;
        }

        $graceExpired = UserSubscription::query()
            ->where('status', SubscriptionStatus::Grace->value)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<', now())
            ->get();

        foreach ($graceExpired as $sub) {
            $sub->update(['status' => SubscriptionStatus::Expired->value]);
            $this->notifySubscriptionUser($sub->fresh(), 'subscription_user_expired');
            $count++;
        }

        return $count;
    }

    /**
     * Process auto-renewals (called by scheduler command).
     */
    public function processAutoRenewals(): int
    {
        $count = 0;

        $renewable = UserSubscription::query()
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Grace->value])
            ->where('auto_renew', true)
            ->whereNull('cancelled_at')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('status', SubscriptionStatus::Active->value)
                        ->whereNotNull('current_period_end')
                        ->where('current_period_end', '<=', now()->addHours(1));
                })->orWhere(function ($query) {
                    $query->where('status', SubscriptionStatus::Grace->value)
                        ->where(function ($query) {
                            $query->whereNull('grace_ends_at')
                                ->orWhere('grace_ends_at', '>', now());
                        });
                });
            })
            ->with(['user', 'plan.prices'])
            ->get();

        foreach ($renewable as $sub) {
            try {
                $this->renew($sub);
                $count++;
            } catch (\Throwable $e) {
                $this->handleRenewalFailure($sub);
            }
        }

        return $count;
    }

    private function activateSubscription(
        User $user,
        SubscriptionPlan $plan,
        BillingCycle $billingCycle,
        string $walletUuid,
        float $amountPaid,
        string $currencyCode,
        Carbon $now,
        ?string $trxId = null,
        bool $startTrial = false,
        ?string $notificationIdentifier = null,
        array $notificationExtra = []
    ): UserSubscription {
        $status    = $startTrial ? SubscriptionStatus::Trial : SubscriptionStatus::Active;
        $trialEnds = $startTrial ? $now->copy()->addDays($plan->trial_days) : null;

        $periodStart = $startTrial ? null : $now;
        $periodEnd   = $startTrial ? null : $billingCycle->calculateEndDate($now);

        $subscription = UserSubscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle'        => $billingCycle->value,
            'status'               => $status->value,
            'started_at'           => $now,
            'trial_ends_at'        => $trialEnds,
            'current_period_start' => $periodStart,
            'current_period_end'   => $periodEnd,
            'grace_ends_at'        => null,
            'auto_renew'           => $plan->auto_renew_default,
            'amount_paid'          => $amountPaid,
            'currency_code'        => $currencyCode,
            'wallet_reference'     => $walletUuid,
        ]);

        if ($amountPaid > 0) {
            SubscriptionTransaction::create([
                'user_subscription_id' => $subscription->id,
                'user_id'              => $user->id,
                'subscription_plan_id' => $plan->id,
                'trx_id'               => $trxId,
                'type'                 => 'new',
                'amount'               => $amountPaid,
                'currency_code'        => $currencyCode,
                'status'               => 'completed',
            ]);
        }

        $this->notifySubscriptionUser(
            $subscription,
            $notificationIdentifier ?? ($startTrial ? 'subscription_user_trial_started' : 'subscription_user_started'),
            array_merge([
                'amount' => $this->formatNotificationAmount($amountPaid, $currencyCode),
                'trx'    => $trxId ?? __('N/A'),
            ], $notificationExtra)
        );

        return $subscription;
    }

    private function shouldStartTrial(SubscriptionPlan $plan, float $price): bool
    {
        return $price > 0 && $plan->hasTrial();
    }

    private function subscriptionBillingCycle(UserSubscription $subscription): BillingCycle
    {
        if ($subscription->billing_cycle instanceof BillingCycle) {
            return $subscription->billing_cycle;
        }

        return $subscription->billing_cycle
            ? BillingCycle::from($subscription->billing_cycle)
            : BillingCycle::Monthly;
    }

    private function priceForCycle(SubscriptionPlan $plan, BillingCycle $billingCycle): ?float
    {
        $plan->loadMissing('prices');

        $price = $plan->prices->first(
            fn ($price) => $this->billingCycleValue($price->billing_cycle) === $billingCycle->value
        );

        return $price ? (float) $price->price : null;
    }

    private function billingCycleValue(BillingCycle|string|null $billingCycle): ?string
    {
        return $billingCycle instanceof BillingCycle ? $billingCycle->value : $billingCycle;
    }

    private function lockedDefaultWallet(User $user): ?Wallet
    {
        $wallet = $this->walletService->getDefaultWallet($user);

        if (! $wallet) {
            return null;
        }

        return Wallet::query()->whereKey($wallet->id)->lockForUpdate()->first();
    }

    private function processExpiredTrials(): int
    {
        $count = 0;

        $trials = UserSubscription::query()
            ->where('status', SubscriptionStatus::Trial->value)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->with(['user', 'plan.prices'])
            ->get();

        foreach ($trials as $subscription) {
            if ($subscription->cancelled_at !== null) {
                $subscription->update(['status' => SubscriptionStatus::Cancelled->value]);
                $count++;

                continue;
            }

            if ($subscription->auto_renew) {
                try {
                    $this->convertTrialToActive($subscription);
                } catch (\Throwable $e) {
                    $this->moveToGraceOrExpire($subscription);
                }
            } else {
                $this->moveToGraceOrExpire($subscription);
            }

            $count++;
        }

        return $count;
    }

    private function convertTrialToActive(UserSubscription $subscription): UserSubscription
    {
        return DB::transaction(function () use ($subscription): UserSubscription {
            $subscription = UserSubscription::query()
                ->whereKey($subscription->id)
                ->lockForUpdate()
                ->with(['user', 'plan.prices'])
                ->firstOrFail();

            if ($subscription->status !== SubscriptionStatus::Trial) {
                return $subscription;
            }

            $user         = $subscription->user;
            $plan         = $subscription->plan;
            $billingCycle = $this->subscriptionBillingCycle($subscription);
            $price        = $this->priceForCycle($plan, $billingCycle);
            $currencyCode = $subscription->currency_code ?? siteCurrency('code');
            $wallet       = null;
            $trxId        = null;

            if ($price === null) {
                throw new NotifyErrorException(__('The selected billing cycle is not available for this plan.'));
            }

            if ($price > 0) {
                $wallet = $this->lockedDefaultWallet($user);

                if (! $wallet) {
                    throw new NotifyErrorException(__('No wallet found.'));
                }

                if ($wallet->balance < $price) {
                    throw new NotifyErrorException(__('Insufficient wallet balance for subscription activation.'));
                }

                $this->walletService->subtractMoney($wallet, $price);

                $trxData = new TransactionData(
                    user_id: $user->id,
                    trx_type: TrxType::SUBSCRIPTION,
                    amount: $price,
                    amount_flow: AmountFlow::MINUS,
                    fee: 0,
                    currency: $currencyCode,
                    provider: 'wallet',
                    processing_type: MethodType::AUTOMATIC,
                    net_amount: $price,
                    payable_amount: $price,
                    payable_currency: $currencyCode,
                    wallet_reference: $wallet->uuid,
                    trx_data: [
                        'plan_id'              => $plan->id,
                        'plan_name'            => $plan->name,
                        'billing_cycle'        => $billingCycle->value,
                        'user_subscription_id' => $subscription->id,
                        'trial_conversion'     => true,
                    ],
                    description: __('Subscription Trial Conversion: :plan', ['plan' => $plan->name]),
                    status: TrxStatus::COMPLETED,
                );

                $transaction = $this->transactionService->create($trxData);
                $trxId       = $transaction->trx_id;
            }

            $now = Carbon::now();

            $subscription->update([
                'status'               => SubscriptionStatus::Active->value,
                'trial_ends_at'        => null,
                'current_period_start' => $now,
                'current_period_end'   => $billingCycle->calculateEndDate($now),
                'grace_ends_at'        => null,
                'amount_paid'          => (float) $price,
                'wallet_reference'     => $wallet?->uuid ?? $subscription->wallet_reference,
            ]);

            if ($price > 0) {
                SubscriptionTransaction::create([
                    'user_subscription_id' => $subscription->id,
                    'user_id'              => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'trx_id'               => $trxId,
                    'type'                 => 'trial_conversion',
                    'amount'               => $price,
                    'currency_code'        => $currencyCode,
                    'status'               => 'completed',
                ]);
            }

            $converted = $subscription->fresh();

            $this->notifySubscriptionUser($converted, 'subscription_user_trial_converted', [
                'amount' => $this->formatNotificationAmount((float) $price, $currencyCode),
                'trx'    => $trxId ?? __('N/A'),
            ]);

            return $converted;
        });
    }

    private function moveToGraceOrExpire(UserSubscription $subscription): void
    {
        $subscription->loadMissing('plan');

        if ((int) $subscription->plan->grace_days > 0) {
            $graceStartsAt = $subscription->current_period_end && now()->isBefore($subscription->current_period_end)
                ? $subscription->current_period_end
                : Carbon::now();

            $subscription->update([
                'status'             => SubscriptionStatus::Grace->value,
                'current_period_end' => $subscription->current_period_end ?? $graceStartsAt,
                'grace_ends_at'      => $graceStartsAt->copy()->addDays($subscription->plan->grace_days),
            ]);

            $this->notifySubscriptionUser($subscription->fresh(), 'subscription_user_grace_started');

            return;
        }

        $subscription->update([
            'status'        => SubscriptionStatus::Expired->value,
            'grace_ends_at' => null,
        ]);

        $this->notifySubscriptionUser($subscription->fresh(), 'subscription_user_expired');
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function notifySubscriptionUser(UserSubscription $subscription, string $identifier, array $extra = []): void
    {
        if (! NotificationTemplate::query()->where('identifier', $identifier)->exists()) {
            return;
        }

        $subscription->loadMissing(['user', 'plan']);

        if (! $subscription->user) {
            return;
        }

        $subscription->user->notify(new TemplateNotification(
            identifier: $identifier,
            data: array_merge($this->subscriptionNotificationData($subscription), $this->normalizeNotificationData($extra)),
            action: route('user.subscription.current')
        ));
    }

    /**
     * @return array<string, string>
     */
    private function subscriptionNotificationData(UserSubscription $subscription): array
    {
        $subscription->loadMissing('plan');

        $billingCycle = $this->subscriptionBillingCycle($subscription);
        $status       = $subscription->status instanceof SubscriptionStatus
            ? $subscription->status->label()
            : title((string) $subscription->status);
        $currencyCode = $subscription->currency_code ?? siteCurrency('code');

        return [
            'plan'          => $subscription->plan?->name ?? __('Subscription plan'),
            'cycle'         => $billingCycle->label(),
            'status'        => $status,
            'amount'        => $this->formatNotificationAmount((float) $subscription->amount_paid, $currencyCode),
            'period_end'    => $this->formatNotificationDate($subscription->current_period_end),
            'trial_ends_at' => $this->formatNotificationDate($subscription->trial_ends_at),
            'grace_ends_at' => $this->formatNotificationDate($subscription->grace_ends_at),
            'auto_renew'    => $subscription->auto_renew ? __('Enabled') : __('Disabled'),
            'trx'           => __('N/A'),
            'cancelled_by'  => $subscription->cancelled_by_admin ? __('Admin') : __('User'),
            'previous_plan' => __('Previous plan'),
            'credit'        => $this->formatNotificationAmount(0, $currencyCode),
            'charge'        => $this->formatNotificationAmount(0, $currencyCode),
        ];
    }

    private function formatNotificationAmount(float $amount, ?string $currencyCode): string
    {
        return trim(($currencyCode ?: siteCurrency('code')).' '.number_format($amount, 2));
    }

    private function formatNotificationDate(mixed $value): string
    {
        if (! $value) {
            return __('Not available');
        }

        return Carbon::parse($value)->format('d M Y, h:i A');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function normalizeNotificationData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[$key] = match (true) {
                $value === null => '',
                is_bool($value) => $value ? __('Yes') : __('No'),
                default         => (string) $value,
            };
        }

        return $normalized;
    }

    private function handleRenewalFailure(UserSubscription $subscription): void
    {
        if ($subscription->current_period_end && now()->isBefore($subscription->current_period_end)) {
            return;
        }

        $this->moveToGraceOrExpire($subscription);
    }
}
