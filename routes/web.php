<?php

use App\Http\Controllers\Api\ApiDocsController;
use App\Http\Controllers\Common\AppController;
use App\Http\Controllers\Common\FileController;
use App\Http\Controllers\Common\LocaleController;
use App\Http\Controllers\Common\SummernoteController;
use App\Http\Controllers\Frontend\AgentController;
use App\Http\Controllers\Frontend\AgentOperationController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CardholdersController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\DemoDisclosureController;
use App\Http\Controllers\Frontend\DepositController;
use App\Http\Controllers\Frontend\ExchangeMoneyController;
use App\Http\Controllers\Frontend\GiftCardController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\IPNController;
use App\Http\Controllers\Frontend\KycSubmissionController;
use App\Http\Controllers\Frontend\MerchantController;
use App\Http\Controllers\Frontend\MerchantPaymentReceiveController;
use App\Http\Controllers\Frontend\MobileRechargeController;
use App\Http\Controllers\Frontend\NotificationController;
use App\Http\Controllers\Frontend\P2P\AdvertiserController;
use App\Http\Controllers\Frontend\P2P\DisputeController;
use App\Http\Controllers\Frontend\P2P\OfferController;
use App\Http\Controllers\Frontend\P2P\OfferPromotionController;
use App\Http\Controllers\Frontend\P2P\OrderController;
use App\Http\Controllers\Frontend\P2P\PaymentAccountController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PaymentLinkCheckoutController;
use App\Http\Controllers\Frontend\PaymentLinkController;
use App\Http\Controllers\Frontend\PhoneVerificationController;
use App\Http\Controllers\Frontend\ReferralController;
use App\Http\Controllers\Frontend\RequestMoneyController;
use App\Http\Controllers\Frontend\SecurityController;
use App\Http\Controllers\Frontend\SendMoneyController;
use App\Http\Controllers\Frontend\SettingController;
use App\Http\Controllers\Frontend\SignupBonusController;
use App\Http\Controllers\Frontend\StatusController;
use App\Http\Controllers\Frontend\SubscriberController;
use App\Http\Controllers\Frontend\SubscriptionController;
use App\Http\Controllers\Frontend\TicketController;
use App\Http\Controllers\Frontend\TransactionController;
use App\Http\Controllers\Frontend\TwoFactorController;
use App\Http\Controllers\Frontend\UserRankController;
use App\Http\Controllers\Frontend\VirtualCardController;
use App\Http\Controllers\Frontend\VirtualCardRequestController;
use App\Http\Controllers\Frontend\VoucherController;
use App\Http\Controllers\Frontend\WalletController;
use App\Http\Controllers\Frontend\WalletEarnController;
use App\Http\Controllers\Frontend\WalletPinController;
use App\Http\Controllers\Frontend\WithdrawAccountController;
use App\Http\Controllers\Frontend\WithdrawController;
use App\Http\Controllers\Webhook\BitnobWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page Routes
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');

// Redirect /home to /
Route::redirect('/home', '/');

// Blog Routes
Route::prefix('blog')->as('blog.')->controller(BlogController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{slug}', 'details')->name('details');
});

// Contact Routes
Route::post('/contact-submit', [ContactController::class, 'submit'])->name('contact.submit');

// Subscribe
Route::post('/subscribe', SubscriberController::class)->name('subscribe.submit');

// Public Gift Card preview (recipient lands here from email link)
Route::get('/gift-card/{code}', [GiftCardController::class, 'preview'])->name('gift-card.preview');

