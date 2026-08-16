<?php

namespace App\Support;

use App\Enums\KycStatus;
use App\Models\User;

class UserDashboardKycNotice
{
    /**
     * Build the dashboard KYC notice payload for the given user.
     *
     * @return array{state: string, is_approved: bool, dismiss_key: string|null, notice: array{title: string, message: string, icon: string, step: string, cta: string, cta_icon: string}}
     */
    public function forUser(User $user): array
    {
        $kycSubmission = $user->kycSubmission;
        $state         = $this->resolveState($kycSubmission?->status);
        $isApproved    = $state === 'approved';

        return [
            'state'       => $state,
            'is_approved' => $isApproved,
            'dismiss_key' => $isApproved
                ? 'digikash:kyc-verified-notice-dismissed:'.$user->getKey().':'.($kycSubmission?->updated_at?->timestamp ?? 'active')
                : null,
            'notice' => $this->notices()[$state],
        ];
    }

    private function resolveState(?KycStatus $status): string
    {
        return match ($status) {
            KycStatus::APPROVED => 'approved',
            KycStatus::PENDING  => 'pending',
            KycStatus::REJECTED => 'rejected',
            default             => 'missing',
        };
    }

    /**
     * @return array<string, array{title: string, message: string, icon: string, step: string, cta: string, cta_icon: string}>
     */
    private function notices(): array
    {
        return [
            'approved' => [
                'title'    => __('Identity verified'),
                'message'  => __('Your KYC is complete. Your verified wallet features are available.'),
                'icon'     => 'fa-check',
                'step'     => __('Verified'),
                'cta'      => __('View status'),
                'cta_icon' => 'fa-arrow-right',
            ],
            'pending' => [
                'title'    => __('Identity review in progress'),
                'message'  => __('We received your KYC details. Reviews usually finish within 24 hours.'),
                'icon'     => 'fa-shield-alt',
                'step'     => __('Step 2 of 3'),
                'cta'      => __('Check status'),
                'cta_icon' => 'fa-arrow-right',
            ],
            'rejected' => [
                'title'    => __('Update your KYC details'),
                'message'  => __('Some information needs correction. Review the notes and resubmit your documents.'),
                'icon'     => 'fa-times',
                'step'     => __('Action needed'),
                'cta'      => __('Resubmit KYC'),
                'cta_icon' => 'fa-redo-alt',
            ],
            'missing' => [
                'title'    => __('Verify your identity'),
                'message'  => __('Submit your details and documents to unlock secure wallet features.'),
                'icon'     => 'fa-shield-alt',
                'step'     => __('Step 1 of 3'),
                'cta'      => __('Start verification'),
                'cta_icon' => 'fa-arrow-right',
            ],
        ];
    }
}
