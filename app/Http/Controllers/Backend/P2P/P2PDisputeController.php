<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\P2P;

use App\Enums\P2P\DisputeStatus;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Backend\BaseController;
use App\Models\P2P\Dispute;
use App\Models\P2P\Order;
use App\Services\P2P\P2POrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class P2PDisputeController extends BaseController
{
    // region Trade Dispute Review and Resolution

    public function __construct(private readonly P2POrderService $orders) {}

    public static function permissions(): array
    {
        return [
            'index|history|show|resolveRelease|resolveRefund' => 'p2p-dispute-manage',
        ];
    }

    public function index(): View
    {
        $disputes = Dispute::with(['order.maker', 'order.taker', 'raiser'])
            ->where('status', DisputeStatus::OPEN->value)
            ->latest()->paginate(20);

        return view('backend.p2p.disputes.open_trade_disputes', compact('disputes'));
    }

    public function history(): View
    {
        $disputes = Dispute::with(['order.maker', 'order.taker', 'raiser'])
            ->whereIn('status', [DisputeStatus::RESOLVED->value, DisputeStatus::REJECTED->value])
            ->latest()->paginate(20);

        return view('backend.p2p.disputes.trade_dispute_history', compact('disputes'));
    }

    public function show(Dispute $dispute): View
    {
        $dispute->load(['order.offer', 'order.wallet.currency', 'order.maker', 'order.taker', 'raiser']);

        return view('backend.p2p.disputes.trade_dispute_details', compact('dispute'));
    }

    public function resolveRelease(Request $request, Dispute $dispute): RedirectResponse
    {
        return $this->resolveWith(
            $request,
            $dispute,
            fn (Order $order) => $this->orders->adminResolveRelease($order),
            __('Escrow released to buyer by admin'),
            __('Trade dispute resolved by releasing escrow to the buyer')
        );
    }

    public function resolveRefund(Request $request, Dispute $dispute): RedirectResponse
    {
        return $this->resolveWith(
            $request,
            $dispute,
            fn (Order $order) => $this->orders->adminResolveRefund($order),
            __('Escrow refunded to maker by admin'),
            __('Trade dispute resolved by refunding escrow to the maker')
        );
    }

    /**
     * Shared resolve pipeline: validates admin note, guards against
     * non-OPEN disputes (idempotency), wraps service call + status
     * transition in a single transaction, and composes a final
     * resolution string from the action + optional admin note.
     */
    private function resolveWith(
        Request $request,
        Dispute $dispute,
        \Closure $action,
        string $defaultResolution,
        string $successMessage
    ): RedirectResponse {
        $validated = $request->validate([
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        if ($dispute->status !== DisputeStatus::OPEN) {
            return back()->with('notifyevs', [
                'type'    => 'info',
                'message' => __('This trade dispute is already resolved.'),
            ]);
        }

        try {
            DB::transaction(function () use ($dispute, $action, $defaultResolution, $validated) {
                $locked = Dispute::query()->whereKey($dispute->id)->lockForUpdate()->first();

                if (! $locked || $locked->status !== DisputeStatus::OPEN) {
                    throw new NotifyErrorException(__('This trade dispute is already resolved.'));
                }

                $order = Order::query()
                    ->with(['offer', 'wallet.currency'])
                    ->lockForUpdate()
                    ->findOrFail($locked->order_id);

                $action($order);

                $note       = trim((string) ($validated['resolution_note'] ?? ''));
                $resolution = $note !== ''
                    ? $defaultResolution.' — '.$note
                    : $defaultResolution;

                $locked->update([
                    'status'     => DisputeStatus::RESOLVED->value,
                    'resolution' => $resolution,
                ]);
            });
        } catch (NotifyErrorException $e) {
            return back()->with('notifyevs', ['type' => 'error', 'message' => $e->getMessage()]);
        }

        return back()->with('notifyevs', ['type' => 'success', 'message' => $successMessage]);
    }

    // endregion
}
