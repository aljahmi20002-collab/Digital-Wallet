<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\VirtualCard\CardholderStatus;
use App\Models\Cardholders;
use App\Models\VirtualCardProvider;
use App\Services\VirtualCard\Drivers\Bitnob\BitnobCardProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class CardholdersController extends BaseController
{
    public static function permissions(): array
    {
        return [
            '*' => 'virtual-card-action',
        ];
    }

    /**
     * Show all cardholders (personal & business).
     */
    public function index(Request $request)
    {
        $cardholders = Cardholders::with(['user', 'business'])
            ->status($request->input('status'))
            ->search($request->input('search'))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $statuses = CardholderStatus::options();
        // Active providers — used to badge each row with the providers
        // that can actually issue for that cardholder's country.
        $providers = VirtualCardProvider::active()->orderBy('order')->get();

        return view('backend.virtual_card.cardholder.index', compact('cardholders', 'statuses', 'providers'));
    }

    /**
     * Handle approve or reject cardholder action (personal & business).
     */
    public function action(Request $request, $id)
    {
        $cardholder = Cardholders::findOrFail($id);
        if ($cardholder->status !== CardholderStatus::PENDING) {
            return Redirect::back()->with('error', __('Already processed.'));
        }
        $action = $request->input('action');
        if ($action === 'approve') {
            DB::transaction(function () use ($cardholder) {
                $cardholder->status = CardholderStatus::APPROVED;
                $cardholder->save();
                Log::info('Cardholder approved', ['id' => $cardholder->id]);
            });
            notifyEvs('success', __('Cardholder approved.'));

            return Redirect::back()->with('success', __('Cardholder approved.'));
        } elseif ($action === 'reject') {
            DB::transaction(function () use ($cardholder) {
                $cardholder->status = CardholderStatus::REJECTED;
                $cardholder->save();
                Log::info('Cardholder rejected', ['id' => $cardholder->id]);
            });
            notifyEvs('success', __('Cardholder rejected.'));

            return Redirect::back()->with('success', __('Cardholder rejected.'));
        }
        notifyEvs('error', __('Invalid action.'));

        return Redirect::back()->with('error', __('Invalid action.'));
    }

    /**
     * Pre-index a cardholder with the Bitnob Visa BIN pool.
     *
     * Bitnob's `/virtualcards/registercarduser` endpoint registers the
     * customer AND indexes them in the appropriate card-brand pool when
     * `cardBrand: Visa` is included in the payload. Calling it here
     * before the user attempts issuance avoids the "User with this email
     * is not indexed for visa" failure mode that otherwise comes back as
     * a `createdStatus: failed` card. Idempotent — re-running on an
     * already-indexed user simply returns the existing record.
     */
    public function bitnobVerify(int $id)
    {
        $cardholder = Cardholders::with(['user', 'kycTemplate'])->findOrFail($id);

        try {
            $provider = app(BitnobCardProvider::class);
            $response = $provider->verifyCardholder($cardholder);

            $ok  = (bool) ($response['status'] ?? false);
            $msg = (string) ($response['message'] ?? ($ok ? 'Cardholder verified with Bitnob.' : 'Bitnob did not return a success status.'));

            // The customer id Bitnob returns is what every later card
            // call will refer to — stash it on kyc_documents so it shows
            // in the admin view and survives re-seeding.
            $customerId = $response['data']['customerId'] ?? ($response['data']['id'] ?? null);
            if ($customerId) {
                $docs                       = is_array($cardholder->kyc_documents) ? $cardholder->kyc_documents : [];
                $docs['bitnob_customer_id'] = $customerId;
                $docs['bitnob_verified_at'] = now()->toIso8601String();
                $cardholder->update(['kyc_documents' => $docs]);
            }

            Log::info('Bitnob manual cardholder verify', [
                'cardholderId' => $cardholder->id,
                'customerId'   => $customerId,
                'ok'           => $ok,
            ]);

            notifyEvs($ok ? 'success' : 'warning', $msg);
        } catch (\Throwable $e) {
            Log::error('Bitnob manual cardholder verify failed', [
                'cardholderId' => $cardholder->id,
                'error'        => $e->getMessage(),
            ]);
            notifyEvs('error', __('Bitnob verify failed: :err', ['err' => $e->getMessage()]));
        }

        return Redirect::back();
    }
}
