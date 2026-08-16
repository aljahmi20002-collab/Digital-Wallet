@extends('backend.landings.layout')

@section('title', __('Preview Landing Page'))
@section('sub_title', __('Landing Preview'))
@section('sub_subtitle', $landing_page->name)

@section('sub_action')
    <a href="{{ route('admin.custom-landing.edit', $landing_page) }}" class="btn btn-primary cla-btn">
        <x-icon name="manage" height="20" width="20"/>
        <span>@lang('Edit')</span>
    </a>
    <a href="{{ route('admin.custom-landing.index') }}" class="btn btn-light cla-btn">
        <x-icon name="back" height="20" width="20"/>
        <span>@lang('Back')</span>
    </a>
@endsection

@section('sub_content')
    @php
        $formatBytes = static function (int|float $bytes): string {
            if ($bytes < 1024) {
                return number_format($bytes) . ' B';
            }

            if ($bytes < 1048576) {
                return number_format($bytes / 1024, 1) . ' KB';
            }

            return number_format($bytes / 1048576, 1) . ' MB';
        };
    @endphp

    <div class="cla-preview-grid">
        <div class="cla-preview-main">
            @if($landing_page->hasIndexFile())
                <iframe class="cla-preview-frame" src="{{ $landing_page->publicUrl() }}" sandbox="allow-forms allow-popups allow-scripts allow-downloads" loading="lazy"></iframe>
            @else
                <div class="cla-empty cla-empty--preview">
                    <x-icon name="custom-landing" class="cla-empty__icon"/>
                    <strong>@lang('index.html is missing')</strong>
                    <span>@lang('Upload a valid ZIP bundle before previewing this landing page.')</span>
                </div>
            @endif
        </div>

        <aside class="cla-preview-side">
            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Publication')</span>
                <span class="cla-pill {{ $landing_page->status ? 'cla-pill--success' : 'cla-pill--muted' }}">
                    {{ $landing_page->status ? __('Active') : __('Inactive') }}
                </span>
                @if(! $landing_page->status && $landing_page->hasIndexFile())
                    <form action="{{ route('admin.custom-landing.activate', $landing_page) }}" method="POST" class="mt-3">
                        @csrf
                        <button class="btn btn-success cla-btn w-100">
                            <x-icon name="play" height="18" width="18"/>
                            <span>@lang('Activate Landing')</span>
                        </button>
                    </form>
                @endif
            </div>

            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Bundle Details')</span>
                <div class="cla-detail-list">
                    <div>
                        <span>@lang('Folder')</span>
                        <strong>{{ $landing_page->folder }}</strong>
                    </div>
                    <div>
                        <span>@lang('Files')</span>
                        <strong>{{ number_format($landing_page->file_count) }}</strong>
                    </div>
                    <div>
                        <span>@lang('Storage')</span>
                        <strong>{{ $formatBytes($landing_page->total_size) }}</strong>
                    </div>
                    <div>
                        <span>@lang('Published')</span>
                        <strong>{{ $landing_page->published_at?->diffForHumans() ?? __('Not published') }}</strong>
                    </div>
                </div>
            </div>

            <div class="cla-guidance__section">
                <span class="cla-guidance__eyebrow">@lang('Public URL')</span>
                <button type="button" class="cla-copy-line" data-cla-copy-value="{{ $landing_page->publicUrl() }}">
                    <code>{{ $landing_page->publicUrl() }}</code>
                </button>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend/js/custom-landing-admin.js?v=' . config('app.version')) }}"></script>
@endpush
