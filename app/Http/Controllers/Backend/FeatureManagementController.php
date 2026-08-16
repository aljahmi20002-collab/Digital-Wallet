<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\UpdateFeatureRequest;
use App\Models\Feature;
use App\Models\FeatureAccessRule;
use App\Services\FeatureManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureManagementController extends BaseController
{
    public function __construct(private readonly FeatureManager $features) {}

    public static function permissions(): array
    {
        return [
            'index'  => 'feature-list',
            'edit'   => 'feature-list',
            'update' => 'feature-manage',
            'toggle' => 'feature-manage',
        ];
    }

    public function index(): View
    {
        $this->features->sync();

        $catalogKeys = array_keys((array) config('feature_catalog.features', []));

        $features = $this->features
            ->all()
            ->filter(fn (Feature $feature): bool => in_array($feature->key, $catalogKeys, true))
            ->values();

        $roleControls = $features->filter(
            fn (Feature $feature): bool => $this->isRoleControlFeature($feature)
        );

        $summary = [
            'total'    => $features->count(),
            'enabled'  => $features->where('is_enabled', true)->count(),
            'disabled' => $features->where('is_enabled', false)->count(),
            'core'     => $features->where('is_core', true)->count(),
            'roles'    => $roleControls->count(),
        ];

        return view('backend.feature_management.index', [
            'features' => $features,
            'summary'  => $summary,
        ]);
    }

    public function edit(Feature $feature): View|RedirectResponse
    {
        if ($this->isRoleControlFeature($feature)) {
            return redirect()
                ->route('admin.features.index')
                ->with('notifyevs', [
                    'type'    => 'warning',
                    'message' => __('":name" is a role control and can only be enabled or disabled.', ['name' => $feature->label]),
                ]);
        }

        $feature->loadMissing('accessRules');

        $supportedPanels = $this->supportedPanelsFor($feature);

        $rulesByPanel = collect($supportedPanels)->mapWithKeys(function (string $panel) use ($feature) {
            $rule = $feature->accessRules->firstWhere('panel', $panel) ?? new FeatureAccessRule([
                'panel'         => $panel,
                'is_visible'    => true,
                'is_accessible' => true,
                'conditions'    => [],
            ]);

            return [$panel => $rule];
        });

        return view('backend.feature_management.edit', [
            'feature'      => $feature,
            'rulesByPanel' => $rulesByPanel,
            'panelOptions' => $this->panelOptions($supportedPanels),
        ]);
    }

    public function update(UpdateFeatureRequest $request, Feature $feature): RedirectResponse
    {
        if ($this->isRoleControlFeature($feature)) {
            return redirect()
                ->route('admin.features.index')
                ->with('notifyevs', [
                    'type'    => 'warning',
                    'message' => __('":name" is a role control and can only be enabled or disabled.', ['name' => $feature->label]),
                ]);
        }

        $validated = $request->validated();

        $feature->update([
            'is_enabled' => (bool) data_get($validated, 'is_enabled', false),
        ]);

        $supportedPanels = $this->supportedPanelsFor($feature);

        foreach ($supportedPanels as $panel) {
            $panelData = (array) data_get($validated, "panels.{$panel}", []);

            $conditions = [
                'requires_kyc'      => (bool) data_get($panelData, 'requires_kyc', false),
                'requires_phone'    => (bool) data_get($panelData, 'requires_phone', false),
                'countries_allowed' => $this->normalizeCountries((string) data_get($panelData, 'countries_allowed', '')),
            ];

            FeatureAccessRule::query()->updateOrCreate(
                ['feature_id' => $feature->id, 'panel' => $panel],
                [
                    'is_visible'    => (bool) data_get($panelData, 'is_visible', false),
                    'is_accessible' => (bool) data_get($panelData, 'is_accessible', false),
                    'conditions'    => $conditions,
                ]
            );
        }

        // Drop any pre-existing rule rows for panels that are no longer
        // supported by this feature (e.g. a stale agent rule on send_money
        // left over before this filter was introduced).
        FeatureAccessRule::query()
            ->where('feature_id', $feature->id)
            ->whereNotIn('panel', $supportedPanels)
            ->delete();

        $this->features->flush();

        return redirect()
            ->route('admin.features.index')
            ->with('notifyevs', [
                'type'    => 'success',
                'message' => __('Feature ":name" updated successfully.', ['name' => $feature->label]),
            ]);
    }

    public function toggle(Request $request, Feature $feature): RedirectResponse
    {
        $validated = $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        $feature->update(['is_enabled' => (bool) $validated['is_enabled']]);

        $this->features->flush();

        return back()->with('notifyevs', [
            'type'    => 'success',
            'message' => $feature->is_enabled
                ? __('":name" is now enabled across the platform.', ['name' => $feature->label])
                : __('":name" has been disabled and will be hidden everywhere.', ['name' => $feature->label]),
        ]);
    }

    /**
     * Build a label list for the panels supported by the current feature.
     *
     * @param  array<int, string>    $supportedPanels
     * @return array<string, string>
     */
    private function panelOptions(array $supportedPanels = []): array
    {
        $all = [
            FeatureAccessRule::PANEL_USER     => __('User Panel'),
            FeatureAccessRule::PANEL_MERCHANT => __('Merchant Panel'),
            FeatureAccessRule::PANEL_AGENT    => __('Agent Panel'),
        ];

        if ($supportedPanels === []) {
            return $all;
        }

        return array_intersect_key($all, array_flip($supportedPanels));
    }

    /**
     * Read the catalog config to discover which panels this feature
     * actually targets. Falls back to every panel only when the catalog
     * does not declare any (defensive — should not happen in practice).
     *
     * @return array<int, string>
     */
    private function supportedPanelsFor(Feature $feature): array
    {
        $declared = (array) config("feature_catalog.features.{$feature->key}.panels", []);

        $keys = array_keys($declared);

        // Keep only canonical panel codes that the FeatureAccessRule model
        // knows about; anything else (typos, removed panels) is ignored.
        $valid = array_values(array_intersect($keys, FeatureAccessRule::PANELS));

        return $valid !== [] ? $valid : FeatureAccessRule::PANELS;
    }

    private function isRoleControlFeature(Feature $feature): bool
    {
        return config("feature_catalog.features.{$feature->key}.manage_mode") === 'role_toggle';
    }

    /**
     * @return array<int, string>
     */
    private function normalizeCountries(string $raw): array
    {
        $parts = array_filter(array_map('trim', explode(',', $raw)));

        return array_values(array_unique(array_map('strtoupper', $parts)));
    }
}
