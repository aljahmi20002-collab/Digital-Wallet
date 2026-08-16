@extends('backend.landings.layout')

@section('title', __('Upload Custom Landing Page'))
@section('sub_title', __('Upload Custom Landing Page'))
@section('sub_subtitle', __('Validate a ZIP package, publish it, and connect it to DigiKash action links.'))
@section('sub_icon', 'upload')

@section('sub_action')
    <a href="{{ route('admin.custom-landing.index') }}" class="btn btn-outline-primary cla-btn">
        <x-icon name="back" height="18" width="18"/>
        {{ __('Back') }}
    </a>
    <a href="{{ route('admin.custom-landing.guide') }}" class="btn btn-outline-secondary cla-btn">
        <x-icon name="info" height="18" width="18"/>
        {{ __('Guide') }}
    </a>
@endsection

@section('sub_content')
    <form action="{{ route('admin.custom-landing.store') }}" method="POST" enctype="multipart/form-data" class="cla-form-grid">
        @csrf

        <div class="cla-form-main">
            <div class="cla-field">
                <label for="name" class="form-label">@lang('Landing Page Name')</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="@lang('Campaign landing name')" required>
                <span class="text-muted small">@lang('Use a clear name so the published folder can be identified later.')</span>
            </div>

            <div class="cla-field">
                <label class="form-label" for="zipFile">@lang('Landing ZIP')</label>
                <label class="cla-dropzone" data-cla-dropzone>
                    <span class="cla-dropzone__icon">
                        <x-icon name="upload" height="26" width="26"/>
                    </span>
                    <span>
                        <strong data-cla-file-name>@lang('Drop ZIP file here or click to upload')</strong>
                        <span>@lang('The package must include index.html at the ZIP root.')</span>
                    </span>
                    <input type="file" id="zipFile" name="zipFile" accept=".zip" data-cla-file-input required>
                </label>
            </div>

            <div class="cla-submit-row">
                <button class="btn btn-primary cla-btn" type="submit">
                    <x-icon name="check" height="18" width="18"/>
                    @lang('Validate & Publish')
                </button>
            </div>
        </div>

        <aside class="cla-guidance">
            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Allowed Files')</span>
                <p class="mb-0 text-muted">@lang('Upload a ZIP containing HTML, CSS, JavaScript, images, fonts, and other static assets needed by the landing page.')</p>
                <pre class="cla-code-block">/index.html
/css/
/js/
/images/
/fonts/</pre>
            </div>

            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Action Shortcuts')</span>
                <p class="mb-0 text-muted">@lang('Use placeholders or data actions to connect buttons to product flows after publishing.')</p>
                <button type="button" class="cla-copy-line" data-cla-copy-value='data-dk-action="user-login"'>
                    <code>data-dk-action="user-login"</code>
                </button>
                <button type="button" class="cla-copy-line" data-cla-copy-value="{user_register_url}">
                    <code>{user_register_url}</code>
                </button>
                <button type="button" class="cla-copy-line" data-cla-copy-value="{user_dashboard_url}">
                    <code>{user_dashboard_url}</code>
                </button>
            </div>

            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Publish Notes')</span>
                <div class="cla-token-list">
                    <span>@lang('ZIP only')</span>
                    <span>@lang('index.html required')</span>
                    <span>@lang('Current active page disabled')</span>
                </div>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('backend/js/custom-landing-admin.js?v=' . config('app.version')) }}"></script>
@endpush
