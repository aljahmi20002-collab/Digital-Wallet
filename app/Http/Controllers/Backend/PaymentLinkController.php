<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\PaymentLinkStatus;
use App\Exceptions\NotifyErrorException;
use App\Models\Currency;
use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Services\PaymentLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentLinkController extends BaseController
{
    public function __construct(private readonly PaymentLinkService $paymentLinks) {}

    public static function permissions(): array
    {
        return [
            'index|show'                    => 'payment-link-list',
            'toggleStatus|destroy'          => 'payment-link-manage',
        ];
    }

    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $paymentLinks = $this->paymentLinks->adminListing($filters);
        $metrics      = $this->paymentLinks->adminMetrics();

        $merchants = Merchant::query()
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        $currencies = Currency::query()
            ->where('status', true)
            ->orderBy('code')
            ->get(['id', 'code']);

        return view('backend.payment_link.index', [
            'paymentLinks' => $paymentLinks,
            'metrics'      => $metrics,
            'merchants'    => $merchants,
            'currencies'   => $currencies,
            'statuses'     => PaymentLinkStatus::options(),
            'filters'      => $filters,
        ]);
    }

    public function show(PaymentLink $paymentLink): View
    {
        $paymentLink->load(['user', 'merchant', 'currency']);

        return view('backend.payment_link.show', [
            'paymentLink' => $paymentLink,
        ]);
    }

    public function toggleStatus(PaymentLink $paymentLink): RedirectResponse
    {
        try {
            $this->paymentLinks->adminToggleStatus($paymentLink);
            notifyEvs('success', __('Payment link status updated successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('admin.payment-links.show', $paymentLink);
    }

    public function destroy(PaymentLink $paymentLink): RedirectResponse
    {
        try {
            $this->paymentLinks->adminDelete($paymentLink);
            notifyEvs('success', __('Payment link deleted successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());

            return redirect()->route('admin.payment-links.show', $paymentLink);
        }

        return redirect()->route('admin.payment-links.index');
    }

    /**
     * Pull supported filters from the request as a normalised array. Any
     * unknown values are dropped so the service query stays safe.
     *
     * @return array<string, mixed>
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'status'       => $request->string('status')->toString() ?: null,
            'search'       => $request->string('search')->toString() ?: null,
            'merchant_id'  => $request->integer('merchant_id') ?: null,
            'currency_id'  => $request->integer('currency_id') ?: null,
            'date_from'    => $request->string('date_from')->toString() ?: null,
            'date_to'      => $request->string('date_to')->toString() ?: null,
            'has_payments' => $request->boolean('has_payments'),
        ];
    }
}
