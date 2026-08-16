@extends('backend.landings.layout')

@section('title', __('Edit Landing Page'))
@section('sub_title', __('Edit Landing Page'))
@section('sub_subtitle', __('Update landing metadata, replace assets, or control active status.'))
@section('sub_icon', 'manage')

@section('sub_action')
    <a href="{{ route('admin.custom-landing.show', $landing_page) }}" class="btn btn-outline-secondary cla-btn">
        <x-icon name="eye" height="18" width="18"/>
        {{ __('Preview') }}
    </a>
    <a href="{{ route('admin.custom-landing.index') }}" class="btn btn-outline-primary cla-btn">
        <x-icon name="back" height="18" width="18"/>
        {{ __('Back') }}
    </a>
@endsection

@section('sub_content')
    <form action="{{ route('admin.custom-landing.update', $landing_page) }}" method="POST" enctype="multipart/form-data" class="cla-form-grid">
        @csrf
        @method('PUT')

        <div class="cla-form-main">
            <div class="cla-field">
                <label for="name" class="form-label">@lang('Landing Page Name')</label>
                <input type="text" id="name" name="name" value="{{ old('name', $landing_page->name) }}" class="form-control" placeholder="@lang('Campaign landing name')" required>
                <span class="text-muted small">@lang('Changing the name does not rename the existing public folder.')</span>
            </div>

            <div class="cla-field">
                <label class="form-label" for="zipFile">@lang('Replace ZIP Files')</label>
                <label class="cla-dropzone" data-cla-dropzone>
                    <span class="cla-dropzone__icon">
                        <x-icon name="upload" height="26" width="26"/>
                    </span>
                    <span>
                        <strong data-cla-file-name>@lang('Drop a replacement ZIP or click to browse')</strong>
                        <span>@lang('Leave empty to keep the currently published files.')</span>
                    </span>
                    <input type="file" id="zipFile" name="zipFile" accept=".zip" data-cla-file-input>
                </label>
            </div>

            <div class="cla-field">
                <label class="cla-switch">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" value="1" @checked(old('status', $landing_page->status))>
                    <span class="cla-switch__track"></span>
                    <span class="cla-switch__text">@lang('Use this landing as active homepage')</span>
                </label>
            </div>

            <div class="cla-submit-row">
                <button class="btn btn-primary cla-btn" type="submit">
                    <x-icon name="check" height="18" width="18"/>
                    @lang('Update Landing Page')
                </button>
            </div>
        </div>

        <aside class="cla-guidance">
            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Current Package')</span>
                <div class="cla-detail-list">
                    <div>
                        <span>@lang('Folder')</span>
                        <strong>{{ $landing_page->folder }}</strong>
                    </div>
                    <div>
                        <span>@lang('Files')</span>
                        <strong>{{ number_format((int) $landing_page->file_count) }}</strong>
                    </div>
                    <div>
                        <span>@lang('Last Validated')</span>
                        <strong>{{ $landing_page->last_validated_at?->diffForHumans() ?? __('Validation pending') }}</strong>
                    </div>
                </div>
            </div>

            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Action Shortcuts')</span>
                <button type="button" class="cla-copy-line" data-cla-copy-value='data-dk-action="user-login"'>
                    <code>data-dk-action="user-login"</code>
                </button>
                <button type="button" class="cla-copy-line" data-cla-copy-value="{user_deposit_url}">
                    <code>{user_deposit_url}</code>
                </button>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('backend/js/custom-landing-admin.js?v=' . config('app.version')) }}"></script>
@endpush
