@extends('backend.virtual_card.index')
@section('title', __('All Virtual Card Requests'))

@section('virtual_card_header')
	<div class="vc-admin-hero my-3">
		<div>
			<span class="vc-admin-hero__eyebrow">{{ __('Request History') }}</span>
			<h3>{{ __('All Virtual Card Requests') }}</h3>
			<p>{{ __('Track every card request, approval state, card metadata, and review history.') }}</p>
		</div>
		<div class="vc-admin-hero__stats">
			<div>
				<span>{{ __('Total') }}</span>
				<strong>{{ $requests->total() }}</strong>
			</div>
		</div>
		<a href="{{ route('admin.virtual-card.requests.awaiting') }}" class="btn btn-light vc-admin-hero__btn">
			<i class="fa-solid fa-clock-rotate-left"></i>
			{{ __('View Pending') }}
		</a>
	</div>
@endsection

@section('virtual_card_content')
	<div class="card-body vc-admin-board">
		<div class="vc-admin-toolbar">
			<form action="{{ route('admin.virtual-card.requests.all') }}" method="GET" class="row g-2 g-md-3">
				<div class="col-md-6 col-xl-auto">
					<x-form.select name="status" :options="$statuses" :selected="request('status')" :includeBlank="true"/>
				</div>
				<div class="col-md-6 col-xl-auto">
					<div class="input-group">
						<input type="hidden" name="daterange" value="{{ request('daterange') }}">
						<div id="reportrange" class="form-control d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center gap-2">
								<i class="fa-solid fa-calendar-days"></i>
								<span class="text-nowrap flex-grow-1"></span>
							</div>
							<x-icon name="angle-down" class="text-muted flex-shrink-0"/>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-xl-auto">
					<div class="input-group">
						<input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search by user, email, card...') }}">
						<button type="submit" class="btn btn-primary">
							<i class="fa-solid fa-magnifying-glass"></i>
						</button>
					</div>
				</div>
			</form>
		</div>

		<div class="table-responsive vc-admin-table">
			<table class="table align-middle mb-0">
				<thead>
				<tr>
					<th>{{ __('User') }}</th>
					<th>{{ __('Card Details') }}</th>
					<th>{{ __('Status') }}</th>
					<th>{{ __('Requested') }}</th>
					<th class="text-end">{{ __('Actions') }}</th>
				</tr>
				</thead>
				<tbody>
				@forelse($requests as $request)
					<tr>
						<td>
							<div class="vc-admin-user">
								<img src="{{ $request->user->avatar_alt }}" alt="{{ $request->user->name }}" loading="lazy">
								<div>
									<span class="fw-semibold">{{ $request->user->name }}</span>
									<span>#{{ $request->uuid }}</span>
								</div>
							</div>
						</td>
						<td>
							<span class="vc-admin-chip">{{ $request->wallet->currency->code }}</span>
							@if($request->card)
								<span class="vc-admin-chip vc-admin-chip--success ms-1">**** {{ $request->card->last4 }}</span>
								<small class="d-block text-muted mt-1">
									{{ $request->card->brand ?? '' }} /
									{{ $request->card->expiry_month }}/{{ substr($request->card->expiry_year, -2) }}
								</small>
							@endif
						</td>
						<td>
							<span class="badge bg-{{ $request->status->badgeColor() }}">{{ $request->status->label() }}</span>
							@if($request->admin_note)
								<div class="small text-muted mt-1" title="{{ $request->admin_note }}">
									<i class="fa-solid fa-note-sticky"></i>
									{{ Str::limit($request->admin_note, 30) }}
								</div>
							@endif
						</td>
						<td>
							<div class="fw-semibold">{{ $request->created_at->format('M d, Y') }}</div>
							<small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
						</td>
						<td class="text-end">
							<button type="button" class="btn btn-primary vc-admin-action" data-coreui-toggle="modal" data-coreui-target="#review-request-{{ $request->uuid }}">
								<i class="fa-solid fa-arrow-up-right-from-square"></i>
								{{ __('Details') }}
							</button>
							@include('backend.virtual_card.partials._review_modal', ['request' => $request])
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="5">
							<x-admin-not-found
								:title="__('No requests found')"
								:message="__('Virtual card requests matching the current filters will appear here.')"
								icon="fa-inbox"
							/>
						</td>
					</tr>
				@endforelse
				</tbody>
			</table>
		</div>

		@if($requests->hasPages())
			<div class="d-flex justify-content-center mt-4">
				{{ $requests->withQueryString()->links() }}
			</div>
		@endif
	</div>
@endsection
