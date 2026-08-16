<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\TrxStatus;
use App\Enums\VirtualCard\VirtualCardStatus;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\VirtualCard;
use App\Services\Bitnob\BitnobDepositService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Bitnob webhook receiver.
 *
 * Auth is enforced by the `bitnob.signature` middleware. We then dispatch
 * the event through `config('bitnob.webhook_events')` so adding a new event
 * is one row in config + one method here. ALL paths return 200 so Bitnob
 * doesn't enter its 3-retry storm — failures are logged for ops review.
 */
class BitnobWebhookController extends Controller
{
    public function __construct(protected BitnobDepositService $deposits)
    {
        //
    }

    public function __invoke(Request $request): JsonResponse
    {
        $event = (string) $request->input('event', '');
        $map   = (array) config('bitnob.webhook_events', []);

        if (! isset($map[$event])) {
            Log::info('Bitnob webhook received unknown event', ['event' => $event]);

            return response()->json(['ok' => true, 'ignored' => $event]);
        }

        $method = $map[$event];

        try {
            $this->{$method}($request->all());
        } catch (\Throwable $e) {
            Log::error('Bitnob webhook handler error', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
            // Still 200 — Bitnob retries on non-200, and we don't want to
            // store-and-replay duplicate side effects.
        }

        return response()->json(['ok' => true]);
    }

    /* ------------------------- Card events --------------------------- */

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardCreated(array $event): void
    {
        $cardId = $event['cardId'] ?? $event['id'] ?? null;
        $card   = $this->findCard($cardId, $event['reference'] ?? null);
        if (! $card) {
            return;
        }
        $card->update(['status' => VirtualCardStatus::Active->value]);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardCreationFailed(array $event): void
    {
        $card = $this->findCard($event['cardId'] ?? null, $event['reference'] ?? null);
        if (! $card) {
            return;
        }
        $card->update(['status' => VirtualCardStatus::Blocked->value]);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardTopupSuccess(array $event): void
    {
        $this->markCardTransactionStatus($event, TrxStatus::COMPLETED);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardTopupFailed(array $event): void
    {
        $this->markCardTransactionStatus($event, TrxStatus::FAILED);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardWithdrawalSuccess(array $event): void
    {
        $this->markCardTransactionStatus($event, TrxStatus::COMPLETED);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardWithdrawalFailed(array $event): void
    {
        $this->markCardTransactionStatus($event, TrxStatus::FAILED);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardDebit(array $event): void
    {
        // Authorization debit — append to the card's meta for audit. We don't
        // own the wallet ledger for card debits (Bitnob holds the float).
        $card = $this->findCard($event['cardId'] ?? null);
        if (! $card) {
            return;
        }
        $meta                  = $card->meta ?? [];
        $meta['last_debit']    = $event;
        $meta['last_debit_at'] = now()->toIso8601String();
        $card->update(['meta' => $meta]);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardReversal(array $event): void
    {
        $card = $this->findCard($event['cardId'] ?? null);
        if (! $card) {
            return;
        }
        $meta                  = $card->meta ?? [];
        $meta['last_reversal'] = $event;
        $card->update(['meta' => $meta]);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardDeclined(array $event): void
    {
        $card = $this->findCard($event['cardId'] ?? null);
        if (! $card) {
            return;
        }
        $meta                  = $card->meta ?? [];
        $meta['last_declined'] = $event;
        $card->update(['meta' => $meta]);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handleCardTerminated(array $event): void
    {
        $card = $this->findCard($event['cardId'] ?? null);
        if (! $card) {
            return;
        }
        $card->update(['status' => VirtualCardStatus::Blocked->value]);
    }

    /* ------------------------- Deposit events ------------------------ */

    /**
     * @param array<string, mixed> $event
     */
    protected function handleStablecoinDepositSuccess(array $event): void
    {
        $this->deposits->applyDepositSuccess($event);
    }

    /* ------------------------- Payout events ------------------------- */

    /**
     * @param array<string, mixed> $event
     */
    protected function handlePayoutSuccess(array $event): void
    {
        $this->markPayoutTransactionStatus($event, TrxStatus::COMPLETED);
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function handlePayoutFailed(array $event): void
    {
        $this->markPayoutTransactionStatus($event, TrxStatus::FAILED);
    }

    /* --------------------------- Helpers ----------------------------- */

    protected function findCard(?string $providerCardId, ?string $reference = null): ?VirtualCard
    {
        if ($providerCardId) {
            $byMeta = VirtualCard::query()->whereJsonContains('meta->card_id', $providerCardId)->first();
            if ($byMeta) {
                return $byMeta;
            }
            $byColumn = VirtualCard::query()->where('provider_card_id', $providerCardId)->first();
            if ($byColumn) {
                return $byColumn;
            }
        }
        if ($reference) {
            return VirtualCard::query()->whereJsonContains('meta->reference', $reference)->first();
        }

        return null;
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function markCardTransactionStatus(array $event, TrxStatus $status): void
    {
        $reference = (string) ($event['reference'] ?? '');
        if ($reference === '') {
            return;
        }
        $tx = Transaction::query()->where('trx_id', $reference)->first()
            ?? Transaction::query()->whereJsonContains('meta->bitnob_reference', $reference)->first();
        if (! $tx) {
            return;
        }
        $meta                 = $tx->meta ?? [];
        $meta['bitnob_event'] = $event;
        $tx->status           = $status;
        $tx->meta             = $meta;
        $tx->save();
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function markPayoutTransactionStatus(array $event, TrxStatus $status): void
    {
        $reference = (string) (
            data_get($event, 'data.reference')
            ?? data_get($event, 'data.transaction.reference')
            ?? data_get($event, 'data.paymentReference')
            ?? data_get($event, 'data.transactionReference')
            ?? data_get($event, 'reference')
            ?? ''
        );

        if ($reference === '') {
            return;
        }

        $tx = Transaction::query()
            ->where(function ($query) use ($reference) {
                $query->where('trx_id', $reference)
                    ->orWhere('trx_reference', $reference)
                    ->orWhere('trx_data->bitnob_withdraw->reference', $reference)
                    ->orWhere('trx_data->bitnob_withdraw->data->reference', $reference)
                    ->orWhere('trx_data->bitnob_withdraw->raw->data->reference', $reference);
            })
            ->latest('id')
            ->first();

        if (! $tx) {
            return;
        }

        $trxData                         = $tx->trx_data ?? [];
        $trxData['bitnob_payout_event']  = $event;
        $trxData['bitnob_payout_status'] = $status->value;

        $tx->update(['trx_data' => $trxData]);

        if ($tx->status !== TrxStatus::PENDING) {
            return;
        }

        if ($status === TrxStatus::COMPLETED) {
            app(TransactionService::class)->completeTransaction($tx->trx_id, __('Bitnob payout completed.'));
        } elseif ($status === TrxStatus::FAILED) {
            app(TransactionService::class)->cancelTransaction($tx->trx_id, __('Bitnob payout failed.'), true);
        }
    }
}
