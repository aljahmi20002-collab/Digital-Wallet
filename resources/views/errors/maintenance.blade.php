@extends('errors.layouts')
@section('title')
	{{ setting('maintenance_title') }}
@endsection
@section('error-content')
	<section class="error-section py-5 w-100">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-12 col-md-10 col-lg-8">
					<div class="card error-card border-0 shadow-sm text-center p-4 p-md-5">
						<div class="mb-4">
							<img class="img-fluid error-illustration" src="{{ asset(setting('maintenance_cover')) }}" alt="Maintenance" loading="lazy">
						</div>
						<h1 class="h2 mb-2">{{ setting('maintenance_title') }}</h1>
						<p class="lead text-muted mb-0">{{ setting('maintenance_text') }}</p>

						<footer class="text-muted mt-3">
							<small>&copy; {{ date('Y') }} {{ setting('site_title') }}</small>
						</footer>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection