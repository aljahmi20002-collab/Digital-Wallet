@extends('backend.background_tasks.layout')

@section('title', __('Failed Queue Jobs'))
@section('bt_title', __('Failed Queue Jobs'))
@section('bt_icon', 'warning-2')
@section('bt_subtitle', __('Review, retry, or delete failed Laravel queue jobs.'))

@if($count > 0)
    @section('bt_action')
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="POST" action="{{ route('admin.queue.retry-all') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
                    <x-icon name="repeat" height="14" width="14"/>
                    @lang('Retry All')
                </button>
            </form>
            <button type="button"
                    class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1"
                    data-coreui-toggle="modal"
                    data-coreui-target="#flush-confirm-modal">
                <x-icon name="trash" height="14" width="14"/>
                @lang('Flush All')
            </button>
        </div>
    @endsection
@endif

@section('bt_content')

<div class="bt-card">
    @if($count === 0)
        <div class="bt-empty-state">
            <div class="bt-empty-state__icon">
                <x-icon name="check" height="24" width="24"/>
            </div>
            <div class="bt-empty-state__title">@lang('Queue is healthy')</div>
            <div class="bt-empty-state__sub">@lang('No failed jobs found. All jobs are processing normally.')</div>
        </div>
    @else
        <div class="table-responsive">
            <table class="bt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('Job')</th>
                        <th>@lang('Queue')</th>
                        <th>@lang('Exception')</th>
                        <th>@lang('Failed At')</th>
                        <th class="text-end">@lang('Actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedJobs as $job)
                        @php
                            $payload  = json_decode($job->payload ?? '{}', true);
                            $jobClass = $payload['displayName'] ?? $payload['job'] ?? 'Unknown';
                        @endphp
                        <tr>
                            <td class="bt-meta">{{ $job->id }}</td>
                            <td>
                                <div class="small fw-semibold">{{ class_basename($jobClass) }}</div>
                                <div class="bt-meta" style="font-size:0.7rem; word-break:break-all;">{{ $jobClass }}</div>
                            </td>
                            <td class="small">{{ $job->queue }}</td>
                            <td style="max-width:280px;">
                                <span class="small text-danger text-truncate d-block" title="{{ $job->exception }}">
                                    {{ mb_substr($job->exception ?? '', 0, 160) }}{{ mb_strlen($job->exception ?? '') > 160 ? '…' : '' }}
                                </span>
                            </td>
                            <td class="bt-meta text-nowrap">
                                {{ \Carbon\Carbon::parse($job->failed_at)->format('d M Y, H:i') }}
                            </td>
                            <td>
                                <div class="bt-actions justify-content-end">
                                    <form method="POST" action="{{ route('admin.queue.retry', $job->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
                                            <x-icon name="repeat" height="13" width="13"/>
                                            @lang('Retry')
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.queue.forget', $job->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1"
                                                onclick="return confirm('{{ __('Delete this job?') }}')">
                                            <x-icon name="trash" height="13" width="13"/>
                                            @lang('Delete')
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($failedJobs->hasPages())
            <div class="bt-table-foot">{{ $failedJobs->links() }}</div>
        @endif
    @endif
</div>

{{-- Flush Confirmation Modal --}}
@if($count > 0)
    <div class="modal fade" id="flush-confirm-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4 px-3">
                    <div class="mb-3" style="color:var(--bt-danger);">
                        <x-icon name="warning-2" height="48" width="48"/>
                    </div>
                    <h5 class="fw-bold mb-1">@lang('Flush All Failed Jobs?')</h5>
                    <p class="text-muted small mb-4">
                        @lang('This permanently deletes all :count failed job(s). Cannot be undone.', ['count' => number_format($count)])
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-coreui-dismiss="modal">
                            @lang('Cancel')
                        </button>
                        <form method="POST" action="{{ route('admin.queue.flush') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1">
                                <x-icon name="trash" height="14" width="14"/>
                                @lang('Flush All')
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
