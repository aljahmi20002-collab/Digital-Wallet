<?php

use App\Http\Controllers\Backend\ActivityController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\AdminNoticeController;
use App\Http\Controllers\Backend\AgentCommissionRuleController;
use App\Http\Controllers\Backend\AgentController;
use App\Http\Controllers\Backend\AppController;
use App\Http\Controllers\Backend\BackgroundTaskController;
use App\Http\Controllers\Backend\BlogCategoryController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\CardholdersController;
use App\Http\Controllers\Backend\CurrencyController;
use App\Http\Controllers\Backend\CustomLandingController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DepositController;
use App\Http\Controllers\Backend\DepositMethodController;
use App\Http\Controllers\Backend\FeatureManagementController;
use App\Http\Controllers\Backend\FooterItemController;
use App\Http\Controllers\Backend\FooterSectionController;
use App\Http\Controllers\Backend\GiftCardController as AdminGiftCardController;
use App\Http\Controllers\Backend\GiftCardTemplateController;
use App\Http\Controllers\Backend\KycController;
use App\Http\Controllers\Backend\KycTemplateController;
use App\Http\Controllers\Backend\LanguageController;
use App\Http\Controllers\Backend\MerchantController;
use App\Http\Controllers\Backend\MobileRechargeController;
use App\Http\Controllers\Backend\MobileRechargeProviderController;
use App\Http\Controllers\Backend\NavigationController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\NotificationTemplateController;
use App\Http\Controllers\Backend\P2P\P2PAdminController;
use App\Http\Controllers\Backend\P2P\P2PAdvertiserController;
use App\Http\Controllers\Backend\P2P\P2PDisputeController;
use App\Http\Controllers\Backend\P2P\P2POfferPromotionController;
use App\Http\Controllers\Backend\P2P\P2PPaymentMethodController;
use App\Http\Controllers\Backend\P2P\P2PPromotionPackageController;
use App\Http\Controllers\Backend\P2P\P2PSettingController;
use App\Http\Controllers\Backend\PageComponentController;
use App\Http\Controllers\Backend\PageComponentRepeatedContentController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\PaymentGatewayController;
use App\Http\Controllers\Backend\PaymentLinkController;
use App\Http\Controllers\Backend\PluginController;
use App\Http\Controllers\Backend\ProjectUpdaterController;
use App\Http\Controllers\Backend\QueueManagementController;
use App\Http\Controllers\Backend\ReferralController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SiteSeoController;
use App\Http\Controllers\Backend\SocialController;
use App\Http\Controllers\Backend\StaffController;
use App\Http\Controllers\Backend\SubscriberController;
use App\Http\Controllers\Backend\Subscription\AdminUserSubscriptionController;
use App\Http\Controllers\Backend\Subscription\SubscriptionPlanController;
use App\Http\Controllers\Backend\SupportCategoryController;
use App\Http\Controllers\Backend\ThemeManagerController;
use App\Http\Controllers\Backend\TicketController;
use App\Http\Controllers\Backend\TransactionController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\UserManageController;
use App\Http\Controllers\Backend\UserRankController;
use App\Http\Controllers\Backend\VirtualCardController;
use App\Http\Controllers\Backend\VirtualCardFeeSettingController;
use App\Http\Controllers\Backend\WalletEarnPlanController;
use App\Http\Controllers\Backend\WalletEarnStakeController;
use App\Http\Controllers\Backend\WithdrawController;
use App\Http\Controllers\Backend\WithdrawMethodController;
use App\Http\Controllers\Backend\WithdrawScheduleController;
use Illuminate\Support\Facades\Route;

$adminPrefix = trim((string) setting('admin_prefix', 'admin')) ?: 'admin';

/*
|--------------------------------------------------------------------------
| Admin/Backend Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the bootstrap/app within a group which
| contains the "web,admin" middleware group. Now create something great!
|
*/

