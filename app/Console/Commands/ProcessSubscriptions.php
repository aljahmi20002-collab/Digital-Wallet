<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'subscription:process {--renewals : Also process auto-renewals}';

    protected $description = 'Expire overdue subscriptions and optionally process auto-renewals.';

    public function handle(SubscriptionService $subscriptionService): int
    {
        if ($this->option('renewals')) {
            $renewed = $subscriptionService->processAutoRenewals();
            $this->info(__('Processed :count auto-renewals.', ['count' => $renewed]));
        }

        $expired = $subscriptionService->processExpiries();
        $this->info(__('Processed :count subscription expiries.', ['count' => $expired]));

        return self::SUCCESS;
    }
}
