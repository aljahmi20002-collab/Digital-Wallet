<?php

declare(strict_types=1);

namespace App\Support\DemoCredentials;

/**
 * Immutable value object representing one publicly-shareable demo account.
 *
 * Built by {@see DemoCredentialsRepository} from the seeded `users` table
 * and consumed by the `<x-demo-credentials>` Blade component. Carries the
 * minimum surface area the UI needs — no password hashes, no model state.
 */
final class DemoCredential
{
    public function __construct(
        public readonly string $portal,
        public readonly string $displayName,
        public readonly string $roleLabel,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $note = null,
        public readonly ?string $statusLabel = null,
        public readonly ?string $statusTone = null,
    ) {}

    /**
     * @return array{
     *   portal: string,
     *   display_name: string,
     *   role_label: string,
     *   email: string,
     *   password: string,
     *   note: ?string,
     *   status_label: ?string,
     *   status_tone: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'portal'       => $this->portal,
            'display_name' => $this->displayName,
            'role_label'   => $this->roleLabel,
            'email'        => $this->email,
            'password'     => $this->password,
            'note'         => $this->note,
            'status_label' => $this->statusLabel,
            'status_tone'  => $this->statusTone,
        ];
    }
}