/*
|--------------------------------------------------------------------------
| All Type User Routes Like Normal User, Merchant User
|--------------------------------------------------------------------------
*/
Route::prefix('user')->as('user.')->middleware(['auth', 'auth.session', 'account.status.check', 'verified', '2fa', 'block.ip'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/signup-bonus/acknowledge', [SignupBonusController::class, 'acknowledge'])->name('signup-bonus.acknowledge');

    // ========================== User Settings Routes =============================
    Route::prefix('settings')->as('settings.')->controller(SettingController::class)->group(function () {
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile-update', 'profileUpdate')->name('profile.update');
        Route::get('change-password', 'changePassword')->name('password.change');
        Route::post('password-update', 'passwordUpdate')->name('password.update');
        Route::get('verify-email', 'verifyEmail')->name('verify-email');
        Route::get('subscription-status', 'subscriptionStatus')->middleware('feature.enabled:subscription_system')->name('subscription.status');
        Route::get('wallet-earn-status', 'walletEarnStatus')->name('wallet-earn.status');
        Route::get('verify-phone', [PhoneVerificationController::class, 'show'])->name('phone.verify');
        Route::post('verify-phone/send', [PhoneVerificationController::class, 'send'])
            ->middleware('throttle:3,1')
            ->name('phone.send');
        Route::post('verify-phone', [PhoneVerificationController::class, 'verify'])
            ->middleware('throttle:10,1')
            ->name('phone.confirm');
        Route::post('verify-phone/disable', [PhoneVerificationController::class, 'disable'])
            ->middleware('throttle:6,1')
            ->name('phone.disable');
        Route::get('security', [SecurityController::class, 'index'])->name('security.index');
        Route::post('security/logout-other-sessions', [SecurityController::class, 'logoutOtherSessions'])
            ->middleware('throttle:6,1')
            ->name('security.logout-other-sessions');

        // Two-Factor Authentication
        Route::prefix('2fa')->as('2fa.')->controller(TwoFactorController::class)->group(function () {
            Route::get('setup', 'showSetupForm')->name('setup');
            Route::post('enable', 'enable2fa')->name('enable');
            Route::post('disable', 'disable2fa')->name('disable');
        });

        // Wallet PIN
        Route::controller(WalletPinController::class)->group(function () {
            Route::get('wallet-pin', 'form')->name('wallet-pin');
            Route::post('wallet-pin', 'update')->name('wallet-pin.update');
            Route::post('wallet-pin/reset', 'reset')->name('wallet-pin.reset');
            Route::get('wallet-pin/reset/{user}/confirm', 'confirmReset')
                ->name('wallet-pin.reset.confirm');
        });

        // KYC Verification
        Route::prefix('kyc')->as('kyc.')->controller(KycSubmissionController::class)->group(function () {
            Route::get('verify', 'kycVerify')->name('verify');
            Route::get('template/details/{id}', 'templateDetails')->name('template.details');

            Route::post('submit', 'kycSubmit')->name('submit');
        });
    });

    // ========================== Wallet Routes =============================
    Route::prefix('wallet')->as('wallet.')->controller(WalletController::class)->group(function () {
        Route::get('list', 'index')->name('index');
        Route::get('my-qr-code', 'myQrCode')->name('my-qr-code');
        Route::post('create', 'create')->name('create');
        Route::get('currency-info/{currency_id}', 'currencyInfo')->name('currency-info');
        Route::post('status', 'status')->name('status');

        // json response
        Route::get('supported-payment-methods/{wallet_id}', 'supportedPaymentMethods')->name('supported-payment-methods');
        Route::get('info/{role}/{wallet_id}', 'getWalletInfo')->name('info');
        Route::get('validate-recipient/{role}/{emailOrWalletId}', 'validateRecipient')->name('validate.recipient');
    });

    // ========================== Wallet Earn Routes =============================
    Route::prefix('wallet-earn')->as('wallet-earn.')->controller(WalletEarnController::class)->middleware(['feature.enabled:wallet_earn', 'prevent.duplicate'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('plans', 'plans')->name('plans');
        Route::get('my-stakes', 'stakes')->name('stakes');
        Route::post('stake', 'store')->name('store');
        Route::get('stakes/{stake}', 'show')->name('show');
    });

    // ========================== Mobile Recharge Routes =============================
    Route::prefix('mobile-recharge')->as('mobile-recharge.')->controller(MobileRechargeController::class)->middleware(['feature.enabled:mobile_recharge', 'prevent.duplicate'])->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
    });

    // ========================== Deposit Money Routes =============================
    Route::prefix('deposit')->as('deposit.')->controller(DepositController::class)->middleware(['feature.enabled:deposit_money', 'prevent.duplicate'])->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store')->middleware('feature:deposit');
        Route::get('credentials/{method_id}', 'credentials')->name('credentials');
        Route::get('history', 'history')->name('history');
    });

    // ========================== Transfer/Send Money Routes =============================
    Route::prefix('send-money')->as('send-money.')->controller(SendMoneyController::class)->middleware(['feature.enabled:send_money', 'prevent.duplicate'])->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store')->middleware('feature:send_money');
    });

    // ========================== Money Request Routes =============================
    Route::prefix('request-money')->as('request-money.')->controller(RequestMoneyController::class)->middleware(['feature.enabled:request_money', 'prevent.duplicate'])->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store')->middleware('feature:request_money');
    });

    // ========================== Exchange Money Routes =============================
    Route::prefix('exchange-money')->as('exchange-money.')->controller(ExchangeMoneyController::class)->middleware(['feature.enabled:exchange_money', 'prevent.duplicate'])->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store')->middleware(['feature:exchange_money']);
    });

    // ========================== Virtual Card Routes =============================
    Route::prefix('virtual-card')->as('virtual-card.')->middleware(['feature.enabled:virtual_card'])->group(function () {

        // Cardholders Management
        Route::resource('cardholders', CardholdersController::class)->names('cardholders');

        // Virtual Card Request
        Route::prefix('request')->as('request.')->controller(VirtualCardRequestController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::post('store', 'store')->name('store');
            Route::get('eligible-wallets', 'eligibleWallets')->name('eligible-wallets'); // JSON response for virtual card eligibility check
        });

        // My Virtual Card
        Route::controller(VirtualCardController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('card-details/{id}/{provider?}', 'cardDetails')->name('card-details');

            Route::get('topup/{card}', 'topup')->name('topup');
            Route::post('topup-store', 'topupStore')->name('topup-store');

            Route::get('withdraw/{card}', 'withdraw')->name('withdraw');
            Route::post('withdraw-store', 'withdrawStore')->name('withdraw-store');

            Route::post('{card}/freeze', 'freeze')->name('freeze');
            Route::post('{card}/unfreeze', 'unfreeze')->name('unfreeze');
            Route::post('{card}/limits', 'updateLimits')->name('limits.update');
            Route::post('{card}/controls', 'updateControls')->name('controls.update');
        });
    });

    // =========================== Voucher Routes =============================
    Route::prefix('voucher')->as('voucher.')->controller(VoucherController::class)->middleware(['feature.enabled:vouchers'])->group(function () {
        Route::get('my', 'myVouchers')->name('my');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::post('redeem', 'redeem')->name('redeem');
    });

    // =========================== Gift Card Routes =============================
    // Redeem is handled by a Bootstrap modal on the index page, posting
    // directly to user.gift-card.redeem — no standalone redeem screen.
    Route::prefix('gift-cards')->as('gift-card.')->controller(GiftCardController::class)->middleware(['feature.enabled:gift_cards'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store')->middleware('prevent.duplicate');
        Route::post('redeem', 'redeem')->name('redeem');
        Route::post('{giftCard}/cancel', 'cancel')->name('cancel');
    });

    // ========================== Withdraw Money Routes =============================
    Route::prefix('withdraw')->as('withdraw.')->controller(WithdrawController::class)->middleware(['feature.enabled:withdraw_money'])->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store')->middleware(['prevent.duplicate', 'feature:withdraw']);
        Route::get('credentials-fields/{method_id}', 'credentialsFields')->name('credentials.fields');
        Route::get('account-info/{id}', [WithdrawAccountController::class, 'accountInfo'])->name('account.info');
        Route::resource('account', WithdrawAccountController::class)->except(['show', 'destroy']);
    });

    // ========================== P2P Trading Routes =============================
    Route::prefix('p2p')->as('p2p.')->middleware(['p2p.enabled', 'feature.enabled:p2p_marketplace', 'p2p.country', 'prevent.duplicate', 'throttle:30,1'])->group(function () {
        // Offers
        Route::prefix('offers')->as('offers.')->controller(OfferController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('my', 'my')->name('my');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::get('{offer}/edit', 'edit')->name('edit');
            Route::match(['put', 'patch'], '{offer}', 'update')->name('update');
            Route::post('{offer}/toggle', 'toggle')->name('toggle');
            Route::delete('{offer}', 'destroy')->name('destroy');
            Route::get('{offer}', 'show')->name('show');
        });

        Route::prefix('offers/{offer}/promotion')->as('offers.promotion.')->controller(OfferPromotionController::class)->group(function () {
            Route::get('/', 'promote')->name('show');
            Route::post('quote', 'quote')->name('quote');
            Route::post('purchase', 'purchase')->name('purchase');
        });

        // Advertisers
        Route::prefix('advertisers')->as('advertisers.')->controller(AdvertiserController::class)->group(function () {
            Route::get('{user}', 'show')->name('show');
        });

        Route::prefix('payment-accounts')->as('payment-accounts.')->controller(PaymentAccountController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('{paymentAccount}', 'update')->name('update');
            Route::delete('{paymentAccount}', 'destroy')->name('destroy');
        });

        // Orders
        Route::prefix('orders')->as('orders.')->controller(OrderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store'); // create from offer
            Route::get('{order}', 'show')->name('show');
            Route::get('{order}/status', 'status')->name('status');
            Route::post('{order}/paid', 'markPaid')->name('paid');
            Route::post('{order}/release', 'release')->name('release');
            Route::post('{order}/cancel', 'cancel')->name('cancel');
            Route::post('{order}/feedback', 'feedback')->name('feedback');
        });

        // Disputes
        Route::post('orders/{order}/dispute', [DisputeController::class, 'store'])->name('orders.dispute');
    });

    // ========================== Support Management Routes =============================
    Route::prefix('support-ticket')->as('support-ticket.')->controller(TicketController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('show/{ticket}', 'show')->name('show');
        Route::post('reply/{ticket}', 'reply')->name('reply');
        Route::get('close/{ticket}', 'close')->name('close');
    });

    // ========================== Transaction Routes =============================
    Route::prefix('transaction')->as('transaction.')->controller(TransactionController::class)->group(function () {
        Route::get('index', [TransactionController::class, 'index'])->name('index');
        Route::get('download-pdf/{trx_id}', [TransactionController::class, 'downloadPdf'])->name('download-pdf');
        Route::post('action', [TransactionController::class, 'handleAction'])->name('action');
    });

    // ========================== Referral Routes =============================
    Route::prefix('referral')->as('referral.')->controller(ReferralController::class)->middleware(['feature.enabled:referral_program'])->group(function () {
        Route::get('index', 'index')->name('index');
    });

    // ========================== User Rank Routes =============================
    Route::get('rank-showcase', [UserRankController::class, 'showcase'])
        ->middleware('feature.enabled:user_ranks')
        ->name('rank.showcase');

    // ========================== Payment Link Routes =============================
    Route::prefix('payment-links')->as('payment-links.')->controller(PaymentLinkController::class)->middleware(['feature.enabled:payment_link', 'prevent.duplicate'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{paymentLink}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '{paymentLink}', 'update')->name('update');
        Route::post('{paymentLink}/toggle', 'toggle')->name('toggle');
        Route::delete('{paymentLink}', 'destroy')->name('destroy');
    });

    // ========================== Subscription Routes =============================
    Route::prefix('subscription')->as('subscription.')->controller(SubscriptionController::class)->middleware(['feature.enabled:subscription_system'])->group(function () {
        Route::get('plans', 'plans')->name('plans');
        Route::get('current', 'current')->name('current');
        Route::get('history', 'history')->name('history');
        Route::get('checkout/{plan:slug}/{cycle?}', 'checkout')->name('checkout');
        Route::post('subscribe', 'subscribe')->name('subscribe');
        Route::post('{subscription}/cancel', 'cancel')->name('cancel');
        Route::post('{subscription}/renew', 'renew')->name('renew');
    });

    // ========================== Merchant Routes =============================
    Route::middleware('can:merchant')->group(function () {
        Route::resource('merchant', MerchantController::class)->except(['show', 'destroy']);
        Route::get('merchant/{merchant}/config', [MerchantController::class, 'merchantConfig'])->name('merchant.config');
        Route::put('merchant/{merchant}/payment-methods', [MerchantController::class, 'updatePaymentMethods'])->name('merchant.payment-methods.update');
        Route::post('merchant/switch-environment', [MerchantController::class, 'switchEnvironment'])->name('merchant.switch-environment');
        // The legacy merchant QR / payment-link flow has been merged into
        // the unified Payment Link module (see user.payment-links.*).
        // Merchants now create per-shop payment links from there.
    });

    // ========================== Agent QR Cash-Out Routes =============================
    Route::middleware('feature.enabled:agent_program')->group(function () {
        Route::get('agent/qr/{token}/cash-out', [AgentOperationController::class, 'showQrCashOut'])->name('agent.qr.cash-out');
        Route::post('agent/qr/{token}/cash-out', [AgentOperationController::class, 'storeQrCashOut'])->name('agent.qr.cash-out.store');
    });

    // ========================== Agent Routes =============================
    Route::middleware(['can:agent', 'feature.enabled:agent_program'])->group(function () {
        Route::post('agent/cash-in', [AgentOperationController::class, 'cashIn'])->name('agent.cash-in');
        Route::post('agent/cash-out/otp', [AgentOperationController::class, 'sendCashOutOtp'])->name('agent.cash-out.otp');
        Route::post('agent/cash-out', [AgentOperationController::class, 'cashOut'])->name('agent.cash-out');
        Route::post('agent/operations/{operation}/mark-cash-paid', [AgentOperationController::class, 'markCashPaid'])->name('agent.cash-out.mark-paid');
        Route::post('agent/{agent}/regenerate-qr', [AgentController::class, 'regenerateQr'])->name('agent.regenerate-qr');
        Route::resource('agent', AgentController::class)->except(['show', 'destroy']);
    });

    // Notification Management Routes
    Route::controller(NotificationController::class)->prefix('notifications')->as('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('recent', 'recent')->name('recent');
        Route::put('preferences', 'updatePreference')->name('preferences.update');

        Route::get('{id}/read', 'markAsRead')->name('markAsRead');
        Route::get('read-all', 'markAllAsRead')->name('read-all');
    });
});

