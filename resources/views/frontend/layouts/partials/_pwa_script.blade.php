@php
    $pwaController = app(\App\Http\Controllers\Frontend\PwaController::class);
@endphp
@if($pwaController->isPwaEnabled())
    @php($pwaScriptVersion = config('app.version').'-'.filemtime(public_path('frontend/js/pwa.js')))
    <script src="{{ asset('frontend/js/pwa.js?v='.$pwaScriptVersion) }}" defer></script>
@endif
