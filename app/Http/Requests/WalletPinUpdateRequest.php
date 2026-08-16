<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class WalletPinUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'current_credential' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($user): void {
                    if (! $this->credentialMatches($user, (string) $value)) {
                        $fail(__('The current password or wallet PIN is incorrect.'));
                    }
                },
            ],
            'pin' => [
                'required',
                'digits:6',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($this->isWeakPin((string) $value)) {
                        $fail(__('Choose a less obvious PIN. Sequential or repeated digits are not allowed.'));
                    }
                },
                'confirmed',
            ],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pin.required'                => __('Please enter a 6-digit PIN.'),
            'pin.digits'                  => __('The PIN must be exactly 6 digits.'),
            'pin.confirmed'               => __('PIN confirmation does not match.'),
            'current_credential.required' => __('Please confirm your current password or wallet PIN.'),
        ];
    }

    /**
     * Verify the supplied credential against the user's password (or current PIN if set).
     */
    private function credentialMatches(?Authenticatable $user, string $credential): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasWalletPin() && Hash::check($credential, $user->wallet_pin)) {
            return true;
        }

        return Hash::check($credential, $user->password);
    }

    /**
     * Reject obvious sequential or repeated PIN choices.
     */
    private function isWeakPin(string $pin): bool
    {
        if (preg_match('/^(\d)\1{5}$/', $pin)) {
            return true;
        }

        $digits     = array_map('intval', str_split($pin));
        $ascending  = true;
        $descending = true;

        for ($i = 1; $i < count($digits); $i++) {
            if ($digits[$i] !== $digits[$i - 1] + 1) {
                $ascending = false;
            }
            if ($digits[$i] !== $digits[$i - 1] - 1) {
                $descending = false;
            }
        }

        return $ascending || $descending;
    }
}
