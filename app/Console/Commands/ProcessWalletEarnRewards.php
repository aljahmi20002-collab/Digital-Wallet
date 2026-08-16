<?php

namespace App\Console\Commands;

use App\Services\WalletEarnService;
use Illuminate\Console\Command;

class ProcessWalletEarnRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet-earn:process {--limit=100 : Maximum due stakes to process in one run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process due Wallet Earn reward payouts and matured principal returns.';

    /**
     * Execute the console command.
     */
    public function handle(WalletEarnService $walletEarn): int
    {
        $result = $walletEarn->processDueRewards((int) $this->option('limit'));

        $this->info(__('Processed :processed payouts, completed :completed stakes, failed :failed.', $result));

        return self::SUCCESS;
    }
}
