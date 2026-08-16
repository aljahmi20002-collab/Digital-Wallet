<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Enums\VirtualCard\CardholderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCardholderRequest;
use App\Http\Requests\Frontend\UpdateCardholderRequest;
use App\Models\Businesses;
use App\Models\Cardholders;
use App\Traits\FileManageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CardholdersController extends Controller
{
    use FileManageTrait;

    // region Cardholder CRUD
    /**
     * Display a listing of the cardholders.
     */
    public function index()
    {
        $cardholders = Cardholders::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('frontend.user.virtual_card.cardholders.index', compact('cardholders'));
    }

    /**
     * Show the form for creating a new cardholder.
     */
    public function create()
    {
        $cardholderType = CardholderType::cases();
        $businesses     = $this->getUserBusinesses();
        $allCountries   = function_exists('getCountries') ? getCountries() : [];
        $genderOptions  = Gender::options();

        return view('frontend.user.virtual_card.cardholders.create', compact(
            'cardholderType', 'businesses', 'allCountries', 'genderOptions'
        ));
    }

    /**
     * Store a newly created cardholder in storage.
     */
    public function store(StoreCardholderRequest $request)
    {
        $validated            = $request->validated();
        $validated['user_id'] = Auth::id();

        if ($validated['card_type'] === CardholderType::BUSINESS->value) {
            $business                   = $this->createBusinessFromRequest($validated);
            $validated['businesses_id'] = $business->id;
            $validated                  = $this->filterBusinessCardholderData($validated);
        } else {
            $validated = $this->filterPersonalCardholderData($validated);
            $validated = $this->handlePersonalKyc($validated, $request);
        }

        Cardholders::create($validated);
        notifyEvs('success', __('Cardholder created successfully.'));

        return redirect()->route('user.virtual-card.cardholders.index');
    }

    /**
     * Display the specified cardholder.
     */
    public function show(int $id)
    {
        $cardholder = $this->findUserCardholder($id);

        return view('frontend.user.virtual_card.cardholders.show', compact('cardholder'));
    }

    /**
     * Show the form for editing the specified cardholder.
     */
    public function edit(int $id)
    {
        $cardholder     = $this->findUserCardholder($id);
        $businesses     = $this->getUserBusinesses();
        $allCountries   = function_exists('getCountries') ? getCountries() : [];
        $cardholderType = CardholderType::cases();
        $genderOptions  = Gender::options();

        return view('frontend.user.virtual_card.cardholders.edit', compact(
            'cardholder', 'businesses', 'allCountries', 'cardholderType', 'genderOptions'
        ));
    }

    /**
     * Update the specified cardholder in storage.
     */
    public function update(UpdateCardholderRequest $request, int $id)
    {
        $cardholder = $this->findUserCardholder($id);
        $validated  = $request->validated();

        if (! $cardholder->status->isPending()) {
            notifyEvs('warning', __('Not Allowed to update cardholder.'));

            return redirect()->route('user.virtual-card.cardholders.index');
        }

        if ($validated['card_type'] === CardholderType::BUSINESS->value) {
            $business = $cardholder->business;
            if ($business) {
                $payload = $this->mapBusinessPayload($validated);
                // Preserve existing values when the form did not submit them.
                $business->update(array_filter($payload, fn ($v) => $v !== null));
            }
            $validated = $this->filterBusinessCardholderData($validated);
        } else {
            $validated = $this->filterPersonalCardholderData($validated);
            $validated = $this->handlePersonalKyc($validated, $request, $cardholder);
        }

        $cardholder->update($validated);
        notifyEvs('success', __('Cardholder updated successfully.'));

        return redirect()->route('user.virtual-card.cardholders.index');
    }

    /**
     * Remove the specified cardholder from storage.
     */
    public function destroy(int $id)
    {
        $cardholder = $this->findUserCardholder($id);

        if ($cardholder->status->isApproved()) {
            notifyEvs('warning', __('Not Allowed to delete cardholder.'));

            return redirect()->route('user.virtual-card.cardholders.index');
        }

        if ($cardholder->business) {
            $cardholder->business->delete();
        }
        $cardholder->delete();
        notifyEvs('success', __('Cardholder deleted successfully.'));

        return redirect()->route('user.virtual-card.cardholders.index');
    }
    // endregion

    // region Private Helpers
    /**
     * Get all active businesses for the authenticated user.
     */
    private function getUserBusinesses(): Collection
    {
        return Businesses::where('user_id', Auth::id())
            ->where('status', true)
            ->orderBy('business_name')
            ->get();
    }

    /**
     * Find a cardholder belonging to the authenticated user.
     */
    private function findUserCardholder(int $id): Cardholders
    {
        return Cardholders::where('user_id', Auth::id())->findOrFail($id);
    }

    /**
     * Create a business from validated request data.
     */
    private function createBusinessFromRequest(array $validated): Businesses
    {
        return Businesses::create($this->mapBusinessPayload($validated));
    }

    /**
     * Map the (form-suffixed) request payload onto the businesses-table
     * column names. Used by both create and update flows so the suffix
     * convention is owned in one place.
     */
    private function mapBusinessPayload(array $validated): array
    {
        return [
            'user_id'               => Auth::id(),
            'business_name'         => $validated['business_name']         ?? null,
            'trading_name'          => $validated['trading_name']          ?? null,
            'registration_number'   => $validated['registration_number']   ?? null,
            'tin'                   => $validated['tin']                   ?? null,
            'business_type'         => $validated['business_type']         ?? null,
            'incorporation_date'    => $validated['incorporation_date']    ?? null,
            'incorporation_country' => $validated['incorporation_country'] ?? null,
            'industry'              => $validated['industry']              ?? null,
            'mcc_code'              => $validated['mcc_code']              ?? null,
            'website_url'           => $validated['website_url']           ?? null,
            'contact_email'         => $validated['contact_email']         ?? null,
            'contact_phone'         => $validated['contact_phone']         ?? null,
            'phone_country_code'    => $validated['phone_country_code_b']  ?? null,
            'address_line1'         => $validated['address_line1_b']       ?? null,
            'address_line2'         => $validated['address_line2_b']       ?? null,
            'city'                  => $validated['city_b']                ?? null,
            'state'                 => $validated['state_b']               ?? null,
            'postal_code'           => $validated['postal_code_b']         ?? null,
            'country'               => $validated['country_b']             ?? null,
            'beneficial_owners'     => $this->cleanBeneficialOwners($validated['beneficial_owners'] ?? null),
        ];
    }

    /**
     * Drop empty UBO rows so we never persist a row with all-null fields.
     */
    private function cleanBeneficialOwners(?array $owners): ?array
    {
        if (! is_array($owners) || empty($owners)) {
            return null;
        }

        $clean = [];
        foreach ($owners as $owner) {
            if (! is_array($owner)) {
                continue;
            }
            $hasValue = array_filter(array_map(
                static fn ($v) => is_string($v) ? trim($v) : $v,
                $owner
            ), static fn ($v) => $v !== null && $v !== '');
            if (! empty($hasValue)) {
                $clean[] = [
                    'name'          => $owner['name'] ?? null,
                    'dob'           => $owner['dob']  ?? null,
                    'ownership_pct' => isset($owner['ownership_pct']) ? (float) $owner['ownership_pct'] : null,
                    'country'       => $owner['country']   ?? null,
                    'id_type'       => $owner['id_type']   ?? null,
                    'id_number'     => $owner['id_number'] ?? null,
                ];
            }
        }

        return $clean ?: null;
    }

    /**
     * Handle the single ID-document upload for personal cardholders.
     *
     * Replaces the legacy `kyc_template_id` + dynamic-credentials flow.
     * The Government-ID section on the form posts a single `id_document`
     * file; we persist it under `kyc_documents['id_document']` so the
     * existing `kyc_documents` JSON column doubles as the document store.
     */
    private function handlePersonalKyc(array $validated, Request $request, ?Cardholders $existingCardholder = null): array
    {
        // Already-saved documents (preserve everything we don't overwrite).
        $documents = $existingCardholder?->kyc_documents ?? [];

        if ($request->hasFile('id_document')) {
            $documents['id_document'] = $this->uploadFile($request->file('id_document'));
        }

        // First-time creation gets a "pending" KYC status; updates leave
        // the existing status alone so admins don't have to re-review on
        // every cosmetic edit.
        if ($existingCardholder === null) {
            $validated['kyc_status'] = KycStatus::PENDING->value;
        }

        $validated['kyc_documents'] = $documents ?: null;

        // Stash the legacy template id only if it was actually posted
        // — keeps backward compatibility with admin reviewers who still
        // filter by it without forcing the field on the user.
        if ($request->filled('kyc_template_id')) {
            $validated['kyc_type'] = $request->input('kyc_template_id');
        } elseif ($existingCardholder) {
            $validated['kyc_type'] = $existingCardholder->kyc_type;
        }

        return $validated;
    }

    /**
     * Only keep fields relevant for personal cardholder.
     */
    private function filterPersonalCardholderData(array $validated): array
    {
        return collect($validated)->only([
            'user_id',
            // Identity
            'title', 'first_name', 'middle_name', 'last_name',
            'gender', 'dob', 'nationality', 'place_of_birth', 'relation',
            // Contact
            'email', 'mobile', 'phone_country_code',
            // Address
            'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
            // Government ID
            'id_type', 'id_number', 'id_issue_country', 'id_issue_date', 'id_expiry',
            // Tax
            'tax_id', 'tax_country',
            // Employment / AML
            'occupation', 'employer', 'annual_income', 'source_of_funds',
            // Compliance flags
            'pep_flag', 'sanctions_flag',
            // System
            'card_type', 'kyc_status', 'kyc_type', 'address_proof_type', 'kyc_documents', 'status',
        ])->toArray();
    }

    /**
     * Only keep fields relevant for business cardholder.
     */
    private function filterBusinessCardholderData(array $validated): array
    {
        return collect($validated)->only([
            'user_id', 'card_type', 'businesses_id', 'status',
        ])->toArray();
    }
    // endregion
}
