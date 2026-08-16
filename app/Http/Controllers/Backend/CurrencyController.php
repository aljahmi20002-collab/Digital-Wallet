<?php

namespace App\Http\Controllers\Backend;

use App\Constants\CurrencyRole;
use App\Constants\CurrencyType;
use App\Models\Currency;
use App\Services\CurrencyConversionService;
use App\Traits\FileManageTrait;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;

class CurrencyController extends BaseController
{
    use FileManageTrait;

    public static function permissions(): array
    {
        return [
            'index|store|edit|update|destroy|rates' => 'currency-manage',
        ];
    }

    // Display a listing of currencies
    public function index()
    {
        $currencies = Currency::with(['activeRoles'])->get();

        return view('backend.currencies.index', compact('currencies'));
    }

    // Store a newly created currency in storage
    public function store(Request $request)
    {
        // Validate and prepare currency data
        $validated                = $this->validateCurrencyData($request);
        $validated['auto_wallet'] = $this->toBoolean($request->default ? true : $request->auto_wallet);
        $validated['status']      = $this->toBoolean($request->status);
        $validated['default']     = $this->toBoolean($request->default);

        // Ensure only one default currency exists
        if ($validated['default']) {
            Currency::where('default', true)->update(['default' => false]);
        } else {
            $this->enforceDefaultCurrency();
        }

        // Process and assign the flag image. When the admin skips the
        // file picker (most common silent-failure path), fall back to a
        // generic placeholder so the create still succeeds and they can
        // upload a real flag from the edit screen later.
        $validated['flag'] = $this->handleFlagUpload($request)
            ?? 'general/static/default/placeholder.png';

        // Create currency record and notify user
        $currency = Currency::create($validated);

        // Get roles data from the request
        $rolesData = $request->input('roles', CurrencyRole::getRoles());

        // Loop through the roles and update them
        foreach ($rolesData as $role) {
            // Find the role by its ID, and if it exists, update it
            $currency->roles()->create([
                'role_name' => $role['role_name'],
                'fee'       => $role['fee']       ?? 0,
                'fee_type'  => $role['fee_type']  ?? null,
                'min_limit' => $role['min_limit'] ?? 0,
                'max_limit' => $role['max_limit'] ?? null,
                'is_active' => $role['status']    ?? false,
            ]);
        }

        notifyEvs('success', __('Currency created successfully'));

        return redirect()->back();
    }

    // Remove the specified currency from storage

    public function update(Request $request, Currency $currency)
    {

        // Validate and prepare currency data
        $validated                = $this->validateCurrencyData($request, true);
        $validated['auto_wallet'] = $this->toBoolean($request->default ? true : $request->auto_wallet);
        $validated['status']      = $this->toBoolean($request->status);
        $validated['default']     = $this->toBoolean($request->default);

        // Prevent disabling the default currency
        if (($currency->default || $validated['default']) && ! $validated['status']) {
            return redirect()->back()->withErrors(__('The default currency cannot be disabled.'));
        }

        $isChangingDefaultCurrency = $validated['default'] && ! $currency->default;

        if ($isChangingDefaultCurrency && ! $request->boolean('default_change_acknowledged')) {
            return redirect()->back()
                ->withErrors(['default_change_acknowledged' => __('Please review and acknowledge the default currency change impact before continuing.')])
                ->withInput();
        }

        // Prevent changing the default currency
        if ($currency->default && ! $validated['default']) {
            return redirect()->back()->withErrors(__('The default currency cannot be changed.'));
        }

        // Ensure only one default currency exists
        if ($isChangingDefaultCurrency) {
            Currency::where('default', true)
                ->whereKeyNot($currency->getKey())
                ->update(['default' => false]);
        } else {
            $this->enforceDefaultCurrency();
        }

        // Process and assign the flag image if present
        if ($request->hasFile('flag')) {
            // Remove the old flag image if necessary
            $validated['flag'] = $this->handleFlagUpload($request, $currency->flag);
        }

        // Update currency record and notify user
        $currency->update($validated);

        if ($isChangingDefaultCurrency) {
            $currency->forceFill([
                'default'     => true,
                'auto_wallet' => true,
                'status'      => true,
            ])->save();
        }

        // Get roles data from the request
        $rolesData = $request->input('roles', []);

        // Loop through the roles and update them
        foreach ($rolesData as $id => $role) {
            // Find the role by its ID, and if it exists, update it
            $currency->roles()->where('id', $id)->update([
                'fee'       => $role['fee']       ?? 0,
                'fee_type'  => $role['fee_type']  ?? null,
                'min_limit' => $role['min_limit'] ?? 0,
                'max_limit' => $role['max_limit'] ?? null,
                'is_active' => $role['status']    ?? false,
            ]);
        }
        notifyEvs('success', __('Currency updated successfully'));

        return redirect()->back();
    }

    public function edit(Currency $currency)
    {
        $this->ensureCurrencyRoles($currency);

        // Eager load the 'roles' relationship along with the currency
        $currency->load('roles');

        return view('backend.currencies.edit', compact('currency'))->render();
    }

