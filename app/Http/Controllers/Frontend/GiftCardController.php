<?php

namespace App\Http\Controllers\Frontend;

use App\Constants\CurrencyRole;
use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\GiftCardStoreRequest;
use App\Mail\GiftCardDelivered;
use App\Models\GiftCard;
use App\Models\GiftCardTemplate;
use App\Models\User;
use App\Models\Wallet as WalletModel;
use App\Services\Handlers\GiftCardHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;
use Transaction;
use Wallet;

class GiftCardController extends Controller
{
    public function index(Request $request): View
    {
        $tab    = $request->input('tab', 'sent');
        $userId = Auth::id();

        $sentQuery = GiftCard::query()
            ->with(['template', 'currency', 'recipient'])
            ->where('user_id', $userId)
            ->latest();

        $receivedQuery = GiftCard::query()
            ->with(['template', 'currency', 'sender'])
            ->where(function ($q) use ($userId) {
                $q->where('recipient_user_id', $userId)
                    ->orWhere('recipient_email', Auth::user()?->email);
            })
            ->latest();

        $stats = [
            'sent'        => (clone $sentQuery)->count(),
            'received'    => (clone $receivedQuery)->count(),
            'total_value' => (clone $sentQuery)->sum('amount'),
            'unredeemed'  => (clone $receivedQuery)->whereIn('status', ['pending', 'delivered'])->count(),
        ];

        $giftCards = match ($tab) {
            'received' => $receivedQuery->paginate(8)->withQueryString(),
            'redeemed' => GiftCard::query()
                ->with(['template', 'currency'])
                ->where(function ($q) use ($userId) {
                    $q->where('redeemed_by', $userId)
                        ->orWhere('user_id', $userId);
                })
                ->where('status', 'redeemed')
                ->latest('redeemed_at')
                ->paginate(8)
                ->withQueryString(),
            default => $sentQuery->paginate(8)->withQueryString(),
        };

        // Wallets feed the redeem modal that lives on this same page —
        // the legacy standalone /redeem screen has been folded into a
        // Bootstrap modal so the page never reloads to redeem.
        $redeemWallets = WalletModel::where('user_id', $userId)
            ->where('status', true)
            ->with('currency')
            ->get();

        return view('frontend.user.gift-cards.index', compact('giftCards', 'tab', 'stats', 'redeemWallets'));
    }

    public function create(): View
    {
        $templates = GiftCardTemplate::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $categories = ['All', ...GiftCardTemplate::CATEGORIES];

        $wallets = WalletModel::where('user_id', Auth::id())
            ->whereHas('currency.roles', fn ($q) => $q->where('role_name', CurrencyRole::GIFT_CARD)->where('is_active', true))
            ->with(['currency.roles' => fn ($q) => $q->where('role_name', CurrencyRole::GIFT_CARD)->where('is_active', true)])
            ->get();

        if ($wallets->isEmpty()) {
            $wallets = WalletModel::where('user_id', Auth::id())
                ->whereHas('currency.roles', fn ($q) => $q->where('role_name', CurrencyRole::VOUCHER)->where('is_active', true))
                ->with(['currency.roles' => fn ($q) => $q->where('role_name', CurrencyRole::VOUCHER)->where('is_active', true)])
                ->get();
        }

        return view('frontend.user.gift-cards.create', compact('templates', 'categories', 'wallets'));
    }

