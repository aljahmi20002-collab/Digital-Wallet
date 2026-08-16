<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AgentStatus;
use App\Enums\TrxStatus;
use App\Http\Requests\Agent\AgentActionRequest;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Notifications\TemplateNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|pendingAgent|approvedAgent|rejectedAgent' => 'agent-list',
            'agentAction'                                    => 'agent-manage',
        ];
    }

    public function index(Request $request): View
    {
        $title  = __('Agent List');
        $agents = Agent::query()
            ->with(['user', 'currency', 'supportedCurrencies', 'commissionRuleAssignments.rule.currency'])
            ->withSum(['operations as completed_volume' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'amount')
            ->withSum(['operations as earned_commission' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'commission_amount')
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        $commissionRules = $this->commissionRules();

        return view('backend.agent.index', compact('agents', 'commissionRules', 'title'));
    }

    public function pendingAgent(Request $request): View
    {
        $title  = __('Pending Agent');
        $agents = Agent::query()
            ->with(['user', 'currency', 'supportedCurrencies', 'commissionRuleAssignments.rule.currency'])
            ->withSum(['operations as completed_volume' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'amount')
            ->withSum(['operations as earned_commission' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'commission_amount')
            ->where('status', AgentStatus::PENDING)
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        $commissionRules = $this->commissionRules();

        return view('backend.agent.index', compact('agents', 'commissionRules', 'title'));
    }

    public function approvedAgent(Request $request): View
    {
        $title  = __('Approved Agent');
        $agents = Agent::query()
            ->with(['user', 'currency', 'supportedCurrencies', 'commissionRuleAssignments.rule.currency'])
            ->withSum(['operations as completed_volume' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'amount')
            ->withSum(['operations as earned_commission' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'commission_amount')
            ->where('status', AgentStatus::APPROVED)
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        $commissionRules = $this->commissionRules();

        return view('backend.agent.index', compact('agents', 'commissionRules', 'title'));
    }

    public function rejectedAgent(Request $request): View
    {
        $title  = __('Rejected Agent');
        $agents = Agent::query()
            ->with(['user', 'currency', 'supportedCurrencies', 'commissionRuleAssignments.rule.currency'])
            ->withSum(['operations as completed_volume' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'amount')
            ->withSum(['operations as earned_commission' => fn ($query) => $query->where('status', TrxStatus::COMPLETED)], 'commission_amount')
            ->where('status', AgentStatus::REJECTED)
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        $commissionRules = $this->commissionRules();

        return view('backend.agent.index', compact('agents', 'commissionRules', 'title'));
    }

    public function agentAction(AgentActionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $agent       = Agent::query()->with(['currency', 'supportedCurrencies', 'user'])->findOrFail($validated['agent_id']);
        $action      = $validated['action'];
        $updateData  = [];
        $notifyUser  = (bool) setting('agent_user_email_notify', true);
        $rulesToSync = null;

        // Approve (also used for fee/commission updates on already-approved agents)
        if ($action === 'approve') {
            $updateData['commission'] = $validated['commission']       ?? $agent->commission;
            $rulesToSync              = $validated['commission_rules'] ?? [];

            if ($agent->status === AgentStatus::PENDING) {
                $updateData['status'] = AgentStatus::APPROVED;

                if ($notifyUser) {
                    $agent->user->notify(new TemplateNotification(
                        identifier: 'agent_user_notify_request_approved',
                        data: [
                            'agent_name' => $agent->agent_name,
                            'currencies' => $this->supportedCurrencyCodes($agent),
                        ],
                        action: route('user.agent.index', ['tab' => 'counter-cashout']),
                    ));
                }
            }
        }

        if ($action === 'reject' && $agent->status === AgentStatus::PENDING) {
            $updateData['status'] = AgentStatus::REJECTED;

            if ($notifyUser) {
                $agent->user->notify(new TemplateNotification(
                    identifier: 'agent_user_notify_request_rejected',
                    data: [
                        'agent_name'       => $agent->agent_name,
                        'currencies'       => $this->supportedCurrencyCodes($agent),
                        'rejection_reason' => $validated['rejection_reason'] ?? __('No reason provided'),
                    ],
                    action: route('user.agent.index', ['tab' => 'overview']),
                ));
            }
        }

        if ($action === 'disable' && $agent->status === AgentStatus::APPROVED) {
            $updateData['status'] = AgentStatus::DISABLED;
        }

        if ($action === 'enable' && $agent->status === AgentStatus::DISABLED) {
            $updateData['status'] = AgentStatus::APPROVED;
        }

        if (! empty($updateData)) {
            $agent->update($updateData);
        }

        if (is_array($rulesToSync)) {
            $this->syncCommissionRuleAssignments($agent, $rulesToSync);
        }

        notifyEvs('success', __('Agent updated successfully'));

        return back();
    }

    private function commissionRules(): EloquentCollection
    {
        return AgentCommissionRule::query()
            ->where('status', true)
            ->with('currency')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    private function supportedCurrencyCodes(Agent $agent): string
    {
        return $agent->supportedCurrencies->pluck('code')->implode(', ') ?: (string) $agent->currency?->code;
    }

    /**
     * @param array<string, array<string, mixed>> $rules
     */
    private function syncCommissionRuleAssignments(Agent $agent, array $rules): void
    {
        $selectedIds = collect($rules)
            ->filter(fn (array $rule): bool => (bool) ($rule['enabled'] ?? false))
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        $validIds = AgentCommissionRule::query()
            ->where('status', true)
            ->whereIn('id', $selectedIds)
            ->pluck('id')
            ->all();

        $agent->commissionRuleAssignments()->delete();

        foreach ($validIds as $ruleId) {
            $ruleInput = $rules[(string) $ruleId] ?? $rules[$ruleId] ?? [];

            $agent->commissionRuleAssignments()->create([
                'agent_commission_rule_id' => $ruleId,
                'operation_type'           => $ruleInput['operation_type'] ?? 'all',
                'priority'                 => (int) ($ruleInput['priority'] ?? 100),
                'status'                   => true,
            ]);
        }
    }
}
