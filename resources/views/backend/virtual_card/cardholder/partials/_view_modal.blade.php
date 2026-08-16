@php
    use App\Enums\VirtualCard\CardholderType;
    use Illuminate\Support\Str;

    $isBusiness = $holder->card_type instanceof CardholderType
        && $holder->card_type === CardholderType::BUSINESS;
    $business   = $holder->business;
    $chCountry  = ($isBusiness && $business) ? $business->country : $holder->country;
    $idDocUrl   = $holder->kyc_documents['id_document'] ?? null;
    $compat     = isset($providers)
        ? $providers->filter(fn ($p) => $p->supportsCountry($chCountry))
        : collect();
    $owners     = is_array($business?->beneficial_owners) ? $business->beneficial_owners : [];
@endphp
<div class="modal fade" id="view-cardholder-{{ $holder->id }}" tabindex="-1" aria-labelledby="viewCardholderLabel-{{ $holder->id }}" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title fw-bold" id="viewCardholderLabel-{{ $holder->id }}">
					{{ __('Cardholder Details') }}
				</h5>
				<button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">

				{{-- Header card --}}
				<div class="card mb-3 shadow-sm border-0">
					<div class="card-body">
						<div class="d-flex flex-wrap align-items-center gap-3">
							<div>
								<h5 class="mb-1">
									{{ (($isBusiness && $business) ? $business->business_name : $holder->full_name) ?: '—' }}
									<span class="badge bg-{{ $holder->card_type->class() }} ms-1">{{ $holder->card_type->label() }}</span>
								</h5>
								<div class="text-muted small">
									{{ (($isBusiness && $business) ? $business->contact_email : $holder->email) ?: '—' }}
								</div>
							</div>
							<div class="ms-auto d-flex flex-wrap gap-2">
								<span class="badge bg-{{ $holder->status->badgeColor() }}">{{ __('Status') }}: {{ $holder->status->label() }}</span>
								@if($holder->kyc_status)
									<span class="badge bg-secondary">{{ __('KYC') }}: {{ $holder->kyc_status?->label() ?? $holder->kyc_status }}</span>
								@endif
							</div>
						</div>

						{{-- Provider compatibility row --}}
						@if(isset($providers))
							<hr class="my-3">
							<div class="d-flex flex-wrap align-items-center gap-2">
								<span class="fw-bold small text-uppercase text-muted">{{ __('Compatible Providers') }}:</span>
								@if($compat->isEmpty())
									<span class="badge bg-danger">
										<i class="fa-solid fa-triangle-exclamation me-1"></i>
										{{ __('No provider supports country :c', ['c' => $chCountry ?: __('—')]) }}
									</span>
								@else
									@foreach($compat as $provider)
										<span class="badge bg-success-subtle text-success border border-success-subtle">
											{{ $provider->display_label ?: $provider->name }}
											@if(is_array($provider->supported_countries) && ! empty($provider->supported_countries))
												<span class="ms-1 opacity-75">({{ implode(',', $provider->supported_countries) }})</span>
											@endif
										</span>
									@endforeach
								@endif
							</div>
						@endif
					</div>
				</div>

				@if(! $isBusiness)
					{{-- Identity --}}
					<div class="card mb-3 border-0 shadow-sm">
						<div class="card-header bg-light">
							<i class="fa-solid fa-user me-2"></i><strong>{{ __('Identity') }}</strong>
						</div>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-3"><div class="text-muted small">{{ __('Title') }}</div><div class="fw-semibold">{{ $holder->title ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('First Name') }}</div><div class="fw-semibold">{{ $holder->first_name ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Middle Name') }}</div><div class="fw-semibold">{{ $holder->middle_name ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Last Name') }}</div><div class="fw-semibold">{{ $holder->last_name ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Gender') }}</div><div class="fw-semibold">{{ $holder->gender?->label() ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Date of Birth') }}</div><div class="fw-semibold">{{ optional($holder->dob)->format('Y-m-d') ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Nationality') }}</div><div class="fw-semibold">{{ $holder->nationality ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Place of Birth') }}</div><div class="fw-semibold">{{ $holder->place_of_birth ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Relation') }}</div><div class="fw-semibold">{{ $holder->relation ?: '—' }}</div></div>
							</div>
						</div>
					</div>

					{{-- Contact --}}
					<div class="card mb-3 border-0 shadow-sm">
						<div class="card-header bg-light">
							<i class="fa-solid fa-envelope me-2"></i><strong>{{ __('Contact') }}</strong>
						</div>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-6"><div class="text-muted small">{{ __('Email') }}</div><div class="fw-semibold">{{ $holder->email ?: '—' }}</div></div>
								<div class="col-md-6"><div class="text-muted small">{{ __('Mobile') }}</div><div class="fw-semibold font-monospace">{{ trim(($holder->phone_country_code ?? '').' '.($holder->mobile ?? '—')) }}</div></div>
							</div>
						</div>
					</div>

					{{-- Address --}}
					<div class="card mb-3 border-0 shadow-sm">
						<div class="card-header bg-light">
							<i class="fa-solid fa-location-dot me-2"></i><strong>{{ __('Billing Address') }}</strong>
						</div>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-12"><div class="text-muted small">{{ __('Street') }}</div><div class="fw-semibold">{{ trim(($holder->address_line1 ?? '').($holder->address_line2 ? ', '.$holder->address_line2 : '')) ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('City') }}</div><div class="fw-semibold">{{ $holder->city ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('State') }}</div><div class="fw-semibold">{{ $holder->state ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Postal') }}</div><div class="fw-semibold">{{ $holder->postal_code ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Country') }}</div><div class="fw-semibold">{{ $holder->country ?: '—' }}</div></div>
							</div>
						</div>
					</div>

					{{-- Government ID --}}
					<div class="card mb-3 border-0 shadow-sm">
						<div class="card-header bg-light">
							<i class="fa-solid fa-id-card-clip me-2"></i><strong>{{ __('Government ID') }}</strong>
						</div>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-3"><div class="text-muted small">{{ __('Type') }}</div><div class="fw-semibold">{{ $holder->id_type ? Str::headline(str_replace('_', ' ', $holder->id_type)) : '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Number') }}</div><div class="fw-semibold font-monospace">{{ $holder->id_number ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Issuing Country') }}</div><div class="fw-semibold">{{ $holder->id_issue_country ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Expiry') }}</div><div class="fw-semibold">{{ optional($holder->id_expiry)->format('Y-m-d') ?: '—' }}</div></div>
								@if($idDocUrl)
									<div class="col-12">
										<a href="{{ asset($idDocUrl) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
											<i class="fa-solid fa-file-arrow-down me-1"></i>{{ __('View ID Document') }}
										</a>
									</div>
								@endif
							</div>
						</div>
					</div>

					{{-- Tax / Employment / Compliance --}}
					@if($holder->tax_id || $holder->occupation || $holder->pep_flag || $holder->sanctions_flag)
						<div class="card mb-3 border-0 shadow-sm">
							<div class="card-header bg-light">
								<i class="fa-solid fa-shield-halved me-2"></i><strong>{{ __('Tax · Employment · Compliance') }}</strong>
							</div>
							<div class="card-body">
								<div class="row g-3">
									<div class="col-md-4"><div class="text-muted small">{{ __('Tax ID') }}</div><div class="fw-semibold font-monospace">{{ $holder->tax_id ?: '—' }}</div></div>
									<div class="col-md-4"><div class="text-muted small">{{ __('Tax Country') }}</div><div class="fw-semibold">{{ $holder->tax_country ?: '—' }}</div></div>
									<div class="col-md-4"><div class="text-muted small">{{ __('Source of Funds') }}</div><div class="fw-semibold">{{ $holder->source_of_funds ? Str::headline(str_replace('_', ' ', $holder->source_of_funds)) : '—' }}</div></div>
									<div class="col-md-4"><div class="text-muted small">{{ __('Occupation') }}</div><div class="fw-semibold">{{ $holder->occupation ?: '—' }}</div></div>
									<div class="col-md-4"><div class="text-muted small">{{ __('Employer') }}</div><div class="fw-semibold">{{ $holder->employer ?: '—' }}</div></div>
									<div class="col-md-4"><div class="text-muted small">{{ __('Annual Income') }}</div><div class="fw-semibold font-monospace">{{ $holder->annual_income ? '$'.number_format((float)$holder->annual_income, 2) : '—' }}</div></div>
									<div class="col-md-4">
										<div class="text-muted small">{{ __('PEP') }}</div>
										<span class="badge bg-{{ $holder->pep_flag ? 'danger' : 'success' }}">{{ $holder->pep_flag ? __('Yes') : __('No') }}</span>
									</div>
									<div class="col-md-4">
										<div class="text-muted small">{{ __('Sanctions') }}</div>
										<span class="badge bg-{{ $holder->sanctions_flag ? 'danger' : 'success' }}">{{ $holder->sanctions_flag ? __('Yes') : __('No') }}</span>
									</div>
								</div>
							</div>
						</div>
					@endif
				@else
					{{-- Business sections --}}
					<div class="card mb-3 border-0 shadow-sm">
						<div class="card-header bg-light">
							<i class="fa-solid fa-building me-2"></i><strong>{{ __('Legal Identity') }}</strong>
						</div>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-6"><div class="text-muted small">{{ __('Legal Name') }}</div><div class="fw-semibold">{{ $business?->business_name ?: '—' }}</div></div>
								<div class="col-md-6"><div class="text-muted small">{{ __('Trading Name') }}</div><div class="fw-semibold">{{ $business?->trading_name ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Entity Type') }}</div><div class="fw-semibold">{{ $business?->business_type ? Str::headline(str_replace('_', ' ', $business->business_type)) : '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Incorporated') }}</div><div class="fw-semibold">{{ optional($business?->incorporation_date)->format('Y-m-d') ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Country') }}</div><div class="fw-semibold">{{ $business?->incorporation_country ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Industry') }}</div><div class="fw-semibold">{{ $business?->industry ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Registration') }}</div><div class="fw-semibold font-monospace">{{ $business?->registration_number ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('TIN') }}</div><div class="fw-semibold font-monospace">{{ $business?->tin ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('MCC') }}</div><div class="fw-semibold font-monospace">{{ $business?->mcc_code ?: '—' }}</div></div>
								<div class="col-md-3"><div class="text-muted small">{{ __('Website') }}</div><div class="fw-semibold">@if($business?->website_url)<a href="{{ $business->website_url }}" target="_blank">{{ $business->website_url }}</a>@else—@endif</div></div>
							</div>
						</div>
					</div>

					<div class="card mb-3 border-0 shadow-sm">
						<div class="card-header bg-light">
							<i class="fa-solid fa-headset me-2"></i><strong>{{ __('Contact & Address') }}</strong>
						</div>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-6"><div class="text-muted small">{{ __('Email') }}</div><div class="fw-semibold">{{ $business?->contact_email ?: '—' }}</div></div>
								<div class="col-md-6"><div class="text-muted small">{{ __('Phone') }}</div><div class="fw-semibold font-monospace">{{ trim(($business?->phone_country_code ?? '').' '.($business?->contact_phone ?? '—')) }}</div></div>
								<div class="col-12"><div class="text-muted small">{{ __('Address') }}</div><div class="fw-semibold">{{ $business?->full_address ?: '—' }}</div></div>
							</div>
						</div>
					</div>

					@if(! empty($owners))
						<div class="card mb-3 border-0 shadow-sm">
							<div class="card-header bg-light">
								<i class="fa-solid fa-user-tie me-2"></i><strong>{{ __('Beneficial Owners') }}</strong>
								<span class="badge bg-info ms-2">{{ count($owners) }}</span>
							</div>
							<div class="card-body">
								@foreach($owners as $owner)
									<div class="d-flex flex-wrap align-items-center gap-2 py-2 @if(! $loop->last) border-bottom @endif">
										<div class="fw-semibold">{{ $owner['name'] ?? '—' }}</div>
										<span class="badge bg-primary">{{ ($owner['ownership_pct'] ?? 0) }}%</span>
										@if(! empty($owner['country']))
											<span class="badge bg-info">{{ $owner['country'] }}</span>
										@endif
										@if(! empty($owner['id_type']))
											<span class="badge bg-light text-dark border">
												{{ Str::headline(str_replace('_', ' ', $owner['id_type'])) }}
												@if(! empty($owner['id_number']))
													<span class="font-monospace ms-1">{{ $owner['id_number'] }}</span>
												@endif
											</span>
										@endif
										@if(! empty($owner['dob']))
											<small class="text-muted ms-auto">DOB: {{ $owner['dob'] }}</small>
										@endif
									</div>
								@endforeach
							</div>
						</div>
					@endif
				@endif

				{{-- KYC documents (legacy) --}}
				@if($holder->kyc_documents && count($holder->kyc_documents) > 0)
					<div class="card mb-3 border-0 shadow-sm">
						<div class="card-header bg-light">
							<i class="fa-solid fa-folder-open me-2"></i><strong>{{ __('KYC Documents') }}</strong>
						</div>
						<div class="card-body">
							<div class="row g-3">
								@foreach($holder->kyc_documents as $type => $doc)
									<div class="col-md-4 col-12">
										<div class="text-muted small mb-1">{{ Str::headline(str_replace('_', ' ', $type)) }}</div>
										@php $isImage = is_string($doc) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $doc); @endphp
										@if($isImage)
											<a href="{{ asset($doc) }}" target="_blank" rel="noopener">
												<img src="{{ asset($doc) }}" alt="{{ $type }}" class="img-fluid rounded border vc-admin-doc-thumb" loading="lazy">
											</a>
										@else
											<a href="{{ asset($doc) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
												<i class="fa-solid fa-file-arrow-down me-1"></i>{{ __('Open file') }}
											</a>
										@endif
									</div>
								@endforeach
							</div>
						</div>
					</div>
				@endif
			</div>

			<div class="modal-footer bg-light p-3 d-flex flex-column gap-2 align-items-stretch">
				@if($holder->status->isPending())
					@can('virtual-card-action')
						<form action="{{ route('admin.virtual-card.cardholders.action', $holder->id) }}" method="POST" class="w-100">
							@csrf
							<div class="d-flex w-100 gap-2">
								<button type="submit" name="action" value="approve" class="btn btn-success text-white d-flex align-items-center justify-content-center flex-grow-1">
									<i class="fa-solid fa-check me-2"></i> {{ __('Approve') }}
								</button>
								<button type="submit" name="action" value="reject" class="btn btn-danger text-white d-flex align-items-center justify-content-center flex-grow-1">
									<i class="fa-solid fa-times me-2"></i> {{ __('Reject') }}
								</button>
							</div>
						</form>
					@endcan
				@else
					<div class="d-flex w-100 justify-content-end">
						<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">{{ __('Close') }}</button>
					</div>
				@endif

				@can('virtual-card-action')
					{{-- Bitnob pre-indexing: register the cardholder with the
					     Bitnob Visa BIN pool so issuance doesn't fail with
					     "User with this email is not indexed for visa".
					     Idempotent — safe to click multiple times. --}}
					<form action="{{ route('admin.virtual-card.cardholders.bitnob-verify', $holder->id) }}" method="POST" class="w-100">
						@csrf
						<button type="submit" class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100">
							<i class="fa-solid fa-shield-check me-2"></i>
							{{ __('Verify with Bitnob (Visa indexing)') }}
						</button>
					</form>
				@endcan
			</div>
		</div>
	</div>
</div>
