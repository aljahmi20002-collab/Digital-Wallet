@php
    $provider = $provider ?? null;
    $isEditing = $provider !== null;
@endphp

<form action="{{ $isEditing ? route('admin.mobile-recharge.providers.update', ['provider' => $provider->id]) : route('admin.mobile-recharge.providers.store') }}"
      method="POST"
      enctype="multipart/form-data"
      class="mra-provider-form">
    @csrf
    @if($isEditing)
        @method('PUT')
    @endif

    @include('backend.mobile_recharge.providers._form', ['provider' => $provider])

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <x-icon name="check" height="16" width="16"/>
            {{ $isEditing ? __('Save Changes') : __('Create Provider') }}
        </button>
        <button type="button" class="btn btn-light" data-coreui-dismiss="modal">@lang('Cancel')</button>
    </div>
</form>
