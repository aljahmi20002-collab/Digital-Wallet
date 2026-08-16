<?php

declare(strict_types=1);

namespace App\Support\DemoCredentials;

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Models\KycSubmission;
use App\Models\User;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Support\Collection;

/**
 * Read-only repository that surfaces the demo accounts seeded by
 * {@see DemoAccountSeeder} so the public auth pages can
 * publish their credentials when `APP_DEMO=true`.
 *
 * Source of truth:
 *   - The seeded `users` table (so the credentials block can never drift
 *     out of sync with what's actually in the database).
 *   - A single, shared demo password emitted by the seeder.
 *
 * Production safety:
 *   - When `config('app.demo')` is false, every accessor short-circuits
 *     and returns an empty Collection.
 *   - Only accounts whose email matches the published `@digikash.test`
 *     fixture domain are surfaced — any real user that happens to share
 *     a username or role is filtered out.
 *   - The password constant is hard-coded to the seeder fixture; live
 *     password hashes are never read or exposed.
 */
final class DemoCredentialsRepository
{
    /**
     * Password baked into DemoAccountSeeder for every demo account.
     */
    public const SHARED_PASSWORD = '12345678';

    /**
     * Email suffix used by every seeded demo account. The repository
     * refuses to surface anything outside this fixture domain so a
     * production user with a similar role never leaks here.
     */
    private const FIXTURE_DOMAIN = '@digikash.test';

    /**
     * Per-portal copy for the credentials card (kept here so the Blade
     * stays a pure renderer).
     *
     * @var array<string, array{role_label: string, note: string}>
     */
    private const PORTAL_COPY = [
        'user' => [
            'role_label' => 'User Account',
            'note'       => 'Personal wallet, transfers, KYC.',
        ],
        'merchant' => [
            'role_label' => 'Merchant Account',
            'note'       => 'Checkout, payouts, webhooks.',
        ],
        'agent' => [
            'role_label' => 'Agent Account',
            'note'       => 'Cash-in / cash-out counter.',
        ],
    ];

    /**
     * Whether the demo credentials surface is enabled for this request.
     */
    public function isEnabled(): bool
    {
        return (bool) config('app.demo', false);
    }

    /**
     * Returns demo credentials grouped by portal: ['user' => [...], ...].
     *
     * @return Collection<string, Collection<int, DemoCredential>>
     */
    public function all(): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        $portals = ['user', 'merchant', 'agent'];

        return collect($portals)->mapWithKeys(
            fn (string $portal) => [$portal => $this->forPortal($portal)]
        );
    }

    /**
     * Returns the demo accounts for a single portal.
     *
     * Only Verified (KYC approved) fixture accounts are surfaced so the
     * block stays short — Pending/Rejected demo rows are still useful in
     * the database for admin-side review screens, but on the public auth
     * page we just need the happy-path login. Falls back to the first
     * fixture account if no Verified row exists yet (e.g. KYC seeders
     * haven't been run).
     *
     * @return Collection<int, DemoCredential>
     */
    public function forPortal(string $portal): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        $role = $this->roleFor($portal);

        if ($role === null) {
            return collect();
        }

        $fixtures = User::query()
            ->with('kycSubmission')
            ->where('role', $role)
            ->where('email', 'like', '%'.self::FIXTURE_DOMAIN)
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'username', 'email', 'role', 'business_name']);

        $verified = $fixtures->first(
            fn (User $user) => $this->isApproved($user)
        );

        $primary = $verified ?? $fixtures->first();

        if ($primary === null) {
            return collect();
        }

        return collect([$this->build($portal, $primary)]);
    }

    private function isApproved(User $user): bool
    {
        if (! method_exists($user, 'kycSubmission')) {
            return false;
        }

        $submission = $user->kycSubmission;

        return $submission instanceof KycSubmission
            && ($submission->status ?? null) === KycStatus::APPROVED;
    }

    private function build(string $portal, User $user): DemoCredential
    {
        $copy        = self::PORTAL_COPY[$portal];
        $kycStatus   = $this->latestKycStatus($user);
        $displayName = $this->displayName($portal, $user);

        return new DemoCredential(
            portal: $portal,
            displayName: $displayName,
            roleLabel: __($copy['role_label']),
            email: (string) $user->email,
            password: self::SHARED_PASSWORD,
            note: __($copy['note']),
            statusLabel: $kycStatus?->statusLabel,
            statusTone: $kycStatus?->statusTone,
        );
    }

    private function displayName(string $portal, User $user): string
    {
        if ($portal === 'merchant' && filled($user->business_name)) {
            return (string) $user->business_name;
        }

        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return $name !== '' ? $name : (string) ($user->username ?? $user->email);
    }

    private function roleFor(string $portal): ?UserRole
    {
        return match ($portal) {
            'user'     => UserRole::USER,
            'merchant' => UserRole::MERCHANT,
            'agent'    => UserRole::AGENT,
            default    => null,
        };
    }

    /**
     * Look up the user's KYC submission so the UI can chip "Verified",
     * "Pending review", etc. Missing submissions degrade silently.
     *
     * @return object{statusLabel: string, statusTone: string}|null
     */
    private function latestKycStatus(User $user): ?object
    {
        if (! method_exists($user, 'kycSubmission')) {
            return null;
        }

        $submission = $user->loadMissing('kycSubmission')->kycSubmission;

        if ($submission === null) {
            return null;
        }

        return match ($submission->status ?? null) {
            KycStatus::APPROVED => (object) ['statusLabel' => __('Verified'), 'statusTone' => 'success'],
            KycStatus::PENDING  => (object) ['statusLabel' => __('Pending review'), 'statusTone' => 'warning'],
            KycStatus::REJECTED => (object) ['statusLabel' => __('Rejected'), 'statusTone' => 'danger'],
            default             => null,
        };
    }
}
