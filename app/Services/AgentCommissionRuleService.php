<?php

namespace App\Services;

use App\Data\CommissionResult;
use App\Enums\AgentCommissionRuleType;
use App\Enums\AgentOperationType;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\AgentCommissionRuleAssignment;
use Illuminate\Support\Collection;

class AgentCommissionRuleService
{
    public function calculate(Agent $agent, AgentOperationType $operationType, float $amount, ?int $currencyId = null): CommissionResult
    {
        $amount = max(0, $amount);
        if ($amount <= 0) {
            return CommissionResult::none();
        }

        $currencyId ??= (int) $agent->currency_id;

        $assignment = $this->matchingAssignment($agent, $operationType, $amount, $currencyId);
        if ($assignment?->rule) {
            return $this->fromRule($assignment->rule, $agent, $operationType, $amount, $currencyId, 'assigned_rule', $assignment);
        }

        $rule = $this->matchingGlobalRule($agent, $operationType, $amount, $currencyId);
        if ($rule) {
            return $this->fromRule($rule, $agent, $operationType, $amount, $currencyId, 'global_rule');
        }

        $agentRate = max(0, (float) $agent->commission);
        if ($agentRate > 0) {
            return $this->fromPercentageFallback('agent_profile', $agentRate, $amount, $currencyId);
        }

        $defaultRate = max(0, (float) setting('agent_default_commission', 0));
        if ($defaultRate > 0) {
            return $this->fromPercentageFallback('global_default', $defaultRate, $amount, $currencyId);
        }

        return CommissionResult::none();
    }

    private function matchingAssignment(Agent $agent, AgentOperationType $operationType, float $amount, int $currencyId): ?AgentCommissionRuleAssignment
    {
        /** @var Collection<int, AgentCommissionRuleAssignment> $assignments */
        $assignments = AgentCommissionRuleAssignment::query()
            ->where('agent_id', $agent->id)
            ->where('status', true)
            ->whereIn('operation_type', ['all', $operationType->value])
            ->with('rule')
            ->get()
            ->filter(fn (AgentCommissionRuleAssignment $assignment): bool => $assignment->rule !== null
                && $this->ruleMatches($assignment->rule, $operationType, $amount, $currencyId));

        return $assignments
            ->sort(function (AgentCommissionRuleAssignment $left, AgentCommissionRuleAssignment $right) use ($currencyId, $operationType): int {
                $scoreComparison = $this->assignmentScore($right, $currencyId, $operationType)
                    <=> $this->assignmentScore($left, $currencyId, $operationType);

                if ($scoreComparison !== 0) {
                    return $scoreComparison;
                }

                $priorityComparison = $left->priority <=> $right->priority;
                if ($priorityComparison !== 0) {
                    return $priorityComparison;
                }

                return $right->id <=> $left->id;
            })
            ->first();
    }

