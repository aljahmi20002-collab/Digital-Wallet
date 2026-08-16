<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\P2P\PaymentAccount;
use App\Services\WalletService;
use App\Traits\Models\Concerns\HasNotificationPreferences;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory;
    use HasNotificationPreferences;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'referral_code',
        'rank_id',
        'old_ranks',
        'avatar',
        'first_name',
        'last_name',
        'business_name',
        'business_address',
        'username',
        'gender',
        'birthday',
        'email',
        'phone',
        'phone_verified_at',
        'phone_verification_enabled',
        'state',
        'city',
        'postal_code',
        'country',
        'address',
        'role',
        'google2fa_secret',
        'two_factor_enabled',
        'status',
        'password',
        'wallet_pin',
        'email_verified_at',
        'signup_bonus_awarded_at',
        'signup_bonus_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'wallet_pin',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * (If you're using Laravel older approach, keep $casts in the property.)
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_ranks'                  => 'array',
            'role'                       => UserRole::class,
            'gender'                     => Gender::class,
            'status'                     => UserStatus::class,
            'email_verified_at'          => 'datetime',
            'phone_verified_at'          => 'datetime',
            'signup_bonus_awarded_at'    => 'datetime',
            'signup_bonus_seen_at'       => 'datetime',
            'phone_verification_enabled' => 'boolean',
            'password'                   => 'hashed',
            'wallet_pin'                 => 'hashed',
            'p2p_trading_suspended_at'   => 'datetime',
        ];
    }

    /**
     * Determine whether the user has configured a wallet PIN.
     */
    public function hasWalletPin(): bool
    {
        return ! empty($this->wallet_pin);
    }

    public function hasVerifiedPhone(): bool
    {
        return ! empty($this->phone) && $this->phone_verified_at !== null;
    }

    public function hasEnabledPhoneVerification(): bool
    {
        return $this->hasVerifiedPhone() && (bool) $this->phone_verification_enabled;
    }

    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }

    public function isP2pTradingSuspended(): bool
    {
        return $this->getAttribute('p2p_trading_suspended_at') !== null;
    }

    public function scopeActive()
    {
        return $this->where('status', UserStatus::ACTIVE);
    }

    public function scopeFilter(Builder $query, Request $request): Builder
    {
        return $query->with(['kycSubmission', 'latestLoginActivity'])
            ->when($request->filled('role') && $request->role !== 'all', function ($q) use ($request) {
                $q->where('role', $request->role);
            })
            ->when($request->filled('kyc_status') && $request->kyc_status !== 'all', function ($q) use ($request) {
                $q->whereHas('kycSubmission', function ($subQuery) use ($request) {
                    $subQuery->where('status', $request->kyc_status);
                });
            })
            ->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('email_verified') && $request->email_verified !== 'all', function ($q) use ($request) {
                $request->email_verified == '1'
                    ? $q->whereNotNull('email_verified_at')
                    : $q->whereNull('email_verified_at');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            });
    }

    public function scopeKycUnverified($query)
    {
        return $query->where(function ($q) {
            $q->doesntHave('kycSubmission')
                ->orWhereHas('kycSubmission', function ($sub) {
                    $sub->where('status', '!=', KycStatus::APPROVED);
                });
        });
    }

    /**
     * Accessor for avatar field: returns a default if not set.
     */
    public function getAvatarAltAttribute(): string
    {
        return $this->avatar ?? '/general/static/default/user.png';
    }

    /**
     * A convenience accessor for displaying the user's full name.
     */
    public function getNameAttribute(): string
    {
        // Assuming `title()` is a custom global helper, or you have a better approach
        return title($this->first_name.' '.$this->last_name);
    }

    // ******************* Wallets relations & methods *******************//

    public function activeWallets(?string $role = null)
    {
        return $this->wallets()->with(['currency.roles'])->active($role)->get();
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function p2pPaymentAccounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class);
    }

    public function getDefaultWalletAttribute()
    {
        // Delegate to WalletService or keep your custom logic here
        return app(WalletService::class)->getDefaultWallet($this);
    }

    public function createWallet($currencyId)
    {
        return app(WalletService::class)->createWalletForCurrency($this, $currencyId);
    }

    public function availableCurrenciesForCreateWallet()
    {
        return Currency::whereNotIn('id', $this->wallets->pluck('currency_id'))
            ->get();
    }

    // ******************* Transactions & Referrals *******************//

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function mobileRecharges(): HasMany
    {
        return $this->hasMany(MobileRecharge::class);
    }

    public function phoneVerificationCodes(): HasMany
    {
        return $this->hasMany(PhoneVerificationCode::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->latest();
    }

    public function walletEarnStakes(): HasMany
    {
        return $this->hasMany(WalletEarnStake::class);
    }

    public function walletEarnRewards(): HasMany
    {
        return $this->hasMany(WalletEarnReward::class);
    }

    public function getReferralLinkAttribute(): string
    {
        return route('user.register', ['ref' => $this->referral_code]);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'user_id');
    }

    public function referredBy()
    {
        return $this->hasOne(Referral::class, 'referred_user_id');
    }

    public function indirectReferrals()
    {
        return $this->hasManyThrough(
            Referral::class,
            Referral::class,
            'parent_referral_id',
            'user_id',
            'id',
            'id'
        );
    }

    // ******************* Ranking & Roles *******************//

    public function rank()
    {
        return $this->belongsTo(UserRank::class, 'rank_id');
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    public function isMerchant(): bool
    {
        return $this->role === UserRole::MERCHANT;
    }

    public function isAgent(): bool
    {
        return $this->role === UserRole::AGENT;
    }

    /**
     * Each user can have one agent profile.
     */
    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function getRecentNotifications(): Collection
    {
        if (! $this->notificationDeliveryEnabled()) {
            return collect();
        }

        return $this->unreadNotifications()->latest()->get();
    }

    // ******************* KYC & Verification *******************//

    /**
     * Each user can only have one active KYC submission.
     */
    public function kycSubmission(): HasOne
    {
        return $this->hasOne(KycSubmission::class);
    }

    /**
     * Check if the user is KYC verified (approved).
     */
    public function isKycVerified(): bool
    {
        $submission = $this->kycSubmission;

        return $submission && $submission->status === KycStatus::APPROVED;
    }

    /**
     * Each user can have multiple tickets.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function merchants()
    {
        return $this->hasMany(Merchant::class);
    }

    /**
     * user activity log
     */
    public function loginActivities(): User|HasMany
    {
        return $this->hasMany(LoginActivity::class);
    }

    public function latestLoginActivity(): HasOne
    {
        return $this->hasOne(LoginActivity::class)->latestOfMany();
    }

    /**
     * Define the relationship between User and UserFeature
     */
    public function features(): User|HasMany
    {
        return $this->hasMany(UserFeature::class)->orderBy('sort_order', 'asc');
    }

    public function hasFeature(string $feature): bool
    {
        return $this->features()->where('feature', $feature)->where('status', true)->exists();
    }

    // app/Models/User.php
    public function virtualCards()
    {
        return $this->hasMany(VirtualCard::class);
    }
}