/*
|--------------------------------------------------------------------------
| Instant Payment Notification (IPN)
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/ipn/{gateway}', [IPNController::class, 'handleIPN'])->name('ipn.handle');

/*
|--------------------------------------------------------------------------
| Bitnob Webhook
|--------------------------------------------------------------------------
| Bitnob signs each payload with HMAC-SHA512 in `x-bitnob-signature`.
| The route is CSRF-exempt (declared in bootstrap/app.php) so external
| POSTs aren't blocked.
*/
Route::post('/webhooks/bitnob', BitnobWebhookController::class)
    ->middleware('bitnob.signature')
    ->name('webhooks.bitnob');

// Payment Status Routes
Route::prefix('status')->as('status.')->controller(StatusController::class)->group(function () {
    Route::match(['get', 'post'], 'success', 'success')->name('success');
    Route::match(['get', 'post'], 'cancel', 'cancel')->name('cancel');
    Route::match(['get', 'post'], 'pending', 'pending')->name('pending');
    Route::match(['get', 'post'], 'callback', 'callback')->name('callback');
});

// ========================== Merchant Payment Routes =============================
Route::prefix('payment')->as('payment.')->controller(MerchantPaymentReceiveController::class)->group(function () {
    Route::get('checkout', 'paymentCheckoutSigned')->name('checkout')->middleware('signed');
    Route::get('pay/{merchant}/{token}', 'paymentCheckoutPublic')->name('pay');
    Route::post('process', 'processPayment')->name('process');
    Route::get('wallet-pay/{token}', 'walletPayment')->name('wallet.pay');
    Route::post('complete', 'completePayment')->name('complete')->middleware(['feature.enabled:merchant_payment']);
    Route::match(['get', 'post'], 'with-account', 'payWithAccount')->name('with.account')->middleware(['auth', 'feature.enabled:merchant_payment']);
});

