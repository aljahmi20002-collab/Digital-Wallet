<?php

namespace App\Http\Requests\Agent;

use App\Enums\AgentOperationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minCommission = $this->minCommission();
        $maxCommission = $this->maxCommission();

        return [
            'agent_id'                          => 'required|exists:agents,id',
            'commission'                        => "nullable|numeric|min:{$minCommission}|max:{$maxCommission}",
            'action'                            => 'required|in:approve,reject,disable,enable',
            'rejection_reason'                  => 'nullable|string|max:500',
            'commission_rules'                  => 'nullable|array',
            'commission_rules.*.enabled'        => 'nullable|boolean',
            'commission_rules.*.operation_type' => ['nullable', Rule::in(array_merge(['all'], AgentOperationType::values()))],
            'commission_rules.*.priority'       => 'nullable|integer|min:1|max:999',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'commission.min' => __('Commission must be at least :min%.', ['min' => $this->minCommission()]),
            'commission.max' => __('Commission cannot exceed :max%.', ['max' => $this->maxCommission()]),
        ];
    }

    private function minCommission(): float
    {
        return (float) setting('agent_min_commission', 0);
    }

    private function maxCommission(): float
    {
        return (float) setting('agent_max_commission', 100);
    }
}
