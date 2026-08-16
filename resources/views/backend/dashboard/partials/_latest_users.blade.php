<div class="col-12 col-xl-6">
	<div class="card dashboard-feed-card dashboard-feed-card--premium dashboard-feed-card--accounts dashboard-activity-panel dashboard-activity-panel--accounts border-0 h-100">
		<div class="card-body p-0">
			<div class="dashboard-panel__header dashboard-activity-panel__header">
				<div class="dashboard-activity-panel__heading">
					<span class="dashboard-section__eyebrow">{{ __('Account Activity') }}</span>
					<h2 class="dashboard-panel__title mb-1">{{ __('Recent Users') }}</h2>
				</div>
				<a href="{{ route('admin.user.index') }}" class="dashboard-link-pill dashboard-activity-panel__link">
					{{ __('View All') }}
					<i class="fas fa-arrow-right"></i>
				</a>
			</div>

			<div class="dashboard-feed-list dashboard-feed-list--divided dashboard-activity-panel__list">
				@if($users->isNotEmpty())
					@foreach($users as $user)
						@php
							$avatarData = getUserAvatarDetails($user->first_name, $user->last_name);
							$isVerified = (bool) $user->email_verified_at;
						@endphp
						<div class="dashboard-feed-item dashboard-feed-item--flush dashboard-feed-item--users">
							{{-- Identity: avatar + name + email (key identifier) --}}
							<div class="dashboard-feed-item__primary">
								<div class="dashboard-avatar-wrap">
									@if($user->avatar)
										<img class="dashboard-avatar dashboard-avatar--image" src="{{ asset($user->avatar) }}" alt="User Avatar" loading="lazy">
									@else
										<div class="dashboard-avatar {{ $avatarData['class'] }}">
											{{ $avatarData['initials'] }}
										</div>
									@endif
									<span class="dashboard-avatar__status bg-{{ $user->status ? 'success' : 'danger' }}"></span>
								</div>

								<div class="dashboard-feed-item__content">
									<a href="{{ route('admin.user.manage', $user->username) }}" class="dashboard-feed-item__title">{{ title($user->name) }}</a>
									<span class="dashboard-feed-item__meta dashboard-feed-item__meta--email" title="{{ $user->email }}">{{ maskSensitive($user->email) }}</span>
								</div>
							</div>

							{{-- Status + when (verification + joined) --}}
							<div class="dashboard-feed-item__status">
								<span class="dashboard-status-pill dashboard-status-pill--{{ $isVerified ? 'success' : 'danger' }}">
									<span class="dashboard-status-pill__dot" aria-hidden="true"></span>
									{{ $isVerified ? __('Verified') : __('Unverified') }}
								</span>
								<span class="dashboard-feed-item__meta">
									<i class="fa-regular fa-clock dashboard-feed-item__meta-icon"></i>{{ $user->created_at->diffForHumans() }}
								</span>
							</div>

							{{-- Action --}}
							<div class="dashboard-feed-item__action">
								<a href="{{ route('admin.user.manage', $user->username) }}" class="dashboard-action-btn" title="{{ __('Manage user') }}" aria-label="{{ __('Manage user') }}">
									<x-icon name="manage" height="18"/>
								</a>
							</div>
						</div>
					@endforeach
				@else
					<x-admin-not-found
						:title="__('No users found')"
						:message="__('Recently joined accounts will appear here once registrations start coming in.')"
						icon="fa-users"
						class="dashboard-empty-state"
					/>
				@endif
			</div>
		</div>
	</div>
</div>

