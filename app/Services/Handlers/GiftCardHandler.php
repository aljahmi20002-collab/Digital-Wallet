<?php

namespace App\Services\Handlers;

use App\Enums\AmountFlow;
use App\Models\Transaction;
use App\Services\Handlers\Interfaces\SuccessHandlerInterface;
use App\Services\TransactionNotifierService;
use Throwable;
use Wallet;

class GiftCardHandler implements SuccessHandlerInterface
{
    public function __construct(protected TransactionNotifierService $notifier) {}

    public function handleSuccess(Transaction $transaction): void
    {
        if ($transaction->amount_flow === AmountFlow::PLUS) {
            Wallet::addMoneyByWalletUuid($transaction->wallet_reference, $transaction->net_amount);

            /*
             * The notification template is best-effort: if the
             * `gift_card_redeemed` template isn't seeded yet (or the
             * notification channel transport fails) we must NOT abort
             * the redeem — the money was already credited and the
             * transaction is committed. Swallowing here keeps the
             * user-facing flow clean and logs the failure for ops.
             */
            try {
                $this->notifier->toUser($transaction, 'gift_card_redeemed', [
                    'amount'    => $transaction->amount.' '.$transaction->currency,
                    'gift_code' => $transaction->gift_card_code ?? 'N/A',
                    'trx'       => $transaction->trx_id,
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }
}
