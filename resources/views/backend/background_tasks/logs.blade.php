@extends('backend.background_tasks.layout')

@section('title', __('Run History'))
@section('bt_title', __('Run History'))
@section('bt_icon', 'history')
@section('bt_subtitle', __('Full log of every manual and scheduled background task execution.'))

@section('bt_content')

{{-- Filters --}}
<form method="GET" action="{{ route('admin.background-tasks.logs') }}" class="bt-filter mb-3">
    <div class="bt-filter__group">
        <label>@lang('Command')</label>
        <select name="task_key" class="form-select">
            <option value="">@lang('All Commands')</option>
            @foreach($tasks as $key => $task)
                <option value="{{ $key }}" @selected(request('task_key') === $key)>{{ $task['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="bt-filter__group">
        <label>@lang('Status')</label>
        <select name="status" class="form-select">
            <option value="">@lang('All')</option>
            <option value="success" @selected(request('status') === 'success')>@lang('Success')</option>
            <option value="failed"  @selected(request('status') === 'failed')>@lang('Failed')</option>
            <option value="running" @selected(request('status') === 'running')>@lang('Running')</option>
        </select>
    </div>
    <div class="bt-filter__group">
        <label>@lang('Date')</label>
        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
    </div>
    <div class="bt-filter__group" style="align-self:flex-end;">
        <button type="submit" class="btn btn-primary">
            <x-icon name="filter" height="13" width="13"/>
            @lang('Filter')
        </button>
    </div>
    @if(request()->hasAny(['task_key','status','date']))
        <div class="bt-filter__group" style="align-self:flex-end;">
            <a href="{{ route('admin.background-tasks.logs') }}" class="btn btn-outline-secondary">
                @lang('Reset')
            </a>
        </div>
    @endif
</form>

<div class="bt-card">
    <div class="table-responsive">
        <table class="bt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('Command')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Output / Error')</th>
                    <th>@lang('Options')</th>
                    <th>@lang('Duration')</th>
                    <th>@lang('By')</th>
                    <th>@lang('Started At')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="bt-meta">{{ $log->id }}</td>
                        <td>
                            <div class="fw-semibold small">
                                {{ $tasks[$log->task_key]['label'] ?? $log->task_key }}
                            </div>
                            <code class="bt-meta">{{ $log->command_signature }}</code>
                        </td>
                        <td>
                            @if($log->isSuccess())
                                <span class="bt-pill bt-pill--success">@lang('Success')</span>
                            @elseif($log->isFailed())
                                <span class="bt-pill bt-pill--danger">@lang('Failed')</span>
                            @else
                                <span class="bt-pill bt-pill--warning">@lang('Running')</span>
                            @endif
                        </td>
                        <td style="max-width:240px;">
                            <span class="small text-truncate d-block {{ $log->isFailed() ? 'text-danger' : 'text-muted' }}"
                                  title="{{ $log->isFailed() ? $log->error_message : $log->output }}">
                                {{ $log->outputSummary() }}
                            </span>
                        </td>
                        <td>
                            @if($log->options)
                                @foreach($log->options as $k => $v)
                                    <span class="bt-pill bt-pill--info">{{ $k }}={{ $v }}</span>
                                @endforeach
                            @else
                                <span class="bt-meta">—</span>
                            @endif
                        </td>
                        <td class="text-nowrap bt-meta">{{ $log->durationLabel() }}</td>
                        <td class="bt-meta text-nowrap">
                            {{ $log->executor?->name ?? ($log->executed_by ? '#'.$log->executed_by : __('System')) }}
                        </td>
                        <td class="text-nowrap bt-meta">
                            <div>{{ $log->started_at?->format('d M, H:i:s') ?? '—' }}</div>
                            @if($log->finished_at)
                                <div>→ {{ $log->finished_at->format('H:i:s') }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-admin-not-found
                                :title="__('No log entries found')"
                                :message="__('Background task logs matching the current filters will appear here.')"
                                icon="fa-clock"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="bt-table-foot">{{ $logs->links() }}</div>
    @endif
</div>

@endsection
