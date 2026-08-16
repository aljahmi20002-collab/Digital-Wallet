<footer class="footer-area-mobile d-lg-none d-block">
    @php
        $depositFooterVisible = $featureManager->isVisible('deposit_money');
        $sendFooterVisible = $featureManager->isVisible('send_money');
        $withdrawFooterVisible = $featureManager->isVisible('withdraw_money');
    @endphp

    <ul>
        @if($depositFooterVisible)
            <li class="{{ isActive('user.deposit.create') }}">
                <a href="{{ route('user.deposit.create') }}">
                    <x-icon name="deposit" class="icon"/>
                    <span>{{ __('Deposit') }}</span>
                </a>
            </li>
        @endif
        @if($sendFooterVisible)
            <li class="{{ isActive('user.send-money.create') }}">
                <a href="{{ route('user.send-money.create') }}">
                    <x-icon name="send-money" class="icon"/>
                    <span>{{ __('Send') }}</span>
                </a>
            </li>
        @endif
        <li class="{{ isActive('user.dashboard') }}">
            <a href="{{ route('user.dashboard') }}">
                <x-icon name="dashboard-2" class="icon"/>
                <span>{{ __('Dashboard') }}</span>
            </a>
        </li>
        @if($withdrawFooterVisible)
            <li class="{{ isActive('user.withdraw.create') }}">
                <a href="{{ route('user.withdraw.create') }}">
                    <x-icon name="withdraw" class="icon"/>
                    <span>{{ __('Withdraw') }}</span>
                </a>
            </li>
        @endif
        @if($featureManager->isVisible('mobile_recharge'))
            <li class="{{ isActive('user.mobile-recharge.create') }}">
                <a href="{{ route('user.mobile-recharge.create') }}">
                    <x-icon name="mobile-recharge" class="icon"/>
                    <span>{{ __('Recharge') }}</span>
                </a>
            </li>
        @else
            <li class="{{ isActive('user.transaction.index') }}">
                <a href="{{ route('user.transaction.index') }}">
                    <x-icon name="transaction-4" class="icon"/>
                    <span>{{ __('Transaction') }}</span>
                </a>
            </li>
        @endif
    </ul>
</footer>
