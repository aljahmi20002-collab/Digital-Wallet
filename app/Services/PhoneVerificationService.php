<?php

namespace App\Services;

use App\Exceptions\NotifyErrorException;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use App\Services\PhoneVerification\PhoneVerificationProviderManager;
use Illuminate\Support\Facades\Hash;

class PhoneVerificationService
{
    public function __construct(
        private readonly PhoneVerificationProviderManager $providers,
    ) {}

    /**
     * @throws NotifyErrorException
     */
    public function send(User $user): PhoneVerificationCode
    {
        $phone = $this->normalizedPhone($user);

        if ($phone === '') {
            throw new NotifyErrorException(__('Please add a phone number before requesting verification.'));
        }

        if ($user->hasEnabledPhoneVerification()) {
            throw new NotifyErrorException(__('Phone verification is already enabled.'));
        }

        $cooldown = (int) config('mobile_services.phone_verification.resend_cooldown_seconds', 60);
        $recent   = PhoneVerificationCode::query()
            ->where('user_id', $user->id)
            ->where('phone_number', $phone)
            ->where('created_at', '>=', now()->subSeconds($cooldown))
            ->latest()
            ->first();

        if ($recent) {
            throw new NotifyErrorException(__('Please wait before requesting another verification code.'));
        }

        $code = $this->generateCode();

        $verificationCode = PhoneVerificationCode::query()->create([
            'user_id'      => $user->id,
            'phone_number' => $phone,
            'code_hash'    => Hash::make($code),
            'attempts'     => 0,
            'sent_at'      => now(),
            'expires_at'   => now()->addMinutes((int) config('mobile_services.phone_verification.expires_minutes', 10)),
        ]);

        $this->providers->activeProvider()->send($user, $code);

        return $verificationCode;
    }

    /**
     * @throws NotifyErrorException
     */
    public function verify(User $user, string $code): void
    {
        $phone = $this->normalizedPhone($user);

        if ($phone === '') {
            throw new NotifyErrorException(__('Please add a phone number before verifying it.'));
        }

        $verificationCode = PhoneVerificationCode::query()
            ->where('user_id', $user->id)
            ->where('phone_number', $phone)
            ->whereNull('verified_at')
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();

        if (! $verificationCode) {
            throw new NotifyErrorException(__('No active phone verification code was found.'));
        }

        $maxAttempts = (int) config('mobile_services.phone_verification.max_attempts', 5);

        if ($verificationCode->attempts >= $maxAttempts) {
            throw new NotifyErrorException(__('Too many wrong verification attempts. Please request a new code.'));
        }

        if (! Hash::check($code, $verificationCode->code_hash)) {
            $verificationCode->increment('attempts');

            throw new NotifyErrorException(__('The verification code is invalid.'));
        }

        $verificationCode->update(['verified_at' => now()]);

        $user->forceFill([
            'phone_verified_at'          => now(),
            'phone_verification_enabled' => true,
        ])->save();
    }

    private function normalizedPhone(User $user): string
    {
        return trim((string) $user->phone);
    }

    private function generateCode(): string
    {
        $testingCode = (string) config('mobile_services.phone_verification.testing_code', '');

        if (app()->environment('testing') && $testingCode !== '') {
            return $testingCode;
        }

        $length = max(4, (int) config('mobile_services.phone_verification.code_length', 6));
        $max    = (10 ** $length) - 1;
        $number = random_int(0, $max);

        return str_pad((string) $number, $length, '0', STR_PAD_LEFT);
    }
}
