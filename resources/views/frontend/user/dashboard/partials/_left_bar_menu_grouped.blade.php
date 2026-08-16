<div class="single-card-box d-lg-block d-none dashboard-sidebar-menu-card">
	@php
		$depositMenuVisible = $featureManager->isVisible('deposit_money');
		$withdrawMenuVisible = $featureManager->isVisible('withdraw_money');
		$sendMoneyMenuVisible = $featureManager->isVisible('send_money');
		$requestMoneyMenuVisible = $featureManager->isVisible('request_money');
		$exchangeMoneyMenuVisible = $featureManager->isVisible('exchange_money');
		$p2pMenuVisible = $featureManager->isVisible('p2p_marketplace');
		$virtualCardMenuVisible = $featureManager->isVisible('virtual_card');
		$voucherMenuVisible = $featureManager->isVisible('vouchers');
		$giftCardMenuVisible = $featureManager->isVisible('gift_cards');
		$referralMenuVisible = $featureManager->isVisible('referral_program');
		$sidebarAgentServiceRoute = route('user.agent.index');
	@endphp
	
	<ul class="left-menu-box">
		{{-- Single: Dashboard --}}
		<li>
			<a href="{{ route('user.dashboard') }}" class="{{ isActive('user.dashboard') }}">
				<x-icon name="dashboard-2" class="icon"/>
				{{ __('Dashboard Overview') }}
				<i class="fa fa-angle-right arrow"></i>
			</a>
		</li>

		@can('agent')
			<li>
				<a href="{{ $sidebarAgentServiceRoute }}" class="{{ request()->routeIs('user.agent.*') ? 'active' : '' }}">
					<x-icon name="sidebar-agent" class="icon"/>
					{{ __('Agent Services') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
			</li>
		@endcan
		
		{{-- Group: Payments (Deposit, Withdrawals) --}}
		@if($depositMenuVisible || $withdrawMenuVisible)
			@php
				$paymentsActive = ($depositMenuVisible && request()->routeIs('user.deposit.*'))
					|| ($withdrawMenuVisible && request()->routeIs('user.withdraw.*'));
			@endphp
			<li class="menu-group">
				<a href="javascript:void(0)" class="group-toggle {{ $paymentsActive ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuPayments"
				   aria-expanded="{{ $paymentsActive ? 'true' : 'false' }}" aria-controls="menuPayments">
					<x-icon name="add-money" class="icon"/>
					{{ __('Add & Withdraw Funds') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
				<ul id="menuPayments" class="collapse submenu {{ $paymentsActive ? 'show' : '' }}" data-bs-parent=".left-menu-box">
					@if($depositMenuVisible)
						<li>
							<a href="{{ route('user.deposit.create') }}" class="{{ isActive('user.deposit.create') }}">
								<x-icon name="add-money" class="icon"/>
								{{ __('Add Money') }}
								<i class="fa fa-angle-right arrow"></i>
							</a>
						</li>
					@endif
					@if($withdrawMenuVisible)
						<li>
							<a href="{{ route('user.withdraw.create') }}" class="{{ isActive('user.withdraw.create') }}">
								<x-icon name="withdraw" class="icon"/>
								{{ __('Withdraw Funds') }}
								<i class="fa fa-angle-right arrow"></i>
							</a>
						</li>
					@endif
				</ul>
			</li>
		@endif
		
		{{-- Group: P2P Trading (Marketplace, My Listings, My Orders) --}}
		@if ($p2pMenuVisible)
			@php
				$p2pActive = request()->routeIs('user.p2p.offers.*')
					|| request()->routeIs('user.p2p.orders.*')
					|| request()->routeIs('user.p2p.advertisers.*')
					|| request()->routeIs('user.p2p.payment-accounts.*');
			@endphp
			<li class="menu-group">
				<a href="javascript:void(0)" class="group-toggle {{ $p2pActive ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuP2P"
				   aria-expanded="{{ $p2pActive ? 'true' : 'false' }}" aria-controls="menuP2P">
					<x-icon name="p2p_trading" class="icon"/>
					{{ __('P2P Marketplace') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
				<ul id="menuP2P" class="collapse submenu {{ $p2pActive ? 'show' : '' }}" data-bs-parent=".left-menu-box">
					<li>
						<a href="{{ route('user.p2p.offers.index') }}" class="{{ isActive('user.p2p.offers.index') }}">
							<x-icon name="p2p_trading" class="icon"/>
							{{ __('P2P Market') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.p2p.offers.my') }}" class="{{ isActive('user.p2p.offers.my') }}">
							<x-icon name="list-2" class="icon"/>
							{{ __('My Trade Ads') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.p2p.offers.create') }}" class="{{ isActive('user.p2p.offers.create') }}">
							<x-icon name="add" class="icon"/>
							{{ __('Create Trade Ad') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.p2p.orders.index') }}" class="{{ isActive('user.p2p.orders.index') }}">
							<x-icon name="history" class="icon"/>
							{{ __('Trade Orders') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.p2p.payment-accounts.index') }}" class="{{ isActive('user.p2p.payment-accounts.*') }}">
							<x-icon name="wallet" class="icon"/>
							{{ __('Payment Accounts') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.p2p.advertisers.show', auth()->id()) }}" class="{{ isActive('user.p2p.advertisers.show') }}">
							<x-icon name="badge-account" class="icon"/>
							{{ __('Trader Profile') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
				</ul>
			</li>
		@endif
		
		{{-- Group: Virtual Cards (My Cards, Cardholders, Requests) --}}
		@if($virtualCardMenuVisible)
			@php
				$virtualCardActive = request()->routeIs('user.virtual-card.*');
			@endphp
			<li class="menu-group">
				<a href="javascript:void(0)" class="group-toggle {{ $virtualCardActive ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuVirtualCards"
				   aria-expanded="{{ $virtualCardActive ? 'true' : 'false' }}" aria-controls="menuVirtualCards">
					<x-icon name="virtual-card" class="icon"/>
					{{ __('My Virtual Cards') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
				<ul id="menuVirtualCards" class="collapse submenu {{ $virtualCardActive ? 'show' : '' }}" data-bs-parent=".left-menu-box">
					<li>
						<a href="{{ route('user.virtual-card.index') }}" class="{{ isActive('user.virtual-card.index') }}">
							<x-icon name="virtual-card" class="icon"/>
							{{ __('My Cards') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.virtual-card.cardholders.index') }}" class="{{ isActive('user.virtual-card.cardholders.*') }}">
							<x-icon name="cardholders" class="icon"/>
							{{ __('Cardholders') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.virtual-card.request.index') }}" class="{{ isActive('user.virtual-card.request.index') }}">
							<x-icon name="list-2" class="icon"/>
							{{ __('My Requests') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
				</ul>
			</li>
		@endif
		
		{{-- Group: Subscriptions (Plans, Current Subscription, History) --}}
		@if($featureManager->isVisible('subscription_system'))
			@php
				$subscriptionActive = request()->routeIs('user.subscription.*');
			@endphp
			<li class="menu-group">
				<a href="javascript:void(0)" class="group-toggle {{ $subscriptionActive ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuSubscriptions"
				   aria-expanded="{{ $subscriptionActive ? 'true' : 'false' }}" aria-controls="menuSubscriptions">
					<x-icon name="layer" class="icon"/>
					{{ __('Subscriptions') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
				<ul id="menuSubscriptions" class="collapse submenu {{ $subscriptionActive ? 'show' : '' }}" data-bs-parent=".left-menu-box">
					<li>
						<a href="{{ route('user.subscription.current') }}" class="{{ isActive('user.subscription.current') }}">
							<x-icon name="plan" class="icon"/>
							{{ __('Current Active Plan') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.subscription.plans') }}" class="{{ isActive(['user.subscription.plans', 'user.subscription.checkout']) }}">
							<x-icon name="subscription" class="icon"/>
							{{ __('Browse Plans') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.subscription.history') }}" class="{{ isActive('user.subscription.history') }}">
							<x-icon name="history" class="icon"/>
							{{ __('Subscription History') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
				</ul>
			</li>
		@endif
		
		{{-- Group: Wallet Earn (Plans, My Stakes) --}}
		@if($featureManager->isVisible('wallet_earn'))
			@php
				$walletEarnActive = request()->routeIs('user.wallet-earn.*');
			@endphp
			<li class="menu-group">
				<a href="javascript:void(0)" class="group-toggle {{ $walletEarnActive ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuWalletEarn"
				   aria-expanded="{{ $walletEarnActive ? 'true' : 'false' }}" aria-controls="menuWalletEarn">
					<x-icon name="trending-up" class="icon"/>
					{{ __('Wallet Earn') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
				<ul id="menuWalletEarn" class="collapse submenu {{ $walletEarnActive ? 'show' : '' }}" data-bs-parent=".left-menu-box">
					<li>
						<a href="{{ route('user.wallet-earn.stakes', ['status' => 'active']) }}"
						   class="{{ request()->routeIs('user.wallet-earn.stakes') && request('status') === 'active' ? 'active' : '' }}">
							<x-icon name="trending-up" class="icon"/>
							{{ __('Active Earn Positions') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.wallet-earn.plans') }}" class="{{ isActive('user.wallet-earn.plans') }}">
							<x-icon name="plan" class="icon"/>
							{{ __('Earn Plans') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
					<li>
						<a href="{{ route('user.wallet-earn.stakes') }}"
						   class="{{ (request()->routeIs('user.wallet-earn.stakes') && request('status') !== 'active') || request()->routeIs('user.wallet-earn.show') ? 'active' : '' }}">
							<x-icon name="wallet" class="icon"/>
							{{ __('All Earn Positions') }}
							<i class="fa fa-angle-right arrow"></i>
						</a>
					</li>
				</ul>
			</li>
		@endif
		
		{{-- Group: Transfers (Send, Request, Exchange) --}}
		@if($sendMoneyMenuVisible || $requestMoneyMenuVisible || $exchangeMoneyMenuVisible)
			@php
				$transferActive = ($sendMoneyMenuVisible && request()->routeIs('user.send-money.*'))
					|| ($requestMoneyMenuVisible && request()->routeIs('user.request-money.*'))
					|| ($exchangeMoneyMenuVisible && request()->routeIs('user.exchange-money.*'));
			@endphp
			<li class="menu-group">
				<a href="javascript:void(0)" class="group-toggle {{ $transferActive ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuTransfers"
				   aria-expanded="{{ $transferActive ? 'true' : 'false' }}" aria-controls="menuTransfers">
					<x-icon name="send-money" class="icon"/>
					{{ __('Transfer & Exchange') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
				<ul id="menuTransfers" class="collapse submenu {{ $transferActive ? 'show' : '' }}" data-bs-parent=".left-menu-box">
					@if($sendMoneyMenuVisible)
						<li>
							<a href="{{ route('user.send-money.create') }}" class="{{ isActive('user.send-money.create') }}">
								<x-icon name="send-money" class="icon"/>
								{{ __('Send Funds') }}
								<i class="fa fa-angle-right arrow"></i>
							</a>
						</li>
					@endif
					@if($requestMoneyMenuVisible)
						<li>
							<a href="{{ route('user.request-money.create') }}" class="{{ isActive('user.request-money.create') }}">
								<x-icon name="request_money" class="icon"/>
								{{ __('Request Payment') }}
								<i class="fa fa-angle-right arrow"></i>
							</a>
						</li>
					@endif
					@if($exchangeMoneyMenuVisible)
						<li>
							<a href="{{ route('user.exchange-money.create') }}" class="{{ isActive('user.exchange-money.create') }}">
								<x-icon name="exchange" class="icon"/>
								{{ __('Currency Exchange') }}
								<i class="fa fa-angle-right arrow"></i>
							</a>
						</li>
					@endif
				</ul>
			</li>
		@endif
		
		{{-- Single: Voucher --}}
		@if($voucherMenuVisible)
			<li>
				<a href="{{ route('user.voucher.my') }}" class="{{ isActive('user.voucher.my') }}">
					<x-icon name="voucher" class="icon"/>
					{{ __('My Vouchers') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
			</li>
		@endif

		{{-- Single: Gift Cards (matches the Voucher menu pattern — index
		     page hosts both Create and Redeem actions, so a submenu would
		     duplicate buttons already on the destination page). --}}
		@if($giftCardMenuVisible)
			<li>
				<a href="{{ route('user.gift-card.index') }}" class="{{ request()->routeIs('user.gift-card.*') ? 'active' : '' }}">
					<x-icon name="voucher" class="icon"/>
					{{ __('Gift Cards') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
			</li>
		@endif
		
		{{-- Single: Merchant (permission protected) --}}
		@can('merchant')
			<li>
				<a href="{{ route('user.merchant.index') }}" class="{{ isActive('user.merchant.index') }}">
					<x-icon name="merchant" class="icon"/>
					{{ __('Merchant Tools') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
			</li>
		@endcan

		{{-- Single: Mobile Recharge --}}
		@if($featureManager->isVisible('mobile_recharge'))
			<li>
				<a href="{{ route('user.mobile-recharge.create') }}" class="{{ isActive('user.mobile-recharge.create') }}">
					<x-icon name="mobile-recharge" class="icon"/>
					{{ __('Mobile Recharge') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
			</li>
		@endif
		
		{{-- Single: Payment Links --}}
		@if($featureManager->isVisible('payment_link'))
			<li>
				<a href="{{ route('user.payment-links.index') }}" class="{{ isActive(['user.payment-links.index', 'user.payment-links.create', 'user.payment-links.edit']) }}">
					<x-icon name="linked" class="icon"/>
					{{ __('Payment Links') }}
					<i class="fa fa-angle-right arrow"></i>
				</a>
			</li>
		@endif
		
		{{-- Single: Transactions --}}
		<li>
			<a href="{{ route('user.transaction.index') }}" class="{{ isActive('user.transaction.index') }}">
				<x-icon name="transaction-4" class="icon"/>
				{{ __('Transaction History') }}
				<i class="fa fa-angle-right arrow"></i>
			</a>
		</li>
		
		{{-- Single: Support --}}
		<li>
			<a href="{{ route('user.support-ticket.index') }}" class="{{ isActive('user.support-ticket.index') }}">
				<x-icon name="support" class="icon"/>
				{{ __('Support Tickets') }}
				<i class="fa fa-angle-right arrow"></i>
			</a>
		</li>
	</ul>
	
	@if ($referralMenuVisible)
		<div class="dashboard-sidebar-referral-slot">
			<x-user-sidebar-referral-card :href="route('user.referral.index')"/>
		</div>
	@endif
</div>