    /**
     * Explicit redirect destinations so the user never gets bounced
     * to an unexpected URL via referer-based `back()`:
     *   · success                       → user.gift-card.index
     *   · validation / business failure → user.gift-card.create
     * Both legs flash a notifyevs message that the destination's
     * _notify_evs.blade.php partial renders as a toast.
     *
     * @throws Throwable
     */
    public function store(GiftCardStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $template = GiftCardTemplate::findOrFail($data['gift_card_template_id']);
        $wallet   = WalletModel::with('currency.roles')->findOrFail($data['wallet_id']);

        if ((int) $wallet->user_id !== (int) Auth::id()) {
            notifyEvs('error', __('That wallet does not belong to you.'));

            return redirect()->route('user.gift-card.create')->withInput();
        }

        $amount = (float) $data['amount'];
        $role   = $this->resolveRole($wallet);
        $fee    = Wallet::calculateFeeByRole($wallet, $amount, $role);

        $netAmount     = Wallet::conversionAmount($wallet, $amount);
        $payableAmount = Wallet::conversionAmount($wallet, $amount + $fee);

        if (! Wallet::isWalletBalanceSufficient($wallet->uuid, $payableAmount)) {
            notifyEvs('error', __('Insufficient balance in the selected wallet.'));

            return redirect()->route('user.gift-card.create')->withInput();
        }

        try {
            Wallet::validateAmountLimitByRole($wallet, $amount, $role);
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());

            return redirect()->route('user.gift-card.create')->withInput();
        }

        $recipientUserId = null;
        if (($data['recipient_mode'] ?? 'email') === 'user') {
            $recipientUserId = User::where('email', $data['recipient_email'])->value('id');
        }

        $scheduledAt = (! empty($data['schedule']) && ! empty($data['scheduled_at']))
            ? $data['scheduled_at']
            : null;

        $giftCard = null;

        DB::transaction(function () use (&$giftCard, $wallet, $template, $amount, $fee, $netAmount, $payableAmount, $data, $recipientUserId, $scheduledAt) {
            $transactionData = new TransactionData(
                user_id: Auth::id(),
                trx_type: TrxType::GIFT_CARD,
                amount: $amount,
                amount_flow: AmountFlow::MINUS,
                fee: $fee,
                currency: siteCurrency(),
                net_amount: $netAmount,
                payable_amount: $payableAmount,
                payable_currency: $wallet->currency->code,
                wallet_reference: $wallet->uuid,
                description: __('Gift Card issued from :wallet wallet', ['wallet' => $wallet->name]),
                status: TrxStatus::COMPLETED,
            );

            Transaction::create($transactionData);
            Wallet::subtractMoney($wallet, $payableAmount);

            /*
             * Initial status is "pending" — the email has been queued
             * but the recipient hasn't opened the card yet. The
             * transition to "delivered" happens inside preview() the
             * first time a NON-sender lands on the public preview URL.
             * That keeps the sender's own preview-clicks from
             * flipping their own card to "delivered" (which was the
             * confusing behaviour the user reported).
             */
            $giftCard = GiftCard::create([
                'gift_card_template_id' => $template->id,
                'user_id'               => Auth::id(),
                'currency_id'           => $wallet->currency->id,
                'amount'                => $netAmount,
                'recipient_name'        => $data['recipient_name'],
                'recipient_email'       => $data['recipient_email'],
                'recipient_user_id'     => $recipientUserId,
                'sender_name'           => $data['sender_name'],
                'message'               => $data['message'] ?? null,
                'delivery_method'       => 'email',
                'scheduled_at'          => $scheduledAt,
                'delivered_at'          => null,
                'status'                => $scheduledAt ? 'scheduled' : 'pending',
                'is_active'             => true,
            ]);

            $template->increment('used_count');
        });

        if ($giftCard && $giftCard->status === 'pending') {
            try {
                Mail::to($giftCard->recipient_email)->queue(new GiftCardDelivered($giftCard));
            } catch (Throwable $e) {
                report($e);
            }
        }

        notifyEvs('success', __('Gift card sent successfully.'));

