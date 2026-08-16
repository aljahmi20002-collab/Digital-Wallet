<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\VirtualCard\VirtualCardNetwork;
use App\Enums\VirtualCard\VirtualCardRequestStatus;
use App\Enums\VirtualCard\VirtualCardStatus;
use App\Exceptions\NotifyErrorException;
use App\Models\PaymentGateway;
use App\Models\VirtualCard;
use App\Models\VirtualCardProvider;
use App\Models\VirtualCardRequest;
use App\Notifications\TemplateNotification;
use App\Services\VirtualCard\Drivers\Bitnob\BitnobCardProvider;
use App\Services\VirtualCard\VirtualCardManager;
use App\Services\VirtualCard\VirtualCardProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Transaction;
use Wallet;

class VirtualCardController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'virtualCardList|requestAwaiting|requestAll'                                 => 'virtual-card-list',
            'review|statusUpdate|refreshCard'                                            => 'virtual-card-action',
            'provider|providerManage|providerUpdate|providerShow|providerTestConnection' => 'virtual-card-provider-manage',
        ];
    }

    /**
     * List all virtual cards with admin filters.
     */
    public function virtualCardList(Request $request)
    {
        $cards = VirtualCard::with(['user', 'wallet.currency', 'provider'])
            ->filter($request)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
        $providers = VirtualCardProvider::all();
        $statuses  = VirtualCardStatus::options();

        return view('backend.virtual_card.list', compact('cards', 'providers', 'statuses'));
    }

    /**
     * List all pending virtual card requests with filters.
     */
    public function requestAwaiting(Request $request)
    {
        $requests = VirtualCardRequest::where('status', VirtualCardRequestStatus::Pending)
            ->with('wallet.currency', 'user', 'cardholder.business')
            ->filter($request)
            ->latest('id')
            ->paginate(10);
        $providers = VirtualCardProvider::all();

        return view('backend.virtual_card.awaiting', compact('requests', 'providers'));
    }

    /**
     * List all virtual card requests with filters.
     */
    public function requestAll(Request $request)
    {
        $requests = VirtualCardRequest::with(['wallet.currency', 'user', 'card', 'cardholder.business'])
            ->filter($request)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
        $providers = VirtualCardProvider::all();
        $statuses  = VirtualCardRequestStatus::options();

        return view('backend.virtual_card.all', compact('requests', 'providers', 'statuses'));
    }

    /**
     * List all virtual card providers (paginated) with per-provider
     * runtime stats so the admin can see issuance volume + health
     * at a glance without drilling into each provider.
     */
    public function provider()
    {
        $providers = VirtualCardProvider::with('paymentGateway')
            ->orderBy('order')
            ->orderBy('id')
            ->paginate(10);

        // Aggregate per-provider card counts in one query — far cheaper
        // than N+1ing per row and works the same regardless of how many
        // providers the script has wired in.
        $cardStats = VirtualCard::query()
            ->selectRaw('provider_id, status, COUNT(*) as total')
            ->groupBy('provider_id', 'status')
            ->get()
            ->groupBy('provider_id');

        $pendingRequests = VirtualCardRequest::query()
            ->where('status', VirtualCardRequestStatus::Pending)
            ->selectRaw('provider_id, COUNT(*) as total')
            ->groupBy('provider_id')
            ->pluck('total', 'provider_id');

        // Decorate each provider on the page with the stats it cares about.
        $providers->getCollection()->transform(function ($provider) use ($cardStats, $pendingRequests) {
            $rows                            = $cardStats->get($provider->id, collect());
            $provider->stat_total            = (int) $rows->sum('total');
            $provider->stat_active           = (int) ($rows->firstWhere('status', VirtualCardStatus::Active->value)->total ?? 0);
            $provider->stat_pending          = (int) ($rows->firstWhere('status', VirtualCardStatus::Pending->value)->total ?? 0);
            $provider->stat_failed           = (int) ($rows->firstWhere('status', VirtualCardStatus::Failed->value)->total ?? 0);
            $provider->stat_inactive         = (int) ($rows->firstWhere('status', VirtualCardStatus::Inactive->value)->total ?? 0);
            $provider->stat_pending_requests = (int) ($pendingRequests[$provider->id] ?? 0);

            return $provider;
        });

        return view('backend.virtual_card.provider', compact('providers'));
    }

    /**
     * Per-provider drill-down — shows capability matrix, recent cards
     * issued through this provider, recent failures, and the provider's
     * own diagnostic information. Works for any registered provider
     * without per-provider conditionals.
     */
    public function providerShow(int $id)
    {
        $provider = VirtualCardProvider::with('paymentGateway')->findOrFail($id);

        $recentCards = VirtualCard::query()
            ->with(['user', 'wallet.currency', 'request.cardholder'])
            ->where('provider_id', $id)
            ->latest('id')
            ->limit(20)
            ->get();

        $recentRequests = VirtualCardRequest::query()
            ->with(['user', 'wallet.currency', 'cardholder'])
            ->where('provider_id', $id)
            ->latest('id')
            ->limit(10)
            ->get();

        $statBuckets = VirtualCard::query()
            ->where('provider_id', $id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('backend.virtual_card.provider_show', compact(
            'provider',
            'recentCards',
            'recentRequests',
            'statBuckets'
        ));
    }

    /**
     * Run the provider's connection test and return a JSON envelope so
     * the admin UI can show success/failure inline without a full page
     * reload. Provider-agnostic — every concrete provider implements
     * the same `testConnection()` shape on AbstractVirtualCardProvider.
     */
    public function providerTestConnection(int $id, VirtualCardProviderFactory $factory)
    {
        $provider = VirtualCardProvider::findOrFail($id);

        try {
            $impl   = $factory->getProvider($provider->code);
            $result = $impl->testConnection();
        } catch (\Throwable $e) {
            $result = [
                'ok'         => false,
                'mode'       => null,
                'message'    => $e->getMessage(),
                'latency_ms' => null,
                'details'    => ['exception' => get_class($e)],
            ];
        }

        return response()->json([
            'provider' => [
                'id'   => $provider->id,
                'code' => $provider->code,
                'name' => $provider->name,
            ],
            'result' => $result,
        ]);
    }

    /**
     * Re-fetch a card's live details from the gateway and persist any
     * changed last4/expiry/status onto the local row. Useful for
     * recovering from async-issuance providers (Bitnob) that may
     * settle a previously-failed card later.
     */
    public function refreshCard(int $card, VirtualCardProviderFactory $factory)
    {
        $card = VirtualCard::with('provider')->findOrFail($card);
        if (! $card->provider) {
            notifyEvs('error', __('This card has no provider attached.'));

            return back();
        }

        try {
            $impl    = $factory->getProvider($card->provider->code);
            $details = $impl->getCardDetails($card);

            $update = [];
            if (! empty($details['card_number'])) {
                $update['last4'] = substr(preg_replace('/\D+/', '', (string) $details['card_number']), -4);
            }
            if (! empty($details['expiry']) && preg_match('#^(\d{2})/(\d{2,4})$#', $details['expiry'], $m)) {
                $update['expiry_month'] = $m[1];
                $update['expiry_year']  = strlen($m[2]) === 2 ? '20'.$m[2] : $m[2];
            }
            $newStatus = strtolower((string) ($details['card_status'] ?? ''));
            if (in_array($newStatus, ['active', 'pending', 'inactive', 'blocked', 'expired', 'failed'], true)) {
                $update['status'] = $newStatus;
            }

            if (! empty($update)) {
                $card->update($update);
            }

            notifyEvs('success', __('Card refreshed from :provider. :count fields updated.', [
                'provider' => $card->provider->name,
                'count'    => count($update),
            ]));
        } catch (\Throwable $e) {
            Log::error('Admin card refresh failed', [
                'card_id' => $card->id,
                'error'   => $e->getMessage(),
            ]);
            notifyEvs('error', __('Refresh failed: :err', ['err' => $e->getMessage()]));
        }

        return back();
    }

    /**
     * Show provider management form.
     */
    public function providerManage(int $id)
    {
        $provider = VirtualCardProvider::findOrFail($id);
        $gateways = PaymentGateway::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'status']);
        $networkOptions   = VirtualCardNetwork::cases();
        $capabilityLabels = $this->providerCapabilityLabels();

        return view('backend.virtual_card.partials.provider_manage', compact(
            'provider',
            'gateways',
            'networkOptions',
            'capabilityLabels'
        ));
    }

    /**
     * Update a virtual card provider.
     */
    public function providerUpdate(Request $request, VirtualCardProvider $provider)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:50',
            'brand'                => 'nullable|string|max:30',
            'display_label'        => 'nullable|string|max:24',
            'brand_color'          => ['nullable', 'string', 'max:16', 'regex:/^#?([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'payment_gateway_id'   => ['nullable', 'integer', 'exists:payment_gateways,id'],
            'supported_networks'   => ['nullable', 'array'],
            'supported_networks.*' => ['string', Rule::in(array_map(fn (VirtualCardNetwork $case) => $case->value, VirtualCardNetwork::cases()))],
            'supported_currencies' => ['nullable', 'string', 'max:1000', 'regex:/^[A-Za-z0-9, ]*$/'],
            'supported_countries'  => ['nullable', 'string', 'max:1000', 'regex:/^[A-Za-z, ]*$/'],
            'issue_fee'            => 'required|numeric|min:0',
            'issue_fee_pct'        => 'nullable|numeric|min:0|max:100',
            'min_balance'          => 'nullable|numeric|min:0',
            'order'                => 'nullable|integer|min:0',
            'capabilities'         => ['nullable', 'array'],
            'capabilities.*'       => ['string', Rule::in(array_keys($this->providerCapabilityLabels()))],
            'status'               => 'boolean',
        ]);

        $validated['status']               = $request->boolean('status') ? 1 : 0;
        $validated['brand_color']          = $this->normalizeHexColor($validated['brand_color'] ?? null);
        $validated['supported_networks']   = $this->normalizeArrayList($validated['supported_networks'] ?? [], lowercase: true);
        $validated['supported_currencies'] = $this->normalizeCsvList($validated['supported_currencies'] ?? null, uppercase: true);
        $validated['supported_countries']  = $this->normalizeCountryList($validated['supported_countries'] ?? null);
        $validated['capabilities']         = $this->normalizeCapabilities($request->input('capabilities', []));

        $provider->update($validated);
        notifyEvs('success', __('Provider Updated Successfully'));

        return redirect()->route('admin.virtual-card.provider.index');
    }

    /**
     * Parse comma-separated ISO-2 country list into a clean array, or
     * NULL when the field is blank ("no restriction").
     */
    private function normalizeCountryList(?string $raw): ?array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $codes = array_filter(array_map(
            fn ($c) => strtoupper(trim($c)),
            explode(',', $raw)
        ), fn ($c) => preg_match('/^[A-Z]{2,3}$/', $c) === 1);

        return $codes ? array_values(array_unique($codes)) : null;
    }

    private function normalizeCsvList(?string $raw, bool $uppercase = false): ?array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $values = array_filter(array_map(static function (string $value) use ($uppercase): string {
            $value = trim($value);

            return $uppercase ? strtoupper($value) : strtolower($value);
        }, explode(',', $raw)));

        return $values ? array_values(array_unique($values)) : null;
    }

    private function normalizeArrayList(array $values, bool $lowercase = false): ?array
    {
        $normalized = array_filter(array_map(static function (mixed $value) use ($lowercase): string {
            $value = trim((string) $value);

            return $lowercase ? strtolower($value) : $value;
        }, $values));

        return $normalized ? array_values(array_unique($normalized)) : null;
    }

    private function normalizeCapabilities(array $selected): array
    {
        $selected = array_flip(array_map('strval', $selected));

        return collect($this->providerCapabilityLabels())
            ->mapWithKeys(fn ($label, $key) => [$key => array_key_exists($key, $selected)])
            ->all();
    }

    private function providerCapabilityLabels(): array
    {
        return [
            'issue'        => __('Issue Card'),
            'card_details' => __('Reveal PAN/CVV'),
            'topup'        => __('Top-up'),
            'withdraw'     => __('Withdraw'),
            'freeze'       => __('Freeze / Unfreeze'),
            'limits'       => __('Spend Limits'),
            'controls'     => __('Card Controls'),
        ];
    }

    /**
     * Activate / inactivate (freeze) an issued card from the admin panel.
     *
     * Calls the provider's freeze/unfreeze API so the change actually
     * takes effect at the gateway. Falls back to a soft DB-only flip
     * when the provider lacks a status API (the provider class itself
     * decides this and signals via `soft => true`).
     */
    public function statusUpdate(Request $request, VirtualCardProviderFactory $factory)
    {
        $data = $request->validate([
            'card_id' => ['required', 'integer', 'exists:virtual_cards,id'],
            'status'  => ['required', 'in:active,inactive'],
        ]);

        $card = VirtualCard::with('provider')->findOrFail($data['card_id']);

        try {
            $provider = $card->provider ? $factory->getProvider($card->provider->code) : null;

            if ($provider) {
                if ($data['status'] === 'inactive') {
                    $provider->freezeCard($card);
                } else {
                    $provider->unfreezeCard($card);
                }
            }

            $card->update([
                'status' => $data['status'] === 'active'
                    ? VirtualCardStatus::Active->value
                    : VirtualCardStatus::Inactive->value,
            ]);

            notifyEvs('success', __('Card status updated to :status.', ['status' => $data['status']]));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin card status update failed', [
                'card_id' => $card->id,
                'target'  => $data['status'],
                'error'   => $e->getMessage(),
            ]);
            notifyEvs('error', __('Failed to update card status: :msg', ['msg' => $e->getMessage()]));
        }

        return back();
    }

    /**
     * Force a leading "#" on user-supplied hex colors so the rendered CSS does
     * not silently break when an admin types "3B6FE0" instead of "#3B6FE0".
     */
    private function normalizeHexColor(?string $hex): ?string
    {
        $value = trim((string) $hex);

        if ($value === '') {
            return null;
        }

        return $value[0] === '#' ? $value : '#'.$value;
    }

    /**
     * Review and process a virtual card request (approve/reject).
     */
    public function review(Request $request, string $uuid)
    {
        $cardRequest = VirtualCardRequest::where('uuid', $uuid)
            ->with(['wallet.currency', 'user', 'cardholder.business'])
            ->firstOrFail();

        $validated = $request->validate([
            'admin_note'  => ['nullable', 'string', 'max:255'],
            'action'      => ['required', 'in:approve,reject'],
            'provider_id' => ['nullable', 'integer', 'exists:virtual_card_providers,id'],
        ]);

        if ($validated['action'] === 'approve') {
            if (empty($validated['provider_id'])) {
                notifyEvs('error', __('Please select a compatible provider before approving.'));

                return back();
            }

            DB::beginTransaction();
            try {
                $manager       = app(VirtualCardManager::class);
                $provider      = VirtualCardProvider::findOrFail($validated['provider_id']);
                $user          = $cardRequest->user;
                $defaultWallet = $user->default_wallet;

                $this->ensureProviderCanIssue($provider, $cardRequest);

                // Use initial load amount from request (if provided) for % surcharge base
                $baseAmount = (float) ($cardRequest->initial_load_amount ?? 0.0);
                // Total issue fee = fixed + % of base
                $issueFee = $provider->issueTotalFee($baseAmount);

                if (! $defaultWallet || $defaultWallet->balance < $issueFee) {
                    DB::rollBack();
                    notifyEvs('warning', 'Insufficient balance in your default wallet for card issuing fee.');

                    return back();
                }

                $trxData = new TransactionData(
                    user_id: $user->id,
                    trx_type: TrxType::SUBTRACT_BALANCE,
                    amount: $issueFee,
                    amount_flow: AmountFlow::MINUS,
                    currency: $defaultWallet->currency->code,
                    net_amount: $issueFee,
                    payable_amount: $issueFee,
                    payable_currency: $defaultWallet->currency->code,
                    wallet_reference: $defaultWallet->uuid,
                    description: __('Virtual Card Issuing Fee'),
                    status: TrxStatus::COMPLETED
                );
                $trx = Transaction::create($trxData);
                Wallet::subtractMoney($defaultWallet, $issueFee);

                // Pass the same base amount to provider (if any) to avoid mismatch
                if ($baseAmount > 0) {
                    $cardRequest->initial_load_amount = $baseAmount; // dynamic attribute for providers expecting $request->amount
                }

                // Bitnob requires the cardholder to be pre-indexed in the
                // Visa BIN pool before card creation will succeed. The
                // first call is harmless on already-indexed users (Bitnob
                // returns "already registered") and triggers indexing on
                // fresh users. Done here so admins don't have to click
                // "Verify with Bitnob" first when approving a request.
                if ($provider->code === 'bitnob') {
                    try {
                        $cardholder = $cardRequest->cardholder;
                        if ($cardholder) {
                            app(BitnobCardProvider::class)
                                ->verifyCardholder($cardholder);
                        }
                    } catch (\Throwable $verifyEx) {
                        // Not fatal — issueCard will re-attempt registration
                        // and surface a clearer error if it really blocks.
                        Log::warning('Bitnob pre-issuance verify failed; continuing to issueCard', [
                            'error' => $verifyEx->getMessage(),
                        ]);
                    }
                }

                $card = $manager->issueProviderCard($cardRequest, $provider->code);

                // The manager has already set the request status (Failed
                // when the provider returned status=`failed`, Issued
                // otherwise). Don't overwrite it here — that would erase
                // a failed-issuance signal that the dashboard relies on.
                $cardStatusValue = $card->status instanceof VirtualCardStatus
                    ? $card->status->value
                    : (string) $card->status;
                $cardIssueFailed = $cardStatusValue === VirtualCardStatus::Failed->value;
                $cardRequest->update(array_filter([
                    'admin_note'  => $request->admin_note,
                    'provider_id' => $provider->id,
                    'status'      => $cardIssueFailed
                        ? VirtualCardRequestStatus::Failed
                        : VirtualCardRequestStatus::Issued,
                ], fn ($v) => $v !== null));

                $wallet      = $cardRequest->wallet;
                $walletName  = $wallet->name ?? $wallet->currency->code;
                $fee         = $issueFee;
                $cardNetwork = $card->brand ?? $cardRequest->network;
                $last4       = isset($card->number) ? substr($card->number, -4) : ($cardRequest->last4 ?? '');

                // Skip the success notification when issuance actually
                // failed — sending "card approved" for a failed card
                // confuses users and floods the inbox.
                if (! $cardIssueFailed) {
                    $cardRequest->user->notify(new TemplateNotification(
                        identifier: 'virtual_card_user_approved',
                        data: [
                            'card_network' => $cardNetwork,
                            'last4'        => $last4,
                            'wallet'       => $walletName,
                            'fee'          => $fee,
                        ],
                        action: route('user.virtual-card.index')
                    ));
                }

                DB::commit();
                if ($cardIssueFailed) {
                    $reason = is_array($card->meta)
                        ? ($card->meta['failure_reason'] ?? __('Provider declined the card creation.'))
                        : __('Provider declined the card creation.');
                    notifyEvs('warning', __('Card saved as failed. :reason', ['reason' => $reason]));
                } else {
                    notifyEvs('success', 'Virtual card has been issued successfully!');
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                logger()->error('Card issue failed: '.$e->getMessage(), [
                    'virtual_card_request_id' => $cardRequest->id,
                    'cardholder_id'           => $cardRequest->cardholder_id,
                    'user_id'                 => $cardRequest->user_id,
                    'wallet_id'               => $cardRequest->wallet_id,
                    'provider_id'             => $provider->id                 ?? $cardRequest->provider_id,
                    'provider_code'           => $provider->code               ?? null,
                    'network'                 => $cardRequest->network?->value ?? $cardRequest->network,
                    'initial_load_amount'     => $cardRequest->initial_load_amount,
                    'exception'               => $e,
                ]);
                notifyEvs('error', 'Card issuing failed: '.$e->getMessage());

                return back();
            }
        }

        if ($validated['action'] === 'reject') {
            $cardRequest->update([
                'status'     => VirtualCardRequestStatus::Rejected,
                'admin_note' => $request->admin_note,
            ]);
            notifyEvs('warning', 'Request rejected successfully.');
        }

        return redirect()->route('admin.virtual-card.requests.awaiting');
    }

    private function ensureProviderCanIssue(VirtualCardProvider $provider, VirtualCardRequest $cardRequest): void
    {
        if (! $provider->status) {
            throw new NotifyErrorException(__('Selected provider is inactive.'));
        }

        if (! $provider->supports('issue')) {
            throw new NotifyErrorException(__('Selected provider does not support card issuance.'));
        }

        $network = strtolower((string) ($cardRequest->network?->value ?? $cardRequest->network ?? ''));
        if ($provider->code === 'bitnob' && $network !== VirtualCardNetwork::Visa->value) {
            throw new NotifyErrorException(__('Bitnob currently supports only Visa virtual cards. Select another provider for :network requests.', [
                'network' => strtoupper($network ?: __('unknown')),
            ]));
        }

        if (! $provider->supportsNetwork($network)) {
            throw new NotifyErrorException(__('Selected provider does not support the requested card network.'));
        }

        $currency = strtoupper((string) ($cardRequest->wallet?->currency?->code ?? ''));
        if (! $provider->supportsCurrency($currency)) {
            throw new NotifyErrorException(__('Selected provider does not support the request wallet currency.'));
        }

        $country = $this->cardholderCountry($cardRequest);
        if (! $provider->supportsCountry($country)) {
            throw new NotifyErrorException(__('Selected provider does not support this cardholder country.'));
        }
    }

    private function cardholderCountry(VirtualCardRequest $cardRequest): ?string
    {
        $cardholder = $cardRequest->cardholder;

        return $cardholder?->card_type?->isBusiness() && $cardholder?->business?->country
            ? $cardholder->business->country
            : ($cardholder?->country ?? null);
    }
}
