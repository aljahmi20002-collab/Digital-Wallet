<div class="d-lg-none mb-3">
    @php
        $depositMenuVisible = $featureManager->isVisible('deposit_money');
        $withdrawMenuVisible = $featureManager->isVisible('withdraw_money');
        $sendMoneyMenuVisible = $featureManager->isVisible('send_money');
        $requestMoneyMenuVisible = $featureManager->isVisible('request_money');
        $exchangeMoneyMenuVisible = $featureManager->isVisible('exchange_money');
        $p2pMenuVisible = setting('p2p_enabled') && $featureManager->isVisible('p2p_marketplace');
        $virtualCardMenuVisible = $featureManager->isVisible('virtual_card');
        $voucherMenuVisible = $featureManager->isVisible('vouchers');
        $referralMenuVisible = setting('referral_enabled') && $featureManager->isVisible('referral_program');
    @endphp

    <div class="card border-0 shadow-sm p-2" style="border-radius: 12px;">
        <div class="qa-wrapper position-relative">
        <div class="quick-actions" data-collapsible="1">
            @if($depositMenuVisible)
                <a href="{{ route('user.deposit.create') }}" class="qa-item {{ isActive('user.deposit.create') }}" role="button">
                    <span class="qa-icon"><x-icon name="add-money" class="icon"/></span>
                    <span>{{ __('Deposit') }}</span>
                </a>
            @endif
            @if($withdrawMenuVisible)
                <a href="{{ route('user.withdraw.create') }}" class="qa-item {{ isActive('user.withdraw.create') }}" role="button">
                    <span class="qa-icon"><x-icon name="withdraw" class="icon"/></span>
                    <span>{{ __('Withdraw') }}</span>
                </a>
            @endif
            @if($sendMoneyMenuVisible)
                <a href="{{ route('user.send-money.create') }}" class="qa-item {{ isActive('user.send-money.create') }}" role="button">
                    <span class="qa-icon"><x-icon name="send-money" class="icon"/></span>
                    <span>{{ __('Send') }}</span>
                </a>
            @endif
            @if($requestMoneyMenuVisible)
                <a href="{{ route('user.request-money.create') }}" class="qa-item {{ isActive('user.request-money.create') }}" role="button">
                    <span class="qa-icon"><x-icon name="request_money" class="icon"/></span>
                    <span>{{ __('Request') }}</span>
                </a>
            @endif

            @if($exchangeMoneyMenuVisible)
                <a href="{{ route('user.exchange-money.create') }}" class="qa-item {{ isActive('user.exchange-money.create') }}" role="button">
                    <span class="qa-icon"><x-icon name="exchange" class="icon"/></span>
                    <span>{{ __('Exchange') }}</span>
                </a>
            @endif
            @if($virtualCardMenuVisible)
                <a href="{{ route('user.virtual-card.index') }}" class="qa-item {{ isActive('user.virtual-card.index') }}" role="button">
                    <span class="qa-icon"><x-icon name="virtual-card" class="icon"/></span>
                    <span>{{ __('Cards') }}</span>
                </a>
            @endif
            @if($featureManager->isVisible('mobile_recharge'))
                <a href="{{ route('user.mobile-recharge.create') }}" class="qa-item {{ isActive('user.mobile-recharge.create') }}" role="button">
                    <span class="qa-icon"><x-icon name="mobile-recharge" class="icon"/></span>
                    <span>{{ __('Recharge') }}</span>
                </a>
            @endif
            @if ($p2pMenuVisible)
                <a href="{{ route('user.p2p.offers.index') }}" class="qa-item {{ isActive('user.p2p.offers.index') }}" role="button">
                    <span class="qa-icon"><x-icon name="p2p_trading" class="icon"/></span>
                    <span>{{ __('P2P') }}</span>
                </a>
                <a href="{{ route('user.p2p.orders.index') }}" class="qa-item {{ isActive('user.p2p.orders.index') }}" role="button">
                    <span class="qa-icon"><x-icon name="history" class="icon"/></span>
                    <span>{{ __('Orders') }}</span>
                </a>
            @endif

            @if($voucherMenuVisible)
                <a href="{{ route('user.voucher.my') }}" class="qa-item {{ isActive('user.voucher.my') }}" role="button">
                    <span class="qa-icon"><x-icon name="voucher" class="icon"/></span>
                    <span>{{ __('Voucher') }}</span>
                </a>
            @endif
            <a href="{{ route('user.transaction.index') }}" class="qa-item {{ isActive('user.transaction.index') }}" role="button">
                <span class="qa-icon"><x-icon name="transaction-4" class="icon"/></span>
                <span>{{ __('Transactions') }}</span>
            </a>
            @if($featureManager->isVisible('payment_link'))
                <a href="{{ route('user.payment-links.index') }}" class="qa-item {{ isActive('user.payment-links.index') }}" role="button">
                    <span class="qa-icon"><x-icon name="linked" class="icon"/></span>
                    <span>{{ __('Pay Links') }}</span>
                </a>
            @endif
            @if ($referralMenuVisible)
                <a href="{{ route('user.referral.index') }}" class="qa-item {{ isActive('user.referral.index') }}" role="button">
                    <span class="qa-icon"><x-icon name="user-group" class="icon"/></span>
                    <span>{{ __('Referrals') }}</span>
                </a>
            @endif
            <a href="{{ route('user.support-ticket.index') }}" class="qa-item {{ isActive('user.support-ticket.index') }}" role="button">
                <span class="qa-icon"><x-icon name="support" class="icon"/></span>
                <span>{{ __('Support') }}</span>
            </a>
        </div>
        <div class="qa-overlay">
            <button type="button" class="qa-see-more" data-expanded="0">
                {{ __('See more') }} <i class="fa fa-angle-down ms-1"></i>
            </button>
        </div>
        </div>
    </div>
</div>
