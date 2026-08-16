<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Feature;
use App\Models\FeatureAccessRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Central feature governance service.
 *
 * Single authority used by middleware, controllers, view composers,
 * Blade directives, navigation builders, and API guards to answer the
 * question: "is this feature available right now for this actor?".
 *
 * Resolution order:
 *   1. Global `is_enabled` flag on the Feature row.
 *   2. Panel visibility (`feature_access_rules.is_visible`).
 *   3. Panel accessibility (`feature_access_rules.is_accessible`).
 *   4. Conditional rules (requires_kyc, requires_phone, countries_allowed, ...).
 *
 * A failing step short-circuits the feature to "off" - menus, routes,
 * buttons, widgets and API endpoints all behave as if the feature does
 * not exist.
 */
class FeatureManager
{
    public const CACHE_KEY = 'feature_manager.catalog.v1';

    public const CACHE_TTL_MINUTES = 60 * 24;

    public const DENIED_DISABLED = 'disabled';

    public const DENIED_PANEL_HIDDEN = 'panel_hidden';

    public const DENIED_PANEL_BLOCKED = 'panel_blocked';

    public const DENIED_KYC_REQUIRED = 'kyc_required';

    public const DENIED_PHONE_REQUIRED = 'phone_required';

    public const DENIED_COUNTRY_BLOCKED = 'country_blocked';

    /**
     * In-process cache of the catalog (per-request).
     *
     * @var Collection<string, Feature>|null
     */
    private ?Collection $catalog = null;