        return redirect()->route('user.gift-card.index');
    }

    /**
     * @throws Throwable
     */
    public function redeem(Request $request, GiftCardHandler $handler): RedirectResponse
    {
        $request->validate([
            'code'      => ['required', 'string'],
            'wallet_id' => ['required', 'integer', 'exists:wallets,id'],
        ]);

        $code = strtoupper(str_replace([' ', '_'], '-', trim((string) $request->input('code'))));

        $giftCard = GiftCard::where('code', $code)->first();

        if (! $giftCard) {
            throw new NotifyErrorException(__('Gift card not found.'));
        }

        if ((int) $giftCard->user_id === (int) Auth::id()) {
            throw new NotifyErrorException(__('You cannot redeem your own gift card.'));
        }

        if (! $giftCard->canBeRedeemed()) {
            throw new NotifyErrorException(__('Gift card is invalid, expired, or already redeemed.'));
        }

        /*
         * Defensive lookup — never throw a raw ModelNotFoundException
         * for wallet ownership mismatches. The default Laravel
         * exception handler renders that as a generic 404 page, which
         * dead-ends the user at /user/gift-cards/redeem. Surface a
         * NotifyErrorException instead so the redeem modal flow
         * returns to the index with a clear flash message.
         */
        $wallet = WalletModel::where('id', $request->input('wallet_id'))
            ->where('user_id', Auth::id())
            ->first();

        if (! $wallet) {
            throw new NotifyErrorException(__('Selected wallet is not available. Please pick a valid wallet.'));
        }

        DB::transaction(function () use ($handler, $giftCard, $wallet) {
            $giftCard->update([
                'is_active'          => false,
                'status'             => 'redeemed',
                'redeemed_by'        => Auth::id(),
                'redeemed_wallet_id' => $wallet->id,
                'redeemed_at'        => now(),
            ]);

            $transactionData = new TransactionData(
                user_id: Auth::id(),
                trx_type: TrxType::GIFT_CARD_REDEEM,
                amount: $giftCard->amount,
                amount_flow: AmountFlow::PLUS,
                fee: 0,
                currency: $wallet->currency->code,
                net_amount: $giftCard->amount,
                payable_amount: $giftCard->amount,
                payable_currency: $wallet->currency->code,
                wallet_reference: $wallet->uuid,
                description: __('Gift Card redeemed (:code)', ['code' => $giftCard->code]),
                status: TrxStatus::COMPLETED,
            );

            $trx                 = Transaction::create($transactionData);
            $trx->gift_card_code = $giftCard->code;

            $handler->handleSuccess($trx);
        });

        notifyEvs('success', __('Gift card redeemed successfully.'));

        return redirect()->route('user.gift-card.index');
    }

    public function preview(string $code): View
    {
        $giftCard = GiftCard::with(['template', 'currency', 'sender'])
            ->where('code', $code)
            ->firstOrFail();

        /*
         * Mark as delivered the first time someone OTHER than the
         * sender opens the public preview. The sender opening their
         * own card (to inspect it) must NOT flip the status — that
         * was the bug the user reported. Guests (no auth) always
         * count as non-sender, so an emailed recipient who clicks
         * the link still triggers the transition.
         */
        $viewerId = Auth::id();
        $senderId = (int) $giftCard->user_id;
        $isSender = $viewerId !== null && (int) $viewerId === $senderId;

        if (! $isSender && $giftCard->status === 'pending') {
            $giftCard->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);
            $giftCard->refresh();
        }

        /*
         * Tell the view who is watching so it can hide the Redeem
         * CTA for the sender (they can't redeem their own card) and
         * show an explanatory note instead.
         */
        $isOwnCard = $isSender;

        return view('frontend.user.gift-cards.preview', compact('giftCard', 'isOwnCard'));
    }

    public function cancel(GiftCard $giftCard): RedirectResponse
    {
        if ((int) $giftCard->user_id !== (int) Auth::id()) {
            throw new NotifyErrorException(__('You cannot cancel this gift card.'));
        }

        if (! in_array($giftCard->status, ['pending', 'scheduled'], true)) {
            throw new NotifyErrorException(__('Only pending or scheduled cards can be cancelled.'));
        }

        $giftCard->update([
            'status'    => 'cancelled',
            'is_active' => false,
        ]);

        notifyEvs('success', __('Gift card cancelled.'));

        return back();
    }

    private function resolveRole(WalletModel $wallet): string
    {
        $hasGiftCardRole = $wallet->currency
            ?->roles
            ?->where('role_name', CurrencyRole::GIFT_CARD)
            ?->where('is_active', true)
            ?->isNotEmpty();

        return $hasGiftCardRole ? CurrencyRole::GIFT_CARD : CurrencyRole::VOUCHER;
    }
}
