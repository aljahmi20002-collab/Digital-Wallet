<?php

namespace App\Services;

use App\Enums\MerchantStatus;
use App\Enums\MethodType;
use App\Models\Admin;
use App\Models\DepositMethod;
use App\Models\Merchant;
use App\Models\User;
use App\Notifications\TemplateNotification;
use App\Traits\FileManageTrait;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class MerchantService
{
    use FileManageTrait;

    /**
     * @param array<string, mixed> $validated
     */
    public function createForUser(User $user, array $validated, mixed $logoFile = null): Merchant
    {
        $currencyIds            = $this->currencyIdsFrom($validated);
        $payload                = collect($validated)->except('currency_ids')->all();
        $payload['user_id']     = $user->id;
        $payload['currency_id'] = $currencyIds[0];

        if ($logoFile) {
            $payload['business_logo'] = $this->uploadImage($logoFile);
        }

        $merchant = Merchant::create($payload);
        $this->syncSupportedCurrencies($merchant, $currencyIds);
        $this->notifyAdminsAboutRequest($user, $merchant);

        return $merchant;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function updateMerchant(Merchant $merchant, array $validated, mixed $logoFile = null): Merchant
    {
        $currencyIds            = $this->currencyIdsFrom($validated);
        $payload                = collect($validated)->except('currency_ids')->all();
        $payload['currency_id'] = $currencyIds[0];

        if ($logoFile) {
            $payload['business_logo'] = $this->uploadImage($logoFile, $merchant->getRawOriginal('business_logo'));
        }

        if ($this->requiresFreshReview($merchant, $payload, $currencyIds)) {
            $payload['status'] = MerchantStatus::PENDING;
        }

        $merchant->update($payload);
        $this->syncSupportedCurrencies($merchant, $currencyIds);

        return $merchant->refresh();
    }

    /**
     * Gateway methods that match both the merchant-supported currencies and
     * active merchant wallets.
     *
     * @return EloquentCollection<int, DepositMethod>
     */
    public function eligiblePaymentMethods(Merchant $merchant): EloquentCollection
    {
        $currencyCodes = $this->merchantWalletCurrencyCodes($merchant);

        if ($currencyCodes === []) {
            return new EloquentCollection;
        }

        return DepositMethod::query()
            ->with('paymentGateway')
            ->where('type', MethodType::AUTOMATIC)
            ->where('status', true)
            ->whereIn('currency', $currencyCodes)
            ->orderBy('currency')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param array<int, mixed> $paymentMethodIds
     */
    public function syncPaymentMethods(Merchant $merchant, array $paymentMethodIds): void
    {
        $eligibleIds = $this->eligiblePaymentMethods($merchant)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $syncIds = collect($paymentMethodIds)
            ->map(fn ($id): int => (int) $id)
            ->intersect($eligibleIds)
            ->unique()
            ->values()
            ->all();

        $merchant->paymentMethods()->sync($syncIds);
        $merchant->unsetRelation('paymentMethods');
    }

    /**
     * @return EloquentCollection<int, DepositMethod>
     */
    public function configuredPaymentMethodsForCurrency(Merchant $merchant, string $currencyCode): EloquentCollection
    {
        $merchant->loadMissing('paymentMethods.paymentGateway');

        $methods = $merchant->paymentMethods
            ->filter(fn (DepositMethod $method): bool => $method->type === MethodType::AUTOMATIC
                && (bool) $method->status
                && strtoupper((string) $method->currency) === strtoupper($currencyCode))
            ->values();

        return new EloquentCollection($methods->all());
    }

    /**
     * @param  array<string, mixed> $validated
     * @return array<int, int>
     */
    private function currencyIdsFrom(array $validated): array
    {
        return collect($validated['currency_ids'] ?? [$validated['currency_id'] ?? null])
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->tap(function ($currencyIds): void {
                if ($currencyIds->isEmpty()) {
                    throw new RuntimeException('At least one supported currency is required for merchant creation.');
                }
            })
            ->all();
    }

    /**
     * @param array<int, int> $currencyIds
     */
    private function syncSupportedCurrencies(Merchant $merchant, array $currencyIds): void
    {
        $primaryCurrencyId = $currencyIds[0] ?? (int) $merchant->currency_id;

        $syncPayload = collect($currencyIds)
            ->mapWithKeys(fn (int $currencyId): array => [
                $currencyId => ['is_primary' => $currencyId === $primaryCurrencyId],
            ])
            ->all();

        $merchant->supportedCurrencies()->sync($syncPayload);
    }

    /**
     * @return array<int, string>
     */
    private function merchantWalletCurrencyCodes(Merchant $merchant): array
    {
        $merchant->loadMissing(['currency', 'supportedCurrencies', 'user']);

        $supportedCurrencyCodes = $merchant->supportedCurrencies
            ->pluck('code')
            ->whenEmpty(fn ($codes) => $merchant->currency?->code ? collect([$merchant->currency->code]) : $codes)
            ->map(fn ($code): string => strtoupper((string) $code))
            ->filter()
            ->unique()
            ->values();

        if ($supportedCurrencyCodes->isEmpty() || ! $merchant->user) {
            return [];
        }

        return $merchant->user
            ->wallets()
            ->active()
            ->whereHas('currency', fn ($query) => $query->whereIn('code', $supportedCurrencyCodes->all()))
            ->with('currency')
            ->get()
            ->pluck('currency.code')
            ->map(fn ($code): string => strtoupper((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<int, int>      $currencyIds
     */
    private function requiresFreshReview(Merchant $merchant, array $payload, array $currencyIds): bool
    {
        if ($merchant->status !== MerchantStatus::APPROVED) {
            return false;
        }

        foreach (['business_name', 'site_url'] as $field) {
            if (array_key_exists($field, $payload) && (string) $merchant->{$field} !== (string) $payload[$field]) {
                return true;
            }
        }

        $existingCurrencyIds = $merchant->supportedCurrencyIds();
        sort($existingCurrencyIds);
        sort($currencyIds);

        return $existingCurrencyIds !== $currencyIds;
    }

    protected function notifyAdminsAboutRequest(User $user, Merchant $merchant): void
    {
        try {
            $admins = Admin::permission('merchant-request-notification')->get();
        } catch (PermissionDoesNotExist) {
            return;
        }

        if ($admins->isEmpty()) {
            return;
        }

        $merchant->loadMissing('supportedCurrencies');

        Notification::send($admins, new TemplateNotification(
            identifier: 'merchant_admin_notify_shop_request',
            data: [
                'user'           => $user->name,
                'business_name'  => $merchant->business_name,
                'business_email' => $merchant->business_email,
                'site_url'       => $merchant->site_url,
                'currencies'     => $merchant->supportedCurrencies->pluck('code')->implode(', ') ?: (string) $merchant->currency?->code,
            ],
            sender: $user,
            action: route('admin.merchant.pending')
        ));
    }
}
