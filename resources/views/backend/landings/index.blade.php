@extends('backend.landings.layout')

@section('title', __('All Landing Pages'))
@section('sub_title', __('Landing Pages'))
@section('sub_subtitle', __('Manage published campaign landings, HTML edits, and the active homepage experience.'))
@section('sub_icon', 'custom-landing')

@section('sub_action')
    <a href="{{ route('admin.custom-landing.guide') }}" class="btn btn-outline-primary cla-btn">
        <x-icon name="info" height="18" width="18"/>
        {{ __('Guide') }}
    </a>
    <a href="{{ route('admin.custom-landing.create') }}" class="btn btn-primary cla-btn">
        <x-icon name="plus" height="18" width="18"/>
        {{ __('Add Landing') }}
    </a>
@endsection

@section('sub_content')
    @php
        $landingCollection = $landings instanceof \Illuminate\Pagination\AbstractPaginator
            ? $landings->getCollection()
            : collect($landings);

        $formatBytes = static function (int|float|null $bytes): string {
            $bytes = (float) ($bytes ?? 0);

            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 1).' MB';
            }

            if ($bytes >= 1024) {
                return number_format($bytes / 1024, 1).' KB';
            }

            return number_format($bytes).' B';
        };
    @endphp

    <div class="cla-metric-grid">
        <div class="cla-metric">
            <span class="cla-metric__label">@lang('Total Landings')</span>
            <strong class="cla-metric__value">{{ number_format($metrics['total'] ?? $landingCollection->count()) }}</strong>
        </div>
        <div class="cla-metric cla-metric--success">
            <span class="cla-metric__label">@lang('Active')</span>
            <strong class="cla-metric__value">{{ number_format($metrics['active'] ?? $landingCollection->where('status', true)->count()) }}</strong>
        </div>
        <div class="cla-metric cla-metric--info">
            <span class="cla-metric__label">@lang('Files')</span>
            <strong class="cla-metric__value">{{ number_format($metrics['files'] ?? $landingCollection->sum('file_count')) }}</strong>
        </div>
        <div class="cla-metric cla-metric--warning">
            <span class="cla-metric__label">@lang('Storage')</span>
            <strong class="cla-metric__value">{{ $formatBytes($metrics['storageSize'] ?? $landingCollection->sum('total_size')) }}</strong>
        </div>
    </div>

    <div class="cla-toolbar">
        <label class="cla-search" for="claLandingSearch">
            <span class="cla-search__icon">
                <x-icon name="search" height="18" width="18"/>
            </span>
            <input id="claLandingSearch" type="search" class="form-control" data-cla-search placeholder="@lang('Search landing name or folder')">
        </label>

        <select class="form-select cla-status-filter" data-cla-status-filter aria-label="@lang('Filter status')">
            <option value="all">@lang('All Status')</option>
            <option value="active">@lang('Active')</option>
            <option value="inactive">@lang('Inactive')</option>
        </select>
    </div>

    <div class="cla-table-wrap">
        <table class="table cla-table mb-0">
            <thead>
                <tr>
                    <th scope="col">@lang('Landing')</th>
                    <th scope="col">@lang('Status')</th>
                    <th scope="col">@lang('Files')</th>
                    <th scope="col">@lang('Updated')</th>
                    <th scope="col" class="text-end">@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($landings as $landing)
                    <tr data-cla-row
                        data-cla-search-text="{{ Str::lower($landing->name.' '.$landing->folder) }}"
                        data-cla-status="{{ $landing->status ? 'active' : 'inactive' }}">
                        <td>
                            <div class="cla-landing-cell">
                                <span class="cla-landing-cell__mark">{{ Str::of($landing->name)->substr(0, 1)->upper() }}</span>
                                <span class="cla-landing-cell__content">
                                    <strong class="cla-landing-cell__name">{{ $landing->name }}</strong>
                                    <span class="cla-landing-cell__folder">{{ $landing->folder }}</span>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="cla-pill {{ $landing->status ? 'cla-pill--success' : 'cla-pill--danger' }}">
                                {{ $landing->status ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td>
                            <span class="cla-meta-stack">
                                <strong>{{ number_format((int) $landing->file_count) }}</strong>
                                <span>{{ $formatBytes($landing->total_size) }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="cla-meta-stack">
                                <strong>{{ $landing->updated_at?->format('M d, Y') ?? __('Not updated') }}</strong>
                                <span>{{ $landing->last_validated_at?->diffForHumans() ?? __('Validation pending') }}</span>
                            </span>
                        </td>
                        <td>
                            <div class="cla-action-list">
                                <a href="{{ route('admin.custom-landing.show', $landing) }}" class="btn btn-outline-secondary cla-icon-btn" title="@lang('Preview')">
                                    <x-icon name="eye" height="18" width="18"/>
                                </a>
                                <form action="{{ route('admin.custom-landing.activate', $landing) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success cla-icon-btn" title="@lang('Activate')" @disabled($landing->status || ! $landing->hasIndexFile())>
                                        <x-icon name="play" height="18" width="18"/>
                                    </button>
                                </form>
                                <a href="{{ route('admin.custom-landing.manage-html', $landing) }}" class="btn btn-outline-primary cla-icon-btn" title="@lang('Manage HTML')">
                                    <x-icon name="html" height="18" width="18"/>
                                </a>
                                <a href="{{ route('admin.custom-landing.edit', $landing) }}" class="btn btn-outline-primary cla-icon-btn" title="@lang('Edit')">
                                    <x-icon name="manage" height="18" width="18"/>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-outline-danger cla-icon-btn delete" data-url="{{ route('admin.custom-landing.destroy', $landing) }}" title="@lang('Delete')">
                                    <x-icon name="delete-3" height="18" width="18"/>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="cla-empty">
                                <x-icon name="upload" class="cla-empty__icon"/>
                                <strong>@lang('No landing pages uploaded yet.')</strong>
                                <span>@lang('Upload a ZIP package to publish your first custom landing page.')</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="cla-empty d-none" data-cla-filter-empty>
        <x-icon name="search" class="cla-empty__icon"/>
        <strong>@lang('No matching landing pages')</strong>
        <span>@lang('Adjust the search text or status filter.')</span>
    </div>

    @if(method_exists($landings, 'links'))
        <div class="cla-pagination">
            {{ $landings->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('backend/js/custom-landing-admin.js?v=' . config('app.version')) }}"></script>
@endpush