    public function destroy(Currency $currency)
    {
        // Prevent deleting the default currency
        if ($currency->default) {
            return redirect()->back()->withErrors(__('The default currency cannot be deleted.'));
        }

        // Check if there are active wallets associated with this currency
        if (DB::table('wallets')->where('currency_id', $currency->id)->exists()) {
            return redirect()->back()->withErrors(__('Cannot delete currency with active wallets.'));
        }

        try {
            // Use a database transaction for consistency
            DB::transaction(function () use ($currency) {
                // Remove the flag image if it exists
                if ($currency->flag) {
                    $this->delete($currency->flag);
                }

                // Delete the currency record
                $currency->delete();
            });

            // Success notification
            notifyEvs('success', __('Currency deleted successfully'));

            return redirect()->back();
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Currency Deletion Error: '.$e->getMessage());

            notifyEvs('error', __('An error occurred while deleting the currency.'));

            // Return with an error message
            return redirect()->back();
        }
    }

    // Show the form for editing the specified currency

    /**
     * Validate the currency data from the request.
     */
    protected function validateCurrencyData(Request $request, $isUpdate = false)
    {
        $rules = [
            // Flag is optional — controller falls back to a placeholder
            // if none uploaded. Previously this was 'required' on create,
            // which produced a silent validation failure when the admin
            // forgot to pick a file (the modal closes on redirect-back
            // and the toast was easy to miss).
            'flag'          => ['nullable', 'image', 'max:4096'],
            'name'          => ['required', 'string', 'max:255'],
            'code'          => ['required', 'string', 'max:10', $isUpdate ? null : 'unique:currencies,code'],
            'symbol'        => ['required', 'string', 'max:10'],
            'type'          => ['required', 'in:'.implode(',', CurrencyType::getTypes())],
            'exchange_rate' => ['required_if:rate_live,0', 'nullable'],
            'rate_live'     => ['boolean'],
            // Roles is optional — controller auto-fills sane defaults
            // for any role that's missing, so the create can't silently
            // fail just because the toggle-collapsed body didn't post
            // anything.
            'roles' => ['nullable', 'array'],
        ];

        $messages = [
            'name.required'   => __('Pick a currency name from the dropdown after choosing a type.'),
            'code.required'   => __('Currency code is required.'),
            'code.unique'     => __('A currency with this code already exists.'),
            'symbol.required' => __('Currency symbol is required.'),
            'type.required'   => __('Choose whether this is a fiat or crypto currency.'),
        ];

        return $request->validate(array_map(fn ($rule) => is_array($rule) ? array_filter($rule) : $rule, $rules), $messages);
    }

    // Update the specified currency in storage

    /**
     * Convert a value to boolean.
     */
    protected function toBoolean($value)
    {
        return (bool) $value;
    }

    /**
     * Ensure at least one currency is set as default.
     *
     * Guarded against an empty table — on a brand-new install (or
     * any state where every currency has been deleted) Currency::first()
     * returns null and `null->update(...)` used to 500 the create POST.
     */
    protected function enforceDefaultCurrency(): void
    {
        if (Currency::query()->where('default', true)->exists()) {
            return;
        }

        $first = Currency::query()->first();
        if ($first === null) {
            return;
        }

        $first->update(['default' => true]);
    }

    /**
     * Handle flag image upload if the file is present.
     */
    protected function handleFlagUpload(Request $request, $old = null)
    {
        return $request->hasFile('flag') ? self::uploadImage($request->file('flag'), $old) : null;
    }

    private function ensureCurrencyRoles(Currency $currency): void
    {
        $existingRoles = $currency->roles()->pluck('role_name')->all();

        foreach (CurrencyRole::getRoles() as $roleName) {
            if (in_array($roleName, $existingRoles, true)) {
                continue;
            }

            $currency->roles()->create([
                'role_name' => $roleName,
                'fee'       => 0,
                'fee_type'  => null,
                'min_limit' => 0,
                'max_limit' => null,
                'is_active' => false,
            ]);
        }
    }

    /**
     * Return exchange rates for currencies as JSON without blocking the index page.
     */
    public function rates(Request $request)
    {
        $base  = $request->input('base', siteCurrency());
        $codes = collect($request->input('codes', []))
            ->filter()
            ->values();

        $query = Currency::query()->select(['id', 'code', 'exchange_rate', 'rate_live', 'status']);
        if ($codes->isNotEmpty()) {
            $query->whereIn('code', $codes);
        }

        $currencies = $query->get();

        /** @var CurrencyConversionService $service */
        $service  = app(CurrencyConversionService::class);
        $decimals = (int) (setting('site_decimal', 2));

        $data = [];

        foreach ($currencies as $currency) {
            // Start with stored/cached value to avoid accessor (which may call external API)
            $rate = (float) $currency->getRawOriginal('exchange_rate');

            if ($currency->rate_live) {
                try {
                    $live = $service->convertCurrency(1, (string) $base, (string) $currency->code);
                    if ($live !== null) {
                        $rate = round((float) $live, $decimals);
                    }
                } catch (\Throwable $e) {
                    // Keep fallback rate
                }
            }

            $data[] = [
                'id'   => $currency->id,
                'code' => $currency->code,
                'rate' => number_format((float) $rate, $decimals, '.', ''),
                'live' => (bool) $currency->rate_live,
            ];
        }

        return response()->json([
            'base'      => $base,
            'data'      => $data,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