    private function matchingGlobalRule(Agent $agent, AgentOperationType $operationType, float $amount, int $currencyId): ?AgentCommissionRule
    {
        /** @var Collection<int, AgentCommissionRule> $rules */
        $rules = AgentCommissionRule::query()
            ->where('status', true)
            ->where('applies_globally', true)
            ->whereIn('operation_type', ['all', $operationType->value])
            ->where(function ($query) use ($currencyId) {
                $query->whereNull('currency_id')->orWhere('currency_id', $currencyId);
            })
            ->where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            })
            ->where(function ($query) {
                $query->whereNull('effective_from')->orWhere('effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')->orWhere('effective_until', '>=', now());
            })
            ->get();

        return $rules
            ->sort(function (AgentCommissionRule $left, AgentCommissionRule $right) use ($currencyId, $operationType): int {
                $scoreComparison = $this->ruleScore($right, $currencyId, $operationType)
                    <=> $this->ruleScore($left, $currencyId, $operationType);

                if ($scoreComparison !== 0) {
                    return $scoreComparison;
                }

                $priorityComparison = $left->priority <=> $right->priority;
                if ($priorityComparison !== 0) {
                    return $priorityComparison;
                }

                return $right->id <=> $left->id;
            })
            ->first();
    }

    private function ruleMatches(AgentCommissionRule $rule, AgentOperationType $operationType, float $amount, int $currencyId): bool
    {
        if (! $rule->status) {
            return false;
        }

        if (! in_array($rule->operation_type, ['all', $operationType->value], true)) {
            return false;
        }

        if ($rule->currency_id !== null && (int) $rule->currency_id !== $currencyId) {
            return false;
        }

        if ((float) $rule->min_amount > $amount) {
            return false;
        }

        if ($rule->max_amount !== null && (float) $rule->max_amount < $amount) {
            return false;
        }

        if ($rule->effective_from !== null && $rule->effective_from->isFuture()) {
            return false;
        }

        if ($rule->effective_until !== null && $rule->effective_until->isPast()) {
            return false;
        }

        return true;
    }

    private function fromRule(
        AgentCommissionRule $rule,
        Agent $agent,
        AgentOperationType $operationType,
        float $amount,
        int $currencyId,
        string $source,
        ?AgentCommissionRuleAssignment $assignment = null,
    ): CommissionResult {
        $commission = match ($rule->calculation_type) {
            AgentCommissionRuleType::PERCENTAGE => $amount * max(0, (float) $rule->percentage_rate) / 100,
            AgentCommissionRuleType::FIXED      => max(0, (float) $rule->fixed_amount),
        };

        if ($rule->min_commission !== null) {
            $commission = max($commission, (float) $rule->min_commission);
        }

        if ($rule->max_commission !== null) {
            $commission = min($commission, (float) $rule->max_commission);
        }

        $commission = round($commission, $this->decimalPlaces());

        return new CommissionResult(
            ruleId: $rule->id,
            source: $source,
            calculationType: $rule->calculation_type,
            percentageRate: (float) $rule->percentage_rate,
            fixedAmount: (float) $rule->fixed_amount,
            amount: $commission,
            snapshot: [
                'source'               => $source,
                'rule_id'              => $rule->id,
                'rule_name'            => $rule->name,
                'assignment_id'        => $assignment?->id,
                'assignment_operation' => $assignment?->operation_type,
                'assignment_priority'  => $assignment?->priority,
                'operation_type'       => $operationType->value,
                'agent_id'             => $agent->id,
                'currency_id'          => $currencyId,
                'calculation_type'     => $rule->calculation_type->value,
                'percentage_rate'      => (float) $rule->percentage_rate,
                'fixed_amount'         => (float) $rule->fixed_amount,
                'min_commission'       => $rule->min_commission,
                'max_commission'       => $rule->max_commission,
                'amount'               => $commission,
            ],
        );
    }

    private function fromPercentageFallback(string $source, float $rate, float $amount, int $currencyId): CommissionResult
    {
        $commission = round($amount * $rate / 100, $this->decimalPlaces());

        return new CommissionResult(
            ruleId: null,
            source: $source,
            calculationType: AgentCommissionRuleType::PERCENTAGE,
            percentageRate: $rate,
            fixedAmount: 0,
            amount: $commission,
            snapshot: [
                'source'           => $source,
                'currency_id'      => $currencyId,
                'calculation_type' => AgentCommissionRuleType::PERCENTAGE->value,
                'percentage_rate'  => $rate,
                'fixed_amount'     => 0,
                'amount'           => $commission,
            ],
        );
    }

    private function assignmentScore(AgentCommissionRuleAssignment $assignment, int $currencyId, AgentOperationType $operationType): int
    {
        return (int) ($assignment->operation_type === $operationType->value) * 50
            + ($assignment->rule ? $this->ruleScore($assignment->rule, $currencyId, $operationType) : 0);
    }

    private function ruleScore(AgentCommissionRule $rule, int $currencyId, AgentOperationType $operationType): int
    {
        return (int) ((int) $rule->currency_id === $currencyId)       * 20
            + (int) ($rule->operation_type === $operationType->value) * 10
            + (int) ($rule->max_amount !== null);
    }

    private function decimalPlaces(): int
    {
        return max(2, min(8, (int) setting('site_decimal', 2)));
    }
}
