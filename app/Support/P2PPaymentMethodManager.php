<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\P2P\PaymentAccount;
use App\Models\User;
use Illuminate\Support\Collection;

class P2PPaymentMethodManager
{
    public static function resolveCountryCode(?User $user = null, ?string $countryCode = null): ?string
    {
        $resolved = strtoupper(trim((string) ($countryCode ?: $user?->country ?: '')));

        return preg_match('/^[A-Z]{2}$/', $resolved) === 1 ? $resolved : null;
    }

    public static function sortMethods(Collection $methods, ?string $countryCode = null, array $savedMethodIds = []): Collection
    {
        $countryCode    = self::resolveCountryCode(countryCode: $countryCode);
        $savedMethodIds = collect($savedMethodIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $sorted = $methods->all();

        usort($sorted, function ($left, $right) use ($countryCode, $savedMethodIds): int {
            return self::compareTuples(
                self::methodTuple($left, $countryCode, $savedMethodIds),
                self::methodTuple($right, $countryCode, $savedMethodIds)
            );
        });

        return collect($sorted)->values();
    }

    public static function sortPaymentAccounts(Collection $accounts, ?string $countryCode = null): Collection
    {
        $countryCode = self::resolveCountryCode(countryCode: $countryCode);
        $sorted      = $accounts->all();

        usort($sorted, function ($left, $right) use ($countryCode): int {
            $leftMethod  = $left instanceof PaymentAccount ? $left->paymentMethod : null;
            $rightMethod = $right instanceof PaymentAccount ? $right->paymentMethod : null;

            $methodCompare = self::compareTuples(
                self::methodTuple($leftMethod, $countryCode),
                self::methodTuple($rightMethod, $countryCode)
            );

            if ($methodCompare !== 0) {
                return $methodCompare;
            }

            $leftLabel  = mb_strtolower(trim((string) ($left instanceof PaymentAccount ? ($left->effective_label ?? $left->label ?? '') : '')));
            $rightLabel = mb_strtolower(trim((string) ($right instanceof PaymentAccount ? ($right->effective_label ?? $right->label ?? '') : '')));

            return [$leftLabel, (int) ($left instanceof PaymentAccount ? $left->id : 0)]
                <=> [$rightLabel, (int) ($right instanceof PaymentAccount ? $right->id : 0)];
        });

        return collect($sorted)->values();
    }

    public static function countryOptions(Collection $methods): Collection
    {
        return $methods
            ->pluck('country')
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter(fn (string $code) => $code !== '')
            ->unique()
            ->sort(function (string $left, string $right): int {
                $leftLabel  = mb_strtolower((string) (getCountryDisplayLabel($left, false) ?? $left));
                $rightLabel = mb_strtolower((string) (getCountryDisplayLabel($right, false) ?? $right));

                return [$leftLabel, $left] <=> [$rightLabel, $right];
            })
            ->values();
    }

    private static function methodTuple(mixed $method, ?string $countryCode = null, array $savedMethodIds = []): array
    {
        $methodId      = (int) data_get($method, 'id', 0);
        $methodCountry = strtoupper(trim((string) data_get($method, 'country', '')));
        $sortOrder     = (int) data_get($method, 'sort_order', 0);
        $methodName    = mb_strtolower(trim((string) data_get($method, 'name', '')));

        return [
            self::countryPriority($methodCountry, $countryCode),
            in_array($methodId, $savedMethodIds, true) ? 0 : 1,
            $sortOrder,
            $methodName,
            $methodId,
        ];
    }

    private static function countryPriority(string $methodCountry, ?string $countryCode = null): int
    {
        if ($countryCode !== null && $methodCountry === $countryCode) {
            return 0;
        }

        if ($methodCountry === '') {
            return 1;
        }

        return 2;
    }

    private static function compareTuples(array $left, array $right): int
    {
        $length = max(count($left), count($right));

        for ($index = 0; $index < $length; $index++) {
            $comparison = ($left[$index] ?? null) <=> ($right[$index] ?? null);

            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return 0;
    }
}
