<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\P2P;

use App\Enums\P2P\OfferStatus;
use App\Http\Controllers\Backend\BaseController;
use App\Models\P2P\Offer;
use App\Models\P2P\PaymentAccount;
use App\Models\P2P\PaymentMethod;
use App\Traits\FileManageTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class P2PPaymentMethodController extends BaseController
{
    use FileManageTrait;

    // region Payment Method Management

    public static function permissions(): array
    {
        return [
            'index|show|store|update|destroy' => 'p2p-method-manage',
        ];
    }

    public function index(): View
    {
        $methods = PaymentMethod::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(12);

        return view('backend.p2p.payment_methods.manage_payment_methods', compact('methods'));
    }

    public function show(PaymentMethod $method): JsonResponse
    {
        return response()->json([
            'id'           => (int) $method->id,
            'name'         => (string) $method->name,
            'logo_url'     => $method->logo ? asset('storage/'.ltrim((string) $method->logo, '/')) : null,
            'country'      => $method->country,
            'instructions' => $method->instructions,
            'fields'       => $method->normalizedFields(),
            'status'       => (int) $method->status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $countryCodes = collect(getCountries())
            ->pluck('code')
            ->map(fn ($code) => strtoupper((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'country'      => ['nullable', 'string', 'size:2', Rule::in($countryCodes)],
            'instructions' => 'nullable|string|max:2000',
            'status'       => 'nullable|boolean',
        ]);

        $fields = $this->validatedFields($request);

        $data = [
            'name'         => $validated['name'],
            'country'      => isset($validated['country']) ? strtoupper((string) $validated['country']) : null,
            'instructions' => $validated['instructions'] ?? null,
            'fields'       => $fields,
            'sort_order'   => ((int) PaymentMethod::query()->max('sort_order')) + 1,
            'status'       => (bool) ($validated['status'] ?? true),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->uploadImage($request->file('logo'));
        }

        PaymentMethod::create($data);

        return back()->with('notifyevs', ['type' => 'success', 'message' => __('Payment method created')]);
    }

    private function validatedFields(Request $request): array
    {
        $validated = $request->validate([
            'fields'            => 'required|array|min:1',
            'fields.*.label'    => 'required|string|max:100',
            'fields.*.key'      => 'nullable|string|max:100',
            'fields.*.type'     => 'required|in:text,number,textarea,select,file',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.options'  => 'nullable|string|max:2000',
        ]);

        $fields = PaymentMethod::normalizeFieldDefinitions((array) ($validated['fields'] ?? []));
        if ($fields === []) {
            throw ValidationException::withMessages([
                'fields' => __('At least one valid dynamic field is required.'),
            ]);
        }

        return $fields;
    }

    public function update(Request $request, PaymentMethod $method): RedirectResponse
    {
        $countryCodes = collect(getCountries())
            ->pluck('code')
            ->map(fn ($code) => strtoupper((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'country'      => ['nullable', 'string', 'size:2', Rule::in($countryCodes)],
            'instructions' => 'nullable|string|max:2000',
            'status'       => 'nullable|boolean',
        ]);

        $fields = $this->validatedFields($request);

        $data = [
            'name'         => $validated['name'],
            'country'      => isset($validated['country']) ? strtoupper((string) $validated['country']) : null,
            'instructions' => $validated['instructions'] ?? null,
            'fields'       => $fields,
            'status'       => (bool) ($validated['status'] ?? $method->status),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->uploadImage($request->file('logo'), $method->logo);
        }

        $method->update($data);

        return back()->with('notifyevs', ['type' => 'success', 'message' => __('Payment method updated')]);
    }

    public function destroy(PaymentMethod $method): RedirectResponse
    {
        $liveOfferCount = Offer::query()
            ->whereIn('status', [OfferStatus::ACTIVE->value, OfferStatus::PAUSED->value])
            ->whereHas('paymentMethods', function ($q) use ($method) {
                $q->where('p2p_payment_methods.id', $method->id);
            })
            ->count();

        if ($liveOfferCount > 0) {
            return back()->with('notifyevs', [
                'type'    => 'error',
                'message' => __(':count live trade ad(s) still use this payment method. Disable the affected ads first, then try again.', ['count' => $liveOfferCount]),
            ]);
        }

        $accountCount = PaymentAccount::query()
            ->where('payment_method_id', $method->id)
            ->count();

        if ($accountCount > 0) {
            return back()->with('notifyevs', [
                'type'    => 'error',
                'message' => __(':count trader(s) have saved accounts for this method. Ask them to remove their accounts before deleting.', ['count' => $accountCount]),
            ]);
        }

        $method->delete();

        return back()->with('notifyevs', ['type' => 'success', 'message' => __('Payment method deleted')]);
    }
}
