<?php

namespace App\Observers;

use App\Enums\AgentStatus;
use App\Models\Agent;
use Illuminate\Support\Str;

class AgentObserver
{
    public function creating(Agent $agent): void
    {
        if (empty($agent->agent_code)) {
            $agent->agent_code = $this->generateUniqueCode();
        }

        if (empty($agent->qr_token)) {
            $agent->qr_token = $this->generateUniqueQrToken();
        }

        if ($agent->qr_enabled === null) {
            $agent->qr_enabled = true;
        }

        if (empty($agent->status)) {
            $autoApprove   = (bool) setting('agent_auto_approve', false);
            $agent->status = $autoApprove ? AgentStatus::APPROVED : AgentStatus::PENDING;
        }

        // Apply the platform default commission only when nothing was passed in.
        if ($agent->commission === null || $agent->commission === '' || (float) $agent->commission === 0.0) {
            $defaultCommission = (float) setting('agent_default_commission', 0);
            if ($defaultCommission > 0) {
                $agent->commission = $defaultCommission;
            }
        }
    }

    private function generateUniqueCode(): string
    {
        $prefix = (string) setting('agent_code_prefix', 'AGT-');

        do {
            $code = $prefix.strtoupper(Str::random(10));
        } while (Agent::query()->withTrashed()->where('agent_code', $code)->exists());

        return $code;
    }

    private function generateUniqueQrToken(): string
    {
        do {
            $token = 'aqr_'.Str::lower(Str::random(32));
        } while (Agent::query()->withTrashed()->where('qr_token', $token)->exists());

        return $token;
    }
}
