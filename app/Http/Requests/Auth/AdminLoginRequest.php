<?php

namespace App\Http\Requests\Auth;

use App\Traits\ReCaptchaValidation;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends FormRequest
{
    use ReCaptchaValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ];

        return $this->addRecaptchaRuleIfConfigured($rules);
    }

    public function messages(): array
    {
        return $this->recaptchaValidationMessages();
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->loginAttemptLimit())) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), $this->loginLockSeconds());
    }

    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->throttleKey());
    }

    public function throttleKey(): string
    {
        return 'admin-login|'.Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    private function loginAttemptLimit(): int
    {
        return max(3, min(10, (int) config('security.login_attempt_limit', 5)));
    }

    private function loginLockSeconds(): int
    {
        return max(1, min(60, (int) config('security.login_lock_minutes', 15))) * 60;
    }
}
