@php use Carbon\Carbon; @endphp
@extends('backend.background_tasks.layout')

@section('title', __('Background Tasks'))

@section('bt_content')

    <div class="bt-stats">
        <div class="bt-stat">
            <div class="bt-stat__label">@lang('Managed Commands')</div>
            <div class="bt-stat__value">{{ $stats['total_tasks'] }}</div>
            <div class="bt-stat__sub">@lang('predefined safe tasks')</div>
        </div>
        <div class="bt-stat">
            <div class="bt-stat__label">@lang('Last Success')</div>
            @if($stats['last_success'])
                <div class="bt-stat__value bt-stat__value--md">
                    {{ Carbon::parse($stats['last_success'])->diffForHumans() }}
                </div>
                <div class="bt-stat__sub">{{ Carbon::parse($stats['last_success'])->format('d M Y, H:i') }}</div>
            @else
                <div class="bt-stat__value bt-stat__value--md text-muted">-</div>
                <div class="bt-stat__sub">@lang('No runs yet')</div>
            @endif
        </div>
        <div class="bt-stat">
            <div class="bt-stat__label">@lang('Last Failure')</div>
            @if($stats['last_failed'])
                <div class="bt-stat__value bt-stat__value--md bt-stat__value--danger">
                    {{ Carbon::parse($stats['last_failed'])->diffForHumans() }}
                </div>
                <div class="bt-stat__sub">{{ Carbon::parse($stats['last_failed'])->format('d M Y, H:i') }}</div>
            @else
                <div class="bt-stat__value bt-stat__value--md" style="color: var(--bt-success);">@lang('None')</div>
                <div class="bt-stat__sub">@lang('All clean')</div>
            @endif
        </div>
        <div class="bt-stat">
            <div class="bt-stat__label">@lang('Failed Queue Jobs')</div>
            <div class="bt-stat__value {{ $stats['failed_jobs'] > 0 ? 'bt-stat__value--danger' : 'bt-stat__value--success' }}">
                {{ number_format($stats['failed_jobs']) }}
            </div>
            <div class="bt-stat__sub">
                @if($stats['failed_jobs'] > 0)
                    @can('queue-manage')
                        <a href="{{ route('admin.queue.failed') }}" class="text-danger fw-semibold">@lang('view failed jobs ->')</a>
                    @else
                        <span class="text-danger">@lang('attention needed')</span>
                    @endcan
                @else
                    @lang('queue is healthy')
                @endif
            </div>
        </div>
    </div>

    @can('queue-manage')
        @if($stats['failed_jobs'] > 0)
            <div class="bt-banner bt-banner--danger mb-3">
                <x-icon name="warning-2" height="14" width="14" class="flex-shrink-0"/>
                <span>
                    @lang(':count failed queue job(s) require attention.', ['count' => number_format($stats['failed_jobs'])])
                    <a href="{{ route('admin.queue.failed') }}" class="fw-semibold ms-1">@lang('Go to Failed Jobs ->')</a>
                </span>
            </div>
        @endif
    @endcan

    <div class="bt-card">
        <div class="table-responsive">
            <table class="bt-table bt-table--preserve-columns">
                <thead>
                <tr>
                    <th>@lang('Command')</th>
                    <th>@lang('Signature / Status')</th>
                    <th>@lang('Last Run')</th>
                    <th>@lang('Output')</th>
                    <th class="text-end">@lang('Total Runs')</th>
                    @can('background-task-run')
                        <th class="text-end">@lang('Action')</th>
                    @endcan
                </tr>
                </thead>
                <tbody>
                @foreach($enriched as $key => $task)
                    @php
                        $log = $task['last_log'];
                        $outputText = trim((string) ($log?->isFailed() ? ($log?->error_message ?? '') : ($log?->output ?? '')));
                    @endphp
                    <tr>
                        <td>
                            <div class="bt-cmd-name">{{ $task['label'] }}</div>
                            <div class="bt-cmd-desc">{{ $task['description'] }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <span class="bt-soft-badge">{{ $task['signature'] }}</span>
                                <div>
                                    @if(!$log)
                                        <span class="bt-pill bt-pill--neutral">@lang('Never run')</span>
                                    @elseif($log->isSuccess())
                                        <span class="bt-pill bt-pill--success">@lang('Success')</span>
                                    @elseif($log->isFailed())
                                        <span class="bt-pill bt-pill--danger">@lang('Failed')</span>
                                    @else
                                        <span class="bt-pill bt-pill--warning">@lang('Running')</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-nowrap">
                            @if(!$log)
                                <span class="bt-meta">-</span>
                            @elseif($log->started_at)
                                <div class="small fw-semibold">{{ $log->started_at->format('d M, H:i') }}</div>
                                <div class="bt-meta">{{ $log->started_at->diffForHumans() }}</div>
                            @else
                                <span class="bt-meta">-</span>
                            @endif
                        </td>
                        <td style="max-width: 200px;">
                            @if($log)
                                <div
                                    class="small text-truncate {{ $log->isFailed() ? 'text-danger' : 'text-muted' }}"
                                    data-coreui-toggle="tooltip"
                                    data-coreui-placement="top"
                                    title="{{ $outputText !== '' ? $outputText : __('No output captured.') }}"
                                >
                                    {{ $log->outputSummary(100) }}
                                </div>
                                @if($log->duration_ms !== null)
                                    <div class="bt-meta">@lang('Duration'): {{ $log->durationLabel() }}</div>
                                @endif
                            @else
                                <span class="bt-meta">-</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold">{{ number_format($task['run_count']) }}</td>
                        @can('background-task-run')
                            <td class="text-end">
                                <div class="bt-actions justify-content-end">
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm run-task-btn d-inline-flex align-items-center gap-1"
                                        data-task-key="{{ $key }}"
                                        data-task-label="{{ $task['label'] }}"
                                        data-task-desc="{{ $task['description'] }}"
                                        data-task-sig="{{ $task['signature'] }}"
                                        data-has-limit="{{ isset($task['options']['limit']) ? '1' : '0' }}"
                                        data-has-renewals="{{ isset($task['options']['renewals']) ? '1' : '0' }}"
                                        data-renewals-default="{{ (bool) ($task['options']['renewals']['default'] ?? false) ? '1' : '0' }}"
                                        data-coreui-toggle="modal"
                                        data-coreui-target="#run-task-modal"
                                    >
                                        <x-icon name="apps" height="13" width="13"/>
                                        @lang('Run')
                                    </button>
                                </div>
                            </td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($recentFailedJobs->count() > 0)
        @can('queue-manage')
            <div class="mt-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="small fw-semibold d-flex align-items-center gap-1" style="color: var(--bt-danger);">
                        <x-icon name="warning-2" height="14" width="14"/>
                        @lang('Recent Failed Jobs')
                    </div>
                    <a href="{{ route('admin.queue.failed') }}" class="small text-muted">@lang('Manage all ->')</a>
                </div>
                <div class="bt-card">
                    <div class="table-responsive">
                        <table class="bt-table">
                            <thead>
                            <tr>
                                <th>@lang('Job')</th>
                                <th>@lang('Queue')</th>
                                <th>@lang('Failed At')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentFailedJobs as $job)
                                @php
                                    $payload = json_decode($job->payload ?? '{}', true);
                                    $jobClass = $payload['displayName'] ?? $payload['job'] ?? 'Unknown';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="small fw-semibold">{{ class_basename($jobClass) }}</div>
                                        <div class="bt-meta" style="font-size: 0.7rem;">{{ $jobClass }}</div>
                                    </td>
                                    <td class="small">{{ $job->queue }}</td>
                                    <td class="bt-meta text-nowrap">
                                        {{ Carbon::parse($job->failed_at)->format('d M, H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @can('background-task-run')
        @include('backend.background_tasks.partials._run_modal')
    @endcan
@endsection

@push('scripts')
    @include('backend.background_tasks.partials._index_scripts')
@endpush
