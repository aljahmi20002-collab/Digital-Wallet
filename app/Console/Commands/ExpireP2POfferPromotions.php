<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\P2P\PromotionStatus;
use App\Models\P2P\OfferPromotion;
use Illuminate\Console\Command;

class ExpireP2POfferPromotions extends Command
{
    protected $signature = 'p2p:promotions:expire';

    protected $description = 'Expire active P2P offer promotions that passed their end time';

    public function handle(): int
    {
        $now     = now();
        $expired = 0;

        OfferPromotion::query()
            ->where('status', PromotionStatus::ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->chunkById(200, function ($promotions) use (&$expired) {
                foreach ($promotions as $promotion) {
                    $promotion->update([
                        'status' => PromotionStatus::EXPIRED,
                    ]);
                    $expired++;
                }
            });

        $this->info("Expired {$expired} promotions.");

        return self::SUCCESS;
    }
}