    /**
     * Resolve all features keyed by their string key.
     *
     * @return Collection<string, Feature>
     */
    public function all(): Collection
    {
        if ($this->catalog instanceof Collection) {
            return $this->catalog;
        }

        if (! $this->hasStorage()) {
            return $this->catalog = new Collection;
        }

        $records = Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_TTL_MINUTES), function (): array {
            return Feature::query()
                ->with('accessRules')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->all();
        });

        return $this->catalog = (new Collection($records))->keyBy('key');
    }

    /**
     * @return Collection<string, Feature>
     */
    public function enabled(): Collection
    {
        return $this->all()->filter(fn (Feature $feature): bool => $feature->is_enabled);
    }

    /**
     * Group features by catalog category, following the configured order.
     *
     * @return array<string, array{label: string, icon: string, features: array<int, Feature>}>
     */
    public function grouped(): array
    {
        $categories = (array) config('feature_catalog.categories', []);
        $buckets    = [];

        foreach ($categories as $key => $meta) {
            $buckets[$key] = [
                'label'    => (string) data_get($meta, 'label', str($key)->headline()->toString()),
                'icon'     => (string) data_get($meta, 'icon', 'apps-1'),
                'order'    => (int) data_get($meta, 'order', 99),
                'features' => [],
            ];
        }

        foreach ($this->all() as $feature) {
            $categoryKey = $feature->category;

            if (! isset($buckets[$categoryKey])) {
                $buckets[$categoryKey] = [
                    'label'    => str($categoryKey)->headline()->toString(),
                    'icon'     => 'apps-1',
                    'order'    => 99,
                    'features' => [],
                ];
            }

            $buckets[$categoryKey]['features'][] = $feature;
        }

        uasort($buckets, fn ($a, $b) => $a['order'] <=> $b['order']);

        return array_filter($buckets, fn (array $bucket): bool => $bucket['features'] !== []);
    }

    public function find(string $key): ?Feature
    {
        return $this->all()->get($key);
    }

    /**
     * Pure global on/off check - ignores panels/rules.
     */
    public function isEnabled(string $key): bool
    {
        $feature = $this->find($key);

        return $feature !== null && $feature->is_enabled;
    }

    /**
     * Should this feature appear in menus, dashboards, widgets for the given panel?
     */
    public function isVisible(string $key, ?string $panel = null): bool
    {
        $feature = $this->find($key);

        if ($feature === null || ! $feature->is_enabled) {
            return false;
        }

        $panel = $panel ?? $this->currentPanel();

        if ($panel === 'admin') {
            return true;
        }

        $rule = $this->accessRuleForPanel($feature, $panel);

        return $rule === null ? true : (bool) $rule->is_visible;
    }

    /**
     * Can the given actor (or current user) actually use this feature right now?
     */
    public function isAccessible(string $key, ?string $panel = null, ?User $user = null): bool
    {
        return $this->denialReason($key, $panel, $user) === null;
    }

    /**
     * Resolve *why* a feature is not accessible for the given actor, or
     * null when it is. Used by middleware and menu composers to deliver
     * actionable error messages instead of opaque 404s.
     */
    public function denialReason(string $key, ?string $panel = null, ?User $user = null): ?string
    {
        $feature = $this->find($key);

        if ($feature === null || ! $feature->is_enabled) {
            return self::DENIED_DISABLED;
        }

        $panel = $panel ?? $this->currentPanel($user);

        if ($panel === 'admin') {
            return null;
        }

        $rule = $this->accessRuleForPanel($feature, $panel);

        if ($rule === null) {
            return null;
        }

        if (! $rule->is_visible) {
            return self::DENIED_PANEL_HIDDEN;
        }

        if (! $rule->is_accessible) {
            return self::DENIED_PANEL_BLOCKED;
        }

        return $this->conditionFailure($rule, $user ?? $this->currentUser());
    }

    /**
     * Resolve the panel code for the current request/guard, or for a given user.
     *
     * Panel is derived from the *request surface* (URL) first so an admin who
     * is simultaneously logged-in as a regular user sees the user-panel
     * ruleset while browsing the frontend, instead of inheriting the
     * admin "always on" bypass.
     */
    public function currentPanel(?User $user = null): string
    {
        if ($this->isAdminRequest()) {
            return 'admin';
        }

        $actor = $user ?? $this->currentUser();

        if ($actor instanceof User && $actor->isMerchant()) {
            return FeatureAccessRule::PANEL_MERCHANT;
        }

        if ($actor instanceof User && $actor->isAgent()) {
            return FeatureAccessRule::PANEL_AGENT;
        }

        return FeatureAccessRule::PANEL_USER;
    }

    /**
     * Determine whether the active request targets the admin surface.
     *
     * Uses the request URL when available so the panel is decided by
     * *where* the user is, not by which guards happen to be logged in.
     * Falls back to the admin auth guard for console contexts where no
     * request is bound (queues, commands, scheduler).
     */
    private function isAdminRequest(): bool
    {
        if (app()->bound('request')) {
            $request = app('request');

            if ($request instanceof Request && $request->is('admin', 'admin/*')) {
                return true;
            }

            if ($request instanceof Request) {
                return false;
            }
        }

        return Auth::guard('admin')->check();
    }

    /**
     * Sync the catalog config into the database. Idempotent.
     *
     * New features are inserted; existing ones have their immutable meta
     * (label, description, icon, category, is_core) refreshed while admin-
     * controlled toggles (is_enabled, rules, conditions) are preserved.
     * Features that disappear from the catalog are left untouched – the
     * admin can delete them from the UI if needed.
     */
    public function sync(): void
    {
        if (! $this->hasStorage()) {
            return;
        }

        $catalog = (array) config('feature_catalog.features', []);
        $order   = 0;

        foreach ($catalog as $key => $definition) {
            $feature = Feature::query()->firstOrNew(['key' => $key]);

            $feature->fill([
                'label'       => (string) data_get($definition, 'label', str($key)->headline()->toString()),
                'category'    => (string) data_get($definition, 'category', 'general'),
                'description' => (string) data_get($definition, 'description', ''),
                'icon'        => (string) data_get($definition, 'icon', ''),
                'is_core'     => (bool) data_get($definition, 'is_core', false),
                'sort_order'  => ++$order,
            ]);

            if (! $feature->exists) {
                $feature->is_enabled = (bool) data_get($definition, 'is_enabled', true);
                $feature->meta       = (array) data_get($definition, 'meta', []);
            }

            $feature->save();

            foreach ((array) data_get($definition, 'panels', []) as $panel => $defaultVisible) {
                $rule = FeatureAccessRule::query()->firstOrNew([
                    'feature_id' => $feature->id,
                    'panel'      => (string) $panel,
                ]);

                if (! $rule->exists) {
                    $rule->is_visible    = (bool) $defaultVisible;
                    $rule->is_accessible = (bool) $defaultVisible;
                    $rule->conditions    = (array) data_get($definition, 'rules', []);
                } else {
                    $defaultRules = (array) data_get($definition, 'rules', []);

                    if ($defaultRules !== []) {
                        $rule->conditions = array_replace($defaultRules, $rule->conditions ?? []);
                    }
                }

                $rule->save();
            }
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->catalog = null;
        Cache::forget(self::CACHE_KEY);
    }

    private function conditionFailure(FeatureAccessRule $rule, ?User $user): ?string
    {
        if ($rule->requiresKyc() && ! ($user instanceof User && $user->isKycVerified())) {
            return self::DENIED_KYC_REQUIRED;
        }

        if ($rule->requiresPhone() && ! ($user instanceof User && $user->hasEnabledPhoneVerification())) {
            return self::DENIED_PHONE_REQUIRED;
        }

        $allowedCountries = $rule->allowedCountries();

        if ($allowedCountries !== [] && $user instanceof User) {
            $userCountry = strtoupper((string) ($user->country ?? ''));

            if ($userCountry !== '' && ! in_array($userCountry, $allowedCountries, true)) {
                return self::DENIED_COUNTRY_BLOCKED;
            }
        }

        return null;
    }

    private function accessRuleForPanel(Feature $feature, string $panel): ?FeatureAccessRule
    {
        $rule = $feature->accessRules->firstWhere('panel', $panel);

        if ($rule === null && $panel === FeatureAccessRule::PANEL_AGENT) {
            return $feature->accessRules->firstWhere('panel', FeatureAccessRule::PANEL_USER);
        }

        return $rule;
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function hasStorage(): bool
    {
        try {
            return Schema::hasTable('features');
        } catch (\Throwable) {
            return false;
        }
    }
}
