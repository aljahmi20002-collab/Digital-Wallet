<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AgentCommissionRuleType;
use App\Enums\AgentOperationType;
use App\Http\Requests\Agent\AgentCommissionRuleRequest;
use App\Models\AgentCommissionRule;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgentCommissionRuleController extends BaseController
{
    public static function permissions(): array
    {
        return [
            '*' => 'agent-commission-rules-manage',
        ];
    }

    public function index(): View
    {
        $title = __('Agent Commission Rules');
        $rules = AgentCommissionRule::query()
            ->with('currency')
            ->withCount('assignments')
            ->orderBy('priority')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $currencies = Currency::query()
            ->where('status', true)
            ->orderBy('code')
            ->get();

        $operationTypes   = ['all' => __('All Operations')] + AgentOperationType::options();
        $calculationTypes = AgentCommissionRuleType::options();

        return view('backend.agent.commission-rules.index', compact(
            'calculationTypes',
            'currencies',
            'operationTypes',
            'rules',
            'title'
        ));
    }

    public function store(AgentCommissionRuleRequest $request): RedirectResponse
    {
        AgentCommissionRule::query()->create($this->payload($request));

        notifyEvs('success', __('Commission rule created successfully.'));

        return to_route('admin.agent.commission-rules.index');
    }

    public function update(AgentCommissionRuleRequest $request, AgentCommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->update($this->payload($request));

        notifyEvs('success', __('Commission rule updated successfully.'));

        return to_route('admin.agent.commission-rules.index');
    }

    public function destroy(AgentCommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->delete();

        notifyEvs('success', __('Commission rule deleted successfully.'));

        return to_route('admin.agent.commission-rules.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AgentCommissionRuleRequest $request): array
    {
        $data                     = $request->validated();
        $data['status']           = $request->boolean('status');
        $data['applies_globally'] = $request->boolean('applies_globally');

        if ($data['calculation_type'] === AgentCommissionRuleType::PERCENTAGE->value) {
            $data['fixed_amount'] = 0;
        }

        if ($data['calculation_type'] === AgentCommissionRuleType::FIXED->value) {
            $data['percentage_rate'] = 0;
        }

        return $data;
    }
}
