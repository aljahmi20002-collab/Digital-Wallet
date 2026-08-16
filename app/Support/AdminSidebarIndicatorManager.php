<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\KycStatus;
use App\Enums\MerchantStatus;
use App\Enums\MethodType;
use App\Enums\P2P\DisputeStatus;
use App\Enums\TicketStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\VirtualCard\VirtualCardRequestStatus;
use App\Models\KycSubmission;
use App\Models\Merchant;
use App\Models\P2P\Dispute;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\VirtualCardRequest;

final class AdminSidebarIndicatorManager
{
    public static function build(array $adminPermissions = []): array
    {
        return self::buildFromDefinitions(self::definitions(), $adminPermissions);
    }

    public static function buildFromDefinitions(array $definitions, array $adminPermissions = []): array
    {
        $routes = [];
        $groups = [];

        foreach ($definitions as $definition) {
            if (! self::passesPermission(data_get($definition, 'permission'), $adminPermissions)) {
                continue;
            }

            $count = (int) value(data_get($definition, 'count', 0));

            if ($count < 1) {
                continue;
            }

            $payload = self::makePayload($count, (string) data_get($definition, 'tone', 'attention'));
            $route   = (string) data_get($definition, 'route', '');
            $group   = (string) data_get($definition, 'group', '');

            if ($route !== '') {
                $routes[$route] = $payload;
            }

            if ($group !== '') {
                $existingCount  = (int) data_get($groups, $group.'.count', 0);
                $groupTone      = (string) data_get($definition, 'group_tone', $payload['tone']);
                $groups[$group] = self::makePayload($existingCount + $count, $groupTone);
            }
        }

        return [
            'routes' => $routes,
            'groups' => $groups,
        ];
    }

    public static function definitions(): array
    {
        return [
            [
                'group'      => 'merchant-management',
                'route'      => 'admin.merchant.pending',
                'permission' => 'merchant-list',
                'tone'       => 'pending',
                'group_tone' => 'pending',
                'count'      => static fn (): int => Merchant::query()->where('status', MerchantStatus::PENDING)->count(),
            ],
            [
                'group'      => 'kyc-manage',
                'route'      => 'admin.kyc.pending',
                'permission' => 'kyc-list',
                'tone'       => 'review',
                'group_tone' => 'review',
                'count'      => static fn (): int => KycSubmission::query()->where('status', KycStatus::PENDING)->count(),
            ],
            [
                'group'      => 'deposit-management',
                'route'      => 'admin.deposit.manual-request',
                'permission' => 'deposit-list',
                'tone'       => 'pending',
                'group_tone' => 'pending',
                'count'      => static fn (): int => Transaction::query()
                    ->where('trx_type', TrxType::DEPOSIT)
                    ->where('status', TrxStatus::PENDING)
                    ->where('processing_type', MethodType::MANUAL)
                    ->count(),
            ],
            [
                'group'      => 'withdraw-management',
                'route'      => 'admin.withdraw.manual-request',
                'permission' => 'withdraw-list',
                'tone'       => 'pending',
                'group_tone' => 'pending',
                'count'      => static fn (): int => Transaction::query()
                    ->where('trx_type', TrxType::WITHDRAW)
                    ->where('status', TrxStatus::PENDING)
                    ->where('processing_type', MethodType::MANUAL)
                    ->count(),
            ],
            [
                'group'      => 'virtual-card-management',
                'route'      => 'admin.virtual-card.requests.awaiting',
                'permission' => 'virtual-card-action',
                'tone'       => 'review',
                'group_tone' => 'review',
                'count'      => static fn (): int => VirtualCardRequest::query()
                    ->where('status', VirtualCardRequestStatus::Pending)
                    ->count(),
            ],
            [
                'group'      => 'support-ticket',
                'route'      => 'admin.support-ticket.new',
                'permission' => 'support-ticket-list',
                'tone'       => 'info',
                'group_tone' => 'info',
                'count'      => static fn (): int => Ticket::query()->where('status', TicketStatus::PENDING)->count(),
            ],
            [
                'group'      => 'p2p-management',
                'route'      => 'admin.p2p.disputes.index',
                'permission' => 'p2p-dispute-manage',
                'tone'       => 'pending',
                'group_tone' => 'pending',
                'count'      => static fn (): int => Dispute::query()->where('status', DisputeStatus::OPEN)->count(),
            ],
        ];
    }

    private static function passesPermission(mixed $permission, array $adminPermissions): bool
    {
        if (! is_string($permission) || $permission === '') {
            return true;
        }

        return in_array($permission, $adminPermissions, true);
    }

    private static function makePayload(int $count, string $tone): array
    {
        return [
            'count'   => $count,
            'display' => $count > 99 ? '99+' : (string) $count,
            'tone'    => in_array($tone, ['pending', 'review', 'info'], true) ? $tone : 'pending',
        ];
    }
}
