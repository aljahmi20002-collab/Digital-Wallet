<?php

namespace App\Services;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\MethodType;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\WalletEarnPayoutFrequency;
use App\Enums\WalletEarnProfitType;
use App\Enums\WalletEarnStatus;
use App\Exceptions\NotifyErrorException;
use App\Models\Admin;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletEarnPlan;
use App\Models\WalletEarnReward;
use App\Models\WalletEarnStake;
use App\Notifications\TemplateNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class WalletEarnService
{
    public function __construct(
        private readonly TransactionService $transactions,
    ) {}

    /**
     * @throws NotifyErrorException
     */
    public function createStake(User $user, WalletEarnPlan $plan, Wallet $wallet, float $amount): WalletEarnStake
    {
        return DB::transaction(function () use ($user, $plan, $wallet, $amount): WalletEarnStake {
            $plan   = WalletEarnPlan::query()->lockForUpdate()->findOrFail($plan->id);
            $wallet = Wallet::query()
                ->with('currency')
                ->whereKey($wallet->id)
                ->where('user_id', $user->id)
                ->where('status', true)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw new NotifyErrorException(__('Invalid wallet selected.'));
            }

            $amount = $this->money($amount);
            $this->assertPlanAcceptsStake($plan, $wallet, $amount);

            $debited = Wallet::query()
                ->whereKey($wallet->id)
                ->where('balance', '>=', $amount)
                ->decrement('balance', $amount);

            if ($debited !== 1) {
                throw new NotifyErrorException(__('Insufficient wallet balance.'));
            }

            $status       = $plan->auto_approve ? WalletEarnStatus::Active : WalletEarnStatus::Pending;
            $startsAt     = $plan->auto_approve ? now() : null;
            $maturesAt    = $startsAt ? $this->calculateMaturityDate($startsAt, $plan->duration_value, $plan->duration_unit) : null;
            $totalPayouts = $startsAt && $maturesAt
                ? $this->calculateScheduledPayouts($startsAt, $plan->payout_frequency, $maturesAt)
                : $this->calculateTotalPayouts($plan->duration_value, $plan->duration_unit, $plan->payout_frequency);
            $expectedProfit = $this->money($this->calculatePayoutAmount($amount, $plan->profit_rate, $plan->profit_type) * $totalPayouts);
            $nextPayoutAt   = $startsAt && $maturesAt
                ? $this->calculateNextPayoutDate($startsAt, $plan->payout_frequency, $maturesAt)
                : null;

            $stake = WalletEarnStake::query()->create([
                'user_id'             => $user->id,
                'wallet_earn_plan_id' => $plan->id,
                'wallet_id'           => $wallet->id,
                'currency_id'         => $wallet->currency_id,
                'plan_name'           => $plan->name,
                'principal_amount'    => $amount,
                'profit_rate'         => $plan->profit_rate,
                'profit_type'         => $plan->profit_type,
                'duration_value'      => $plan->duration_value,
                'duration_unit'       => $plan->duration_unit,
                'payout_frequency'    => $plan->payout_frequency,
                'return_principal'    => $plan->return_principal,
                'expected_profit'     => $expectedProfit,
                'paid_profit'         => 0,
                'total_payouts'       => $totalPayouts,
                'payouts_made'        => 0,
                'status'              => $status,
                'starts_at'           => $startsAt,
                'next_payout_at'      => $nextPayoutAt,
                'matures_at'          => $maturesAt,
            ]);

            $transaction = $this->transactions->create(new TransactionData(
                user_id: $user->id,
                trx_type: TrxType::WALLET_EARN_STAKE,
                amount: $amount,
                amount_flow: AmountFlow::MINUS,
                fee: 0,
                currency: $wallet->currency->code,
                provider: 'wallet_earn',
                processing_type: MethodType::AUTOMATIC,
                net_amount: $amount,
                payable_amount: $amount,
                payable_currency: $wallet->currency->code,
                wallet_reference: $wallet->uuid,
                trx_reference: 'wallet_earn_stake_'.$stake->id,
                trx_data: [
                    'stake_id'      => $stake->id,
                    'plan_id'       => $plan->id,
                    'plan_name'     => $plan->name,
                    'auto_approved' => $plan->auto_approve,
                ],
                remarks: __('Wallet Earn stake created.'),
                description: 'wallet earn stake',
                status: TrxStatus::COMPLETED,
            ));

            $stake->update(['trx_id' => $transaction->trx_id]);

            $stake = $stake->refresh();

            $this->notifyWithTemplate(
                $user,
                'wallet_earn_user_stake_created',
                $this->stakeNotificationData($stake),
                action: $this->userStakeAction($stake)
            );

            if ($stake->status === WalletEarnStatus::Pending) {
                $this->notifyAdminsWithTemplate(
                    'wallet-earn-manage',
                    'wallet_earn_admin_stake_pending',
                    $this->stakeNotificationData($stake),
                    $user,
                    $this->adminStakeAction($stake)
                );
            }

            return $stake;
        }, 3);
    }

    /**
     * @throws NotifyErrorException
     */
    public function approve(WalletEarnStake $stake, ?Admin $admin = null, ?string $note = null): WalletEarnStake
    {
        return DB::transaction(function () use ($stake, $admin, $note): WalletEarnStake {
            $stake = WalletEarnStake::query()->lockForUpdate()->findOrFail($stake->id);

            if ($stake->status !== WalletEarnStatus::Pending) {
                throw new NotifyErrorException(__('Only pending stakes can be approved.'));
            }

            $startsAt     = now();
            $maturesAt    = $this->calculateMaturityDate($startsAt, $stake->duration_value, $stake->duration_unit);
            $totalPayouts = $this->calculateScheduledPayouts($startsAt, $stake->payout_frequency, $maturesAt);

            $stake->update([
                'status'          => WalletEarnStatus::Active,
                'reviewed_by'     => $admin?->id,
                'review_note'     => $note,
                'expected_profit' => $this->money($this->calculatePayoutAmount($stake->principal_amount, $stake->profit_rate, $stake->profit_type) * $totalPayouts),
                'total_payouts'   => $totalPayouts,
                'starts_at'       => $startsAt,
                'matures_at'      => $maturesAt,
                'next_payout_at'  => $this->calculateNextPayoutDate($startsAt, $stake->payout_frequency, $maturesAt),
            ]);

            $stake = $stake->refresh();

            $this->notifyWithTemplate(
                $stake->user,
                'wallet_earn_user_stake_approved',
                $this->stakeNotificationData($stake),
                $admin,
                $this->userStakeAction($stake)
            );

            return $stake;
        }, 3);
    }

    /**
     * @throws NotifyErrorException
     */
    public function reject(WalletEarnStake $stake, ?Admin $admin = null, ?string $note = null): WalletEarnStake
    {
        return DB::transaction(function () use ($stake, $admin, $note): WalletEarnStake {
            $stake = WalletEarnStake::query()->with(['wallet.currency'])->lockForUpdate()->findOrFail($stake->id);

            if ($stake->status !== WalletEarnStatus::Pending) {
                throw new NotifyErrorException(__('Only pending stakes can be rejected.'));
            }

            $this->refundPrincipal($stake, 'wallet earn rejected principal', __('Wallet Earn stake rejected and principal returned.'));

            $stake->update([
                'status'         => WalletEarnStatus::Rejected,
                'reviewed_by'    => $admin?->id,
                'review_note'    => $note,
                'rejected_at'    => now(),
                'next_payout_at' => null,
            ]);

            $stake = $stake->refresh();

            $this->notifyWithTemplate(
                $stake->user,
                'wallet_earn_user_stake_rejected',
                $this->stakeNotificationData($stake),
                $admin,
                $this->userStakeAction($stake)
            );

            return $stake;
        }, 3);
    }

    /**
     * @throws NotifyErrorException
     */
    public function cancel(WalletEarnStake $stake, ?Admin $admin = null, ?string $note = null): WalletEarnStake
    {
        return DB::transaction(function () use ($stake, $admin, $note): WalletEarnStake {
            $stake = WalletEarnStake::query()->with(['wallet.currency'])->lockForUpdate()->findOrFail($stake->id);

            if ($stake->status->isTerminal()) {
                throw new NotifyErrorException(__('This stake is already closed.'));
            }

            $this->refundPrincipal($stake, 'wallet earn canceled principal', __('Wallet Earn stake canceled and principal returned.'));

            $stake->update([
                'status'         => WalletEarnStatus::Canceled,
                'reviewed_by'    => $admin?->id,
                'review_note'    => $note,
                'canceled_at'    => now(),
                'next_payout_at' => null,
            ]);

            $stake = $stake->refresh();

            $this->notifyWithTemplate(
                $stake->user,
                'wallet_earn_user_stake_canceled',
                $this->stakeNotificationData($stake),
                $admin,
                $this->userStakeAction($stake)
            );

            return $stake;
        }, 3);
    }

    /**
     * @throws NotifyErrorException
     */
    public function complete(WalletEarnStake $stake): WalletEarnStake
    {
        return DB::transaction(function () use ($stake): WalletEarnStake {
            $stake = WalletEarnStake::query()->with(['wallet.currency'])->lockForUpdate()->findOrFail($stake->id);

            if ($stake->status !== WalletEarnStatus::Active) {
                throw new NotifyErrorException(__('Only active stakes can be completed.'));
            }

            if ($stake->next_payout_at && $stake->next_payout_at->lessThanOrEqualTo(now())) {
                $this->processLockedStakeDuePayouts($stake);
                $stake->refresh();
            }

            if ($stake->status !== WalletEarnStatus::Active) {
                return $stake->refresh();
            }

            $this->completeLockedStake($stake);

            return $stake->refresh();
        }, 3);
    }

    public function processDueRewards(int $limit = 100): array
    {
        $stakes = WalletEarnStake::query()
            ->where('status', WalletEarnStatus::Active->value)
            ->whereNotNull('next_payout_at')
            ->where('next_payout_at', '<=', now())
            ->orderBy('next_payout_at')
            ->limit($limit)
            ->pluck('id');

        $processed = 0;
        $completed = 0;
        $failed    = 0;

        foreach ($stakes as $stakeId) {
            try {
                $result = $this->processStakePayout((int) $stakeId);
                $processed += $result['payouts'];
                $completed += $result['completed'] ? 1 : 0;
            } catch (\Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        return compact('processed', 'completed', 'failed');
    }

    public function calculateTotalPayouts(int $durationValue, string $durationUnit, WalletEarnPayoutFrequency $frequency): int
    {
        $durationHours = match ($durationUnit) {
            'hours'  => $durationValue,
            'months' => $durationValue * 24 * 30,
            default  => $durationValue * 24,
        };

        return match ($frequency) {
            WalletEarnPayoutFrequency::Daily     => max(1, (int) ceil($durationHours / 24)),
            WalletEarnPayoutFrequency::Weekly    => max(1, (int) ceil($durationHours / (24 * 7))),
            WalletEarnPayoutFrequency::Monthly   => max(1, (int) ceil($durationHours / (24 * 30))),
            WalletEarnPayoutFrequency::EndOfTerm => 1,
        };
    }

    public function calculateScheduledPayouts(CarbonInterface $startsAt, WalletEarnPayoutFrequency $frequency, CarbonInterface $maturesAt): int
    {
        $payouts = 0;
        $cursor  = $startsAt->copy();

        do {
            $next = $this->calculateNextPayoutDate($cursor, $frequency, $maturesAt);
            $payouts++;

            if ($next->greaterThanOrEqualTo($maturesAt)) {
                break;
            }

            $cursor = $next;
        } while ($payouts < 10000);

        return max(1, $payouts);
    }

    public function calculatePayoutAmount(float $principal, float $rate, WalletEarnProfitType $profitType): float
    {
        return $this->money(match ($profitType) {
            WalletEarnProfitType::Fixed      => $rate,
            WalletEarnProfitType::Percentage => $principal * ($rate / 100),
        });
    }

    public function calculateMaturityDate(CarbonInterface $startsAt, int $durationValue, string $durationUnit): CarbonInterface
    {
        $durationValue = max(1, $durationValue);

        return match ($durationUnit) {
            'hours'  => $startsAt->copy()->addHours($durationValue),
            'months' => $startsAt->copy()->addMonthsNoOverflow($durationValue),
            default  => $startsAt->copy()->addDays($durationValue),
        };
    }

    public function calculateNextPayoutDate(CarbonInterface $from, WalletEarnPayoutFrequency $frequency, CarbonInterface $maturesAt): CarbonInterface
    {
        $next = match ($frequency) {
            WalletEarnPayoutFrequency::Daily     => $from->copy()->addDay(),
            WalletEarnPayoutFrequency::Weekly    => $from->copy()->addWeek(),
            WalletEarnPayoutFrequency::Monthly   => $from->copy()->addMonthNoOverflow(),
            WalletEarnPayoutFrequency::EndOfTerm => $maturesAt->copy(),
        };

        return $next->greaterThan($maturesAt) ? $maturesAt->copy() : $next;
    }

    private function processStakePayout(int $stakeId): array
    {
        return DB::transaction(function () use ($stakeId): array {
            $stake = WalletEarnStake::query()
                ->with(['wallet.currency'])
                ->lockForUpdate()
                ->findOrFail($stakeId);

            if ($stake->status !== WalletEarnStatus::Active || ! $stake->next_payout_at || $stake->next_payout_at->greaterThan(now())) {
                return ['paid' => false, 'completed' => false, 'payouts' => 0];
            }

            $result = $this->processLockedStakeDuePayouts($stake);

            return [
                'paid'      => $result['payouts'] > 0,
                'completed' => $result['completed'],
                'payouts'   => $result['payouts'],
            ];
        }, 3);
    }

    /**
     * @return array{payouts: int, completed: bool}
     */
    private function processLockedStakeDuePayouts(WalletEarnStake $stake): array
    {
        if (! $stake->matures_at) {
            return ['payouts' => 0, 'completed' => false];
        }

        $wallet = Wallet::query()
            ->with('currency')
            ->whereKey($stake->wallet_id)
            ->lockForUpdate()
            ->firstOrFail();

        $payouts   = 0;
        $completed = false;

        while (
            $stake->status === WalletEarnStatus::Active
            && $stake->next_payout_at
            && $stake->next_payout_at->lessThanOrEqualTo(now())
            && $stake->payouts_made < $stake->total_payouts
        ) {
            $payoutNumber = $stake->payouts_made + 1;

            if (WalletEarnReward::query()->where('wallet_earn_stake_id', $stake->id)->where('payout_number', $payoutNumber)->exists()) {
                break;
            }

            $scheduledAt = $stake->next_payout_at->copy();
            $amount      = $this->calculatePayoutAmount($stake->principal_amount, $stake->profit_rate, $stake->profit_type);

            $wallet->increment('balance', $amount);

            $transaction = $this->transactions->create(new TransactionData(
                user_id: $stake->user_id,
                trx_type: TrxType::WALLET_EARN_REWARD,
                amount: $amount,
                amount_flow: AmountFlow::PLUS,
                fee: 0,
                currency: $wallet->currency->code,
                provider: 'wallet_earn',
                processing_type: MethodType::SYSTEM,
                net_amount: $amount,
                payable_amount: $amount,
                payable_currency: $wallet->currency->code,
                wallet_reference: $wallet->uuid,
                trx_reference: 'wallet_earn_stake_'.$stake->id.'_payout_'.$payoutNumber,
                trx_data: [
                    'stake_id'      => $stake->id,
                    'payout_number' => $payoutNumber,
                ],
                remarks: __('Wallet Earn reward payout #:number', ['number' => $payoutNumber]),
                description: 'wallet earn reward',
                status: TrxStatus::COMPLETED,
            ));

            WalletEarnReward::query()->create([
                'wallet_earn_stake_id' => $stake->id,
                'user_id'              => $stake->user_id,
                'wallet_id'            => $stake->wallet_id,
                'currency_id'          => $stake->currency_id,
                'transaction_id'       => $transaction->id,
                'amount'               => $amount,
                'payout_number'        => $payoutNumber,
                'scheduled_at'         => $scheduledAt,
                'paid_at'              => now(),
                'status'               => 'paid',
            ]);

            $stake->paid_profit  = $this->money($stake->paid_profit + $amount);
            $stake->payouts_made = $payoutNumber;
            $payouts++;

            $shouldComplete = $payoutNumber >= $stake->total_payouts
                || ($stake->matures_at && $scheduledAt->greaterThanOrEqualTo($stake->matures_at));

            if ($shouldComplete) {
                $stake->save();
                $this->notifyWithTemplate(
                    $stake->user,
                    'wallet_earn_user_reward_paid',
                    $this->stakeNotificationData($stake, $amount, $payoutNumber),
                    action: $this->userStakeAction($stake)
                );
                $this->completeLockedStake($stake);
                $completed = true;

                break;
            }

            $stake->next_payout_at = $this->calculateNextPayoutDate($scheduledAt, $stake->payout_frequency, $stake->matures_at);
            $stake->save();
            $this->notifyWithTemplate(
                $stake->user,
                'wallet_earn_user_reward_paid',
                $this->stakeNotificationData($stake, $amount, $payoutNumber),
                action: $this->userStakeAction($stake)
            );
        }

        return ['payouts' => $payouts, 'completed' => $completed];
    }

    /**
     * @throws NotifyErrorException
     */
    private function assertPlanAcceptsStake(WalletEarnPlan $plan, Wallet $wallet, float $amount): void
    {
        if (! $plan->status) {
            throw new NotifyErrorException(__('This Wallet Earn plan is not available.'));
        }

        if (! $plan->supportsCurrency((int) $wallet->currency_id)) {
            throw new NotifyErrorException(__('This plan does not support the selected wallet currency.'));
        }

        if ($amount < (float) $plan->minimum_amount) {
            throw new NotifyErrorException(__('Amount is below the plan minimum.'));
        }

        if ($plan->maximum_amount !== null && $amount > (float) $plan->maximum_amount) {
            throw new NotifyErrorException(__('Amount exceeds the plan maximum.'));
        }
    }

    private function completeLockedStake(WalletEarnStake $stake): void
    {
        if ($stake->return_principal) {
            $this->refundPrincipal($stake, 'wallet earn principal return', __('Wallet Earn principal returned at maturity.'));
        }

        $stake->update([
            'status'         => WalletEarnStatus::Completed,
            'completed_at'   => now(),
            'next_payout_at' => null,
        ]);

        $stake->refresh();

        $this->notifyWithTemplate(
            $stake->user,
            'wallet_earn_user_stake_completed',
            $this->stakeNotificationData($stake),
            action: $this->userStakeAction($stake)
        );
    }

    private function refundPrincipal(WalletEarnStake $stake, string $description, string $remarks): void
    {
        $wallet = Wallet::query()
            ->with('currency')
            ->whereKey($stake->wallet_id)
            ->lockForUpdate()
            ->firstOrFail();

        $amount = $this->money($stake->principal_amount);
        $wallet->increment('balance', $amount);

        $this->transactions->create(new TransactionData(
            user_id: $stake->user_id,
            trx_type: TrxType::WALLET_EARN_PRINCIPAL,
            amount: $amount,
            amount_flow: AmountFlow::PLUS,
            fee: 0,
            currency: $wallet->currency->code,
            provider: 'wallet_earn',
            processing_type: MethodType::SYSTEM,
            net_amount: $amount,
            payable_amount: $amount,
            payable_currency: $wallet->currency->code,
            wallet_reference: $wallet->uuid,
            trx_reference: 'wallet_earn_stake_'.$stake->id,
            trx_data: [
                'stake_id' => $stake->id,
            ],
            remarks: $remarks,
            description: $description,
            status: TrxStatus::COMPLETED,
        ));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function notifyAdminsWithTemplate(string $permission, string $identifier, array $data, mixed $sender = null, ?string $action = null): void
    {
        if (! NotificationTemplate::query()->where('identifier', $identifier)->exists()) {
            return;
        }

        try {
            $admins = Admin::permission($permission)->get();
        } catch (PermissionDoesNotExist) {
            return;
        }

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new TemplateNotification(
            identifier: $identifier,
            data: $data,
            sender: $sender,
            action: $action
        ));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function notifyWithTemplate(mixed $notifiable, string $identifier, array $data, mixed $sender = null, ?string $action = null): void
    {
        if (! $notifiable || ! NotificationTemplate::query()->where('identifier', $identifier)->exists()) {
            return;
        }

        $notifiable->notify(new TemplateNotification(
            identifier: $identifier,
            data: $data,
            sender: $sender,
            action: $action
        ));
    }

    /**
     * @return array<string, string>
     */
    private function stakeNotificationData(WalletEarnStake $stake, float|int|string|null $profit = null, ?int $payoutNumber = null): array
    {
        $stake->loadMissing(['user', 'currency', 'wallet.currency']);

        $currency = (string) ($stake->currency?->code ?? $stake->wallet?->currency?->code ?? siteCurrency('code') ?? '');

        return [
            'user'               => (string) ($stake->user?->name ?? __('User')),
            'plan'               => (string) $stake->plan_name,
            'amount'             => $this->formatMoney($stake->principal_amount, $currency),
            'principal'          => $this->formatMoney($stake->principal_amount, $currency),
            'profit'             => $this->formatMoney($profit ?? $stake->paid_profit, $currency),
            'expected_profit'    => $this->formatMoney($stake->expected_profit, $currency),
            'paid_profit'        => $this->formatMoney($stake->paid_profit, $currency),
            'currency'           => $currency,
            'status'             => (string) $stake->status->label(),
            'payout_number'      => (string) ($payoutNumber ?? $stake->payouts_made),
            'next_payout_at'     => $this->formatDate($stake->next_payout_at),
            'maturity_date'      => $this->formatDate($stake->matures_at),
            'review_note'        => filled($stake->review_note) ? (string) $stake->review_note : (string) __('No note provided.'),
            'principal_returned' => $stake->return_principal ? (string) __('Yes') : (string) __('No'),
            'trx'                => (string) ($stake->trx_id ?: __('N/A')),
        ];
    }

    private function formatMoney(float|int|string $amount, string $currency): string
    {
        return trim(number_format((float) $amount, $this->decimalPlaces()).' '.$currency);
    }

    private function formatDate(?CarbonInterface $date): string
    {
        return $date?->copy()->format('d M Y, h:i A') ?? (string) __('Not scheduled');
    }

    private function decimalPlaces(): int
    {
        return max(2, min(8, (int) setting('site_decimal', 2)));
    }

    private function userStakeAction(WalletEarnStake $stake): string
    {
        return route('user.wallet-earn.show', $stake);
    }

    private function adminStakeAction(WalletEarnStake $stake): string
    {
        return route('admin.wallet-earn.stakes.show', $stake);
    }

    private function money(float|int|string $amount): float
    {
        return round((float) $amount, 8);
    }
}
