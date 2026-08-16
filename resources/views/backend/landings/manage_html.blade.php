@extends('backend.landings.layout')

@section('title', __('Manage HTML'))
@section('sub_title', __('Manage HTML'))
@section('sub_subtitle', $landing_page->name)
@section('sub_icon', 'html')

@push('styles')
    <link href="{{ asset('backend/css/codemirror.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/css/ayu-dark.css') }}" rel="stylesheet">
@endpush

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
    <form method="POST" action="{{ route('admin.custom-landing.manage-html-update', $landing_page) }}" class="cla-editor-form">
        @csrf

        <div class="cla-editor-toolbar">
            <div class="cla-editor-toolbar__meta">
                <span class="cla-pill {{ $landing_page->status ? 'cla-pill--success' : 'cla-pill--muted' }}">
                    {{ $landing_page->status ? __('Active') : __('Inactive') }}
                </span>
                <span>{{ $landing_page->folder }}/index.html</span>
            </div>

            <div class="cla-editor-toolbar__actions">
                <button type="button" class="btn btn-outline-secondary cla-btn" data-cla-copy-value='data-dk-action="user-login"'>
                    <x-icon name="clipboard" height="18" width="18"/>
                    {{ __('Copy Login Action') }}
                </button>
                <button class="btn btn-primary cla-btn" type="submit">
                    <x-icon name="check" height="18" width="18"/>
                    @lang('Update HTML')
                </button>
            </div>
        </div>

        <div class="cla-guidance__section">
            <span class="cla-guidance__eyebrow">@lang('Editor Notes')</span>
            <p class="mb-0 text-muted">@lang('Use HTML placeholders such as {user_login_url}, or add data actions like data-dk-action="user-login" to buttons and links. The bridge script is injected when the HTML is saved.')</p>
        </div>

        <div class="cla-editor-wrap">
            <textarea name="htmlContent" id="htmlContent" class="form-control editorContainer" rows="18">{{ $content }}</textarea>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('backend/js/codemirror.js') }}"></script>
    <script src="{{ asset('backend/js/code-css.js') }}"></script>
    <script src="{{ asset('backend/js/custom-landing-admin.js?v=' . config('app.version')) }}"></script>
    <script>
        (() => {
            'use strict';

            const editorContainer = document.querySelector('.editorContainer');

            if (! editorContainer) {
                return;
            }

            CodeMirror.fromTextArea(editorContainer, {
                lineNumbers: true,
                mode: 'text/html',
                theme: 'ayu-dark',
            });
        })();
    </script>
@endpush