// ========================== Public Payment Link Routes =============================
Route::prefix('payment-link')->as('payment-link.')->controller(PaymentLinkCheckoutController::class)->group(function () {
    Route::get('{token}', 'show')->name('show');
    // The public pay endpoint accepts Wallet PIN payments and matching active
    // gateway payments. A coarse per-IP rate limit on top of the per-(IP,wallet)
    // RateLimiter inside the controller blocks bulk wallet credential probing.
    Route::post('{token}/pay', 'pay')->name('pay')->middleware(['feature.enabled:payment_link', 'throttle:10,1']);
    Route::get('{token}/success', 'success')->name('success');
});

/*
|--------------------------------------------------------------------------
| Common Routes
|--------------------------------------------------------------------------
*/
Route::get('locale-set/{locale}', [LocaleController::class, 'setLocale'])->name('locale-set');
// Get currency rate with JSON response
Route::get('currency-rate/{fromCurrency}/{toCurrency}', [AppController::class, 'getCurrencyRate'])->name('get-currency-rate');
// Download File
Route::get('/file/download/{filePath}', [FileController::class, 'download'])->where('filePath', '.*')->name('file.download');

Route::prefix('summernote')->as('summernote.')->controller(SummernoteController::class)->group(function () {
    Route::post('image-upload', 'imageUpload')->name('image-upload');
    Route::post('image-delete', 'imageDelete')->name('image-delete');
});

/*
|--------------------------------------------------------------------------
| Merchant Api Documentation
|--------------------------------------------------------------------------
*/
Route::prefix('api-docs')->as('api-docs.')->group(function () {
    Route::get('/', [ApiDocsController::class, 'index'])->name('index');
});

/*
|--------------------------------------------------------------------------
| Public Software-Demo Disclosure Page
|--------------------------------------------------------------------------
|
| Always-available authoritative landing page describing the nature of
| this installation. Referenced by the demo banner, footer attribution,
| and meta-tag disclosures so automated scanners and human reviewers
| can resolve a single canonical URL.
*/

Route::get('demo-disclosure', DemoDisclosureController::class)->name('demo.disclosure');

/*
|--------------------------------------------------------------------------
| LAST: CMS Dynamic Page (Slug-Based)
|--------------------------------------------------------------------------
*/

Route::get('{slug}', PageController::class)
    ->where('slug', '^(?!admin|user|merchant|api|dashboard|payment|login|register|signin|signup|demo-disclosure).*$')
    ->name('page.view');