Route::prefix($adminPrefix)->as('admin.')->group(function () {

    // ========================== 🌟 Dashboard =============================
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ========================== 🎁 Gift Cards =============================
    Route::prefix('gift-card-templates')->as('gift-card-templates.')->controller(GiftCardTemplateController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:gift-card-template-list');
        Route::post('/', 'store')->name('store')->middleware('can:gift-card-template-manage');
        Route::post('position-update', 'positionUpdate')->name('position-update')->middleware('can:gift-card-template-manage');
        Route::put('{template}', 'update')->name('update')->middleware('can:gift-card-template-manage');
        Route::delete('{template}', 'destroy')->name('destroy')->middleware('can:gift-card-template-manage');
    });
    Route::prefix('gift-cards')->as('gift-cards.')->controller(AdminGiftCardController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:gift-card-list');
        Route::post('{giftCard}/cancel', 'cancel')->name('cancel')->middleware('can:gift-card-manage');
    });

    // ========================== 👥 User Management ==========================
    Route::prefix('user')->as('user.')->controller(UserManageController::class)->group(function () {

        // 🔹 User Actions (GET)
        Route::get('manage/{username}/{param?}', 'manageUser')->name('manage');
        Route::get('login/{id}', 'loginAsUser')->name('login');
        Route::get('mail-send/all', 'mailSendAll')->name('mail-send.all');

        // 🔹 User Updates (POST)
        Route::post('feature-status/update', 'updateFeatureStatus')->name('feature-status.update');
        Route::post('update-balance', 'updateBalance')->name('update-balance');
        Route::post('status-update/{id}', 'statusUpdate')->name('status-update');
        Route::post('password-update/{id}', 'passwordUpdate')->name('password-update');
        Route::post('mail-send', 'mailSend')->name('mail-send');

        // 🔹 User Info Update (PUT)
        Route::put('update-info/{id}', 'infoUpdate')->name('update-info');
    });

    // 🔹 User Listings (GET)
    Route::prefix('user')->as('user.')->controller(UserController::class)->group(function () {
        Route::get('active', 'activeUser')->name('active');
        Route::get('suspended', 'suspendedUser')->name('suspended');
        Route::get('unverified', 'unverifiedUser')->name('unverified');
        Route::get('kyc-unverified', 'kycUnverifiedUser')->name('kyc-unverified');
        Route::get('{id}/transaction-stats', 'transactionStats')->name('transaction-stats');
        Route::post('{id}/convert-to-merchant', 'convertToMerchant')->name('convert-to-merchant');
        Route::post('{id}/convert-to-agent', 'convertToAgent')->name('convert-to-agent');
    });
    // 🔹 User Resources
    Route::resource('user', UserController::class)->except(['show', 'create', 'edit']);

    // =============================== 🏪 Merchant Management  =================================
    Route::prefix('merchant')->as('merchant.')->controller(MerchantController::class)->group(function () {
        Route::get('pending', 'pendingMerchant')->name('pending');
        Route::get('approved', 'approvedMerchant')->name('approved');
        Route::get('rejected', 'rejectedMerchant')->name('rejected');
        Route::post('request-action', 'merchantAction')->name('request-action');
    });
    // 🔹 Merchant Resources
    Route::resource('merchant', MerchantController::class);

    // =============================== 🧑‍💼 Agent Management  =================================
    Route::prefix('agent')->as('agent.')->controller(AgentController::class)->group(function () {
        Route::get('pending', 'pendingAgent')->name('pending');
        Route::get('approved', 'approvedAgent')->name('approved');
        Route::get('rejected', 'rejectedAgent')->name('rejected');
        Route::post('request-action', 'agentAction')->name('request-action');
    });
    Route::resource('agent/commission-rules', AgentCommissionRuleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('agent.commission-rules');
    // 🔹 Agent Resources
    Route::resource('agent', AgentController::class)->only(['index']);

    // ================================ 🔑 KYC Management   =================================
    Route::prefix('kyc')->as('kyc.')->group(function () {
        Route::controller(KycController::class)->group(function () {
            Route::get('pending', 'pending')->name('pending');
            Route::get('index', 'index')->name('index');
            Route::post('action', 'requestAction')->name('request-action');
        });
        Route::resource('template', KycTemplateController::class)->except(['show', 'create']);
    });

    // ================================ 🚩 Feature Management  =================================
    Route::prefix('features')->as('features.')->controller(FeatureManagementController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{feature}/edit', 'edit')->name('edit');
        Route::put('{feature}', 'update')->name('update');
        Route::patch('{feature}/toggle', 'toggle')->name('toggle');
    });

    // ================================ 🔍 User/Merchant Activity History  =================================
    Route::get('activity-log', [ActivityController::class, 'index'])->name('activity-log');

    // ================================ 🔐 Admin Profile  =================================
    Route::prefix('profile')->as('profile.')->controller(AdminController::class)->group(function () {
        Route::get('profile', 'profile')->name('view');
        Route::post('info-update', 'updateInfo')->name('info.update');
        Route::post('password-update', 'updatePassword')->name('password.update');

        // Two-Factor Authentication
        Route::prefix('2fa')->as('2fa.')->group(function () {
            Route::post('enable', 'enable2fa')->name('enable');
            Route::post('disable', 'disable2fa')->name('disable');
        });
    });

    // ======================== 👨‍💼 Staff Management  ==============================
    Route::resource('staff', StaffController::class)->except(['show', 'create', 'destroy']);
    Route::resource('role', RoleController::class);

    // ======================== 💱 Currency Management  ==============================
    Route::get('currency/rates', [CurrencyController::class, 'rates'])->name('currency.rates');
    Route::resource('currency', CurrencyController::class);

    // ================================== 💳 Payment Gateway ===============================
    Route::prefix('payment')->as('payment.')->group(function () {
        Route::post('gateway/{gateway}/test', [PaymentGatewayController::class, 'test'])->name('gateway.test');
        Route::resource('gateway', PaymentGatewayController::class)->only(['index', 'edit', 'update']);
        Route::get('gateway-currency/{gateway_id}', [PaymentGatewayController::class, 'gatewayCurrency'])->name('gateway-currency');
    });

    // ======================== 💳 Virtual Card Management  ===============================
    Route::prefix('virtual-card')->name('virtual-card.')->controller(VirtualCardController::class)->group(function () {
        // Card Requests
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('awaiting', 'requestAwaiting')->name('awaiting');
            Route::get('all', 'requestAll')->name('all');
            Route::post('{uuid}/review', 'review')->name('review');
        });

        // Cardholder management routes
        Route::prefix('cardholders')->name('cardholders.')->controller(CardholdersController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/action', 'action')->name('action');
            // Pre-index a cardholder with the Bitnob Visa BIN pool —
            // call this once per cardholder before issuance to avoid
            // the "User with this email is not indexed for visa" error.
            Route::post('/{id}/bitnob-verify', 'bitnobVerify')->name('bitnob-verify');
        });

        // Card Management
        Route::get('list', 'virtualCardList')->name('list');
        Route::post('update-status', 'statusUpdate')->name('update-status');
        // Re-fetch a card's live state from its gateway and persist
        // any changes (status / last4 / expiry). Provider-agnostic.
        Route::post('cards/{card}/refresh', 'refreshCard')->name('cards.refresh');

        // Provider Configuration — multi-gateway management hub.
        Route::prefix('provider')->name('provider.')->group(function () {
            Route::get('/', 'provider')->name('index');
            Route::get('manage/{id}', 'providerManage')->name('manage');
            Route::put('update/{provider}', 'providerUpdate')->name('update');
            // Per-provider drill-down: capability matrix, recent cards,
            // recent requests, diagnostic actions.
            Route::get('show/{id}', 'providerShow')->name('show');
            // JSON endpoint that runs the provider's auth probe so the
            // admin UI can show "credentials OK / failed" inline.
            Route::post('test-connection/{id}', 'providerTestConnection')->name('test-connection');
        });

        // Virtual Card Settings
        Route::resource('fee-settings', VirtualCardFeeSettingController::class)
            ->names('fee-settings');
    });

    // ======================== Wallet Earn Management ===============================
    Route::prefix('wallet-earn')->as('wallet-earn.')->middleware('admin.feature:wallet_earn')->group(function () {
        Route::get('/', [WalletEarnStakeController::class, 'index'])->name('index');
        Route::get('stakes/{stake}', [WalletEarnStakeController::class, 'show'])->name('stakes.show');
        Route::post('stakes/{stake}/approve', [WalletEarnStakeController::class, 'approve'])->name('stakes.approve');
        Route::post('stakes/{stake}/reject', [WalletEarnStakeController::class, 'reject'])->name('stakes.reject');
        Route::post('stakes/{stake}/cancel', [WalletEarnStakeController::class, 'cancel'])->name('stakes.cancel');
        Route::post('stakes/{stake}/complete', [WalletEarnStakeController::class, 'complete'])->name('stakes.complete');
        Route::post('plans/position-update', [WalletEarnPlanController::class, 'positionUpdate'])->name('plans.position-update');

        Route::resource('plans', WalletEarnPlanController::class)
            ->except('show')
            ->parameters(['plans' => 'plan']);
    });

    // ======================== 💰 Deposit Management  ===============================
    Route::prefix('deposit')->as('deposit.')->group(function () {
        Route::controller(DepositController::class)->group(function () {
            Route::get('manual-request', 'manualRequest')->name('manual-request');
            Route::get('history', 'history')->name('history');
            Route::post('request-action', 'requestAction')->name('request-action');
        });
        Route::resource('method', DepositMethodController::class)->except('show');
    });

    // ======================== 🏦 Withdraw Management   ===============================
    Route::prefix('withdraw')->as('withdraw.')->middleware('admin.feature:withdraw_money')->group(function () {
        Route::controller(WithdrawController::class)->group(function () {
            Route::get('manual-request', 'manualRequest')->name('manual-request');
            Route::get('history', 'history')->name('history');
            Route::post('request-action', 'requestAction')->name('request-action');
        });
        Route::resource('method', WithdrawMethodController::class)->except('show');
        Route::controller(WithdrawScheduleController::class)->group(function () {
            Route::get('schedule', 'index')->name('schedule');
            Route::post('schedule-update', 'update')->name('schedule.update');
        });
    });

    // ======================= 🔁 P2P Trading (Admin) =======================
    Route::prefix('p2p')->as('p2p.')->middleware('admin.feature:p2p_marketplace')->group(function () {
        Route::get('/', [P2PAdminController::class, 'index'])->name('index');

        Route::prefix('settings')->as('settings.')->controller(P2PSettingController::class)->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::put('/', 'update')->name('update');
        });

        Route::resource('methods', P2PPaymentMethodController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

        // Advertisers
        Route::prefix('advertisers')->as('advertisers.')->controller(P2PAdvertiserController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{user}', 'show')->name('show');
            Route::post('{user}/suspend', 'suspend')->name('suspend');
            Route::post('{user}/reactivate', 'reactivate')->name('reactivate');
        });

        // Disputes
        Route::prefix('disputes')->as('disputes.')->controller(P2PDisputeController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('history', 'history')->name('history');
            Route::get('{dispute}', 'show')->name('show');
            Route::post('{dispute}/resolve-release', 'resolveRelease')->name('resolve-release');
            Route::post('{dispute}/resolve-refund', 'resolveRefund')->name('resolve-refund');
        });

        Route::post('promotion-packages/position-update', [P2PPromotionPackageController::class, 'positionUpdate'])
            ->name('promotion-packages.position-update');

        Route::resource('promotion-packages', P2PPromotionPackageController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::controller(P2POfferPromotionController::class)->group(function () {
            Route::get('promotions', 'index')->name('promotions.index');
            Route::get('promotion-purchases', 'redirectLegacyPurchases')->name('promotion-purchases.index');
        });
    });

    // ======================== 🏆 Referral Management   ===============================
    Route::prefix('referral')->as('referral.')->group(function () {
        Route::get('index', [ReferralController::class, 'index'])->name('index');
        Route::post('store', [ReferralController::class, 'store'])->name('store');
        Route::get('edit/{id}', [ReferralController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [ReferralController::class, 'update'])->name('update');
        Route::get('status-update/{type}/{status}', [ReferralController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [ReferralController::class, 'destroy'])->name('delete');
        Route::get('card-content', [ReferralController::class, 'cardContent'])->name('card.content');
        Route::post('content-update', [ReferralController::class, 'contentUpdate'])->name('content.update');
    });

    // ======================== User Ranking Management   ===============================
    Route::resource('ranking', UserRankController::class)
        ->except(['create', 'show', 'destroy'])
        ->middleware('admin.feature:user_ranks');

    // ======================== 🔄 Transaction Management  ===============================
    Route::get('transaction', [TransactionController::class, 'index'])->name('transaction');

    // ======================== ⚙️ Site Management  ==============================
    Route::prefix('settings')->as('settings.')->group(function () {
        Route::resource('site', SettingController::class)->only(['index', 'update']);
        Route::post('plugin/{plugin}/test', [PluginController::class, 'test'])->name('plugin.test');
        Route::resource('plugin', PluginController::class)->only(['index', 'edit', 'update']);
        Route::get('{plugin_type}', [PluginController::class, 'pluginType'])->name('plugin_type');
    });

    // ======================== 🎫 Support Ticket  ==============================
    Route::prefix('support-ticket')->as('support-ticket.')->controller(TicketController::class)->group(function () {
        Route::resource('category', SupportCategoryController::class)->except(['show', 'create']);
        Route::get('pending', 'pendingTicket')->name('new');
        Route::get('inprogress', 'inprogress')->name('inprogress');
        Route::get('close', 'closeTicket')->name('close');
        Route::get('history', 'history')->name('history');
        Route::get('show/{ticket}', 'ticketShow')->name('show');
        Route::post('reply/{ticket}', 'ticketReplyStore')->name('reply');
        Route::put('status-update/{ticket_id}', 'statusUpdate')->name('status-update');
    });

    //  ️️======================== 🔔 Notification Management  ==============================
    Route::prefix('notifications')->name('notifications.')->group(function () {

        // 🔹 Notification management routes (prefix: notification)
        Route::controller(NotificationController::class)->group(function () {
            // 🔹 Admin-triggered user notification
            Route::get('to-users', 'notifyUsers')->name('notifyToUser');
            Route::post('to-users/send', 'sendNotification')->name('notifyToUser.send');

            // 🔹 Display notifications
            Route::get('/', 'index')->name('index');
            Route::get('/recent', 'recent')->name('recent');

            // 🔹 State-changing actions (use PATCH)
            Route::get('/{notification}/read', 'markAsRead')->name('markAsRead');
            Route::get('/read-all', 'markAllAsRead')->name('markAllAsRead');
        });

        // 🔹 Template management routes (prefix: template)
        Route::prefix('template')->name('template.')->controller(NotificationTemplateController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{template}/edit', 'edit')->name('edit');
            Route::put('{template}/channel/{channel}', 'updateChannel')->name('update');
        });

    });

    // ======================= 🌎 Language Management =======================
    Route::prefix('language')->name('language.')->controller(LanguageController::class)->group(function () {
        Route::get('translate/{code}', 'translate')->name('translate');
        Route::post('translate-update', 'translatedUpdate')->name('translate-update');
        Route::get('sync-missing-keys', 'syncMissingKeys')->name('sync-missing-keys');
    });
    // 🔹 Resource Language CRUD
    Route::resource('language', LanguageController::class);

    // ======================= 🎉 Custom Landing Page =======================
    Route::prefix('custom-landing')->name('custom-landing.')->controller(CustomLandingController::class)->group(function () {
        Route::get('guide', 'guide')->name('guide');
        Route::post('{custom_landing}/activate', 'activate')->name('activate');
        Route::get('{custom_landing}/manage-html', 'manageHtml')->name('manage-html');
        Route::post('{custom_landing}/manage-html-update', 'manageHtmlUpdate')->name('manage-html-update')->withoutMiddleware('XSS');
    });
    Route::resource('custom-landing', CustomLandingController::class);

    // ======================= 🏷️ Navigation Management =======================
    Route::prefix('navigation')->as('navigation.')->controller(NavigationController::class)->group(function () {
        Route::resource('site', NavigationController::class)->except(['create', 'show']);
        Route::post('position-update', 'positionUpdate')->name('position-update');
    });

    // ======================= 🎨 Theme Manager =======================
    Route::prefix('theme-manager')->as('theme-manager.')->controller(ThemeManagerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('activate/{theme}', 'activate')->name('activate');
    });

    // ======================= 📄 Page Management =======================
    Route::prefix('page')->as('page.')->group(function () {
        Route::resource('site', PageController::class)->except('show');
        Route::resource('component', PageComponentController::class)->except('show')->withoutMiddleware('XSS');
        Route::resource('component-repeated-content', PageComponentRepeatedContentController::class)->only(['edit', 'store', 'update', 'destroy']);

        // 🔹 Page Footer
        Route::prefix('footer')->as('footer.')->group(function () {

            // Footer Section Routes
            Route::resource('section', FooterSectionController::class)->except(['show', 'create']);
            Route::post('section/position-update', [FooterSectionController::class, 'positionUpdate'])->name('section.position-update');

            // Footer Item Routes
            Route::resource('item', FooterItemController::class)->except(['show', 'create']);
            Route::post('item/position-update', [FooterItemController::class, 'positionUpdate'])->name('item.position-update');

        });
    });

    // ======================= 📰 Blog Management =======================
    Route::prefix('blog')->as('blog.')->group(function () {
        Route::resource('post', BlogController::class)->withoutMiddleware('XSS');
        Route::resource('category', BlogCategoryController::class);
    });

    // ======================== 📱 Social Management ========================
    Route::resource('social', SocialController::class)->except(['create', 'show']);

    // ======================= 🔍 Site SEO Management =======================
    Route::resource('site-seo', SiteSeoController::class)->except(['show']);

    // ======================= 📧 Subscriber Management =======================
    Route::prefix('subscriber')->as('subscriber.')->controller(SubscriberController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('send-mail', 'sendMail')->name('send-mail');
        Route::delete('delete/{id}', 'deleteSubscriber')->name('delete');
    });

    // 🔻 Dismissible dashboard-notice endpoint. Whitelisted in the
    //    controller — only known notice keys are accepted.
    Route::post('notices/{key}/dismiss', [AdminNoticeController::class, 'dismiss'])
        ->where('key', '[a-z0-9\-]+')
        ->name('notice.dismiss');

    // ======================= ⚙️ Background Task Management =======================
    Route::prefix('background-tasks')->as('background-tasks.')->group(function () {
        Route::controller(BackgroundTaskController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('run', 'run')->name('run');
            Route::get('logs', 'logs')->name('logs');
            Route::get('scheduler', 'scheduler')->name('scheduler');
        });
    });

    Route::prefix('queue')->as('queue.')->controller(QueueManagementController::class)->group(function () {
        Route::get('failed', 'failed')->name('failed');
        Route::post('retry/{id}', 'retry')->name('retry');
        Route::post('retry-all', 'retryAll')->name('retry-all');
        Route::delete('forget/{id}', 'forget')->name('forget');
        Route::post('flush', 'flush')->name('flush');
    });

    // ======================= 🔗 Payment Link Management =======================
    Route::prefix('payment-links')->as('payment-links.')->controller(PaymentLinkController::class)
        ->middleware('admin.feature:payment_link')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{paymentLink}', 'show')->name('show');
            Route::post('{paymentLink}/toggle-status', 'toggleStatus')->name('toggle-status');
            Route::delete('{paymentLink}', 'destroy')->name('destroy');
        });

    // ======================= Mobile Recharge Management =======================
    Route::prefix('mobile-recharge')->as('mobile-recharge.')
        ->middleware('admin.feature:mobile_recharge')
        ->group(function () {
            Route::prefix('providers')->as('providers.')->controller(MobileRechargeProviderController::class)->group(function () {
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{provider}/edit', 'edit')->name('edit');
                Route::put('{provider}', 'update')->name('update');
                Route::delete('{provider}', 'destroy')->name('destroy');
            });

            Route::controller(MobileRechargeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('{mobileRecharge}', 'show')->name('show');
            });
        });

    // ======================= 💳 Subscription Management =======================
    Route::prefix('subscription')->as('subscription.')->group(function () {
        Route::resource('plans', SubscriptionPlanController::class)->except(['show']);
        Route::post('plans/{plan}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])->name('plans.toggle-status');

        Route::prefix('user-subscriptions')->as('user-subscriptions.')->controller(AdminUserSubscriptionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{subscription}', 'show')->name('show');
            Route::post('{subscription}/activate', 'activate')->name('activate');
            Route::post('{subscription}/cancel', 'cancel')->name('cancel');
        });

        Route::get('transactions', [AdminUserSubscriptionController::class, 'transactions'])->name('transactions');
    });

    // ======================= 🚀 Application Tools =======================
    Route::prefix('app')->as('app.')->controller(AppController::class)->group(function () {
        Route::get('/', 'appInfo')->name('info');

        // Menu search functionality
        Route::get('menu-search', 'getMenusForSearch')->name('menu-search');
        Route::get('/control-panel', 'controlPanel')->name('control-panel');

        Route::post('/smtp-connection-check', 'smtpConnectionCheck')->name('smtp-connection-check');

        Route::get('style-manager', 'styleManager')->name('style-manager');
        Route::post('style-manager', 'styleManagerUpdate')->name('style-manager-update');

        Route::get('/optimize', 'optimize')->name('optimize');
        Route::get('/clear-cache', 'clearCache')->name('clear-cache');

    });

    Route::prefix('app/updater')->as('app.updater.')->controller(ProjectUpdaterController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('activate', 'activate')->name('activate');
        Route::post('check', 'check')->name('check');
        Route::post('backup/download', 'backupDownload')->name('backup.download');
        Route::post('install/{update}', 'install')->name('install');
    });

});
