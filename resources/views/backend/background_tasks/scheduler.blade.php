@extends('backend.background_tasks.layout')

@section('title', __('Scheduler & Queue Guide'))
@section('bt_title', __('Scheduler & Queue Guide'))
@section('bt_icon', 'schedule')
@section('bt_subtitle', __('Instructions for configuring the Laravel scheduler and queue workers on your server.'))

@section('bt_content')

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="bt-card h-100">
            <div class="p-3 border-bottom" style="border-color: var(--bt-border) !important;">
                <div class="fw-bold small d-flex align-items-center gap-1">
                    <x-icon name="schedule" height="14" width="14" class="text-primary"/>
                    @lang('Cron Entry (required)')
                </div>
            </div>
            <div class="p-3">
                <div class="bt-meta mb-2">@lang('Add this to your server crontab') (<code>crontab -e</code>):</div>
                <div class="bt-code-card">
                    <code>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
                </div>
                <div class="bt-banner bt-banner--info mt-2">
                    <x-icon name="notification" height="13" width="13" class="flex-shrink-0 mt-1"/>
                    <span>@lang('All 3 commands run every minute via this single cron entry.')</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="bt-card h-100">
            <div class="p-3 border-bottom" style="border-color: var(--bt-border) !important;">
                <div class="fw-bold small d-flex align-items-center gap-1">
                    <x-icon name="work" height="14" width="14" class="text-primary"/>
                    @lang('Queue Worker')
                </div>
            </div>
            <div class="p-3">
                <div class="bt-meta mb-2">@lang('Start the worker on your server:')
                    <span class="ms-1 fw-semibold" style="color: var(--bt-text);">
                        @lang('Driver:') <code>{{ config('queue.default') }}</code>
                    </span>
                </div>
                <div class="bt-code-card">
                    <code>php artisan queue:work --queue=default --tries=3 --timeout=120</code>
                </div>
                <div class="bt-banner bt-banner--warning mt-2">
                    <x-icon name="warning" height="13" width="13" class="flex-shrink-0 mt-1"/>
                    <span>@lang('Use Supervisor in production to keep the worker running continuously.')</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="bt-card">
            <div class="p-3 border-bottom" style="border-color: var(--bt-border) !important;">
                <div class="fw-bold small d-flex align-items-center gap-1">
                    <x-icon name="apps" height="14" width="14" class="text-primary"/>
                    @lang('Scheduled Commands')
                </div>
            </div>
            <div class="table-responsive">
                <table class="bt-table bt-schedule-table">
                    <thead>
                        <tr>
                            <th>@lang('Signature')</th>
                            <th>@lang('Frequency')</th>
                            <th>@lang('Description')</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="small">wallet-earn:process</code></td>
                            <td><span class="bt-pill bt-pill--info">@lang('Every minute')</span></td>
                            <td class="bt-meta">@lang('Process due Wallet Earn reward payouts and matured principal returns.')</td>
                        </tr>
                        <tr>
                            <td><code class="small">p2p:promotions:expire</code></td>
                            <td><span class="bt-pill bt-pill--info">@lang('Every minute')</span></td>
                            <td class="bt-meta">@lang('Expire active P2P offer promotions that passed their end time.')</td>
                        </tr>
                        <tr>
                            <td><code class="small">p2p:orders:expire</code></td>
                            <td><span class="bt-pill bt-pill--info">@lang('Every minute')</span></td>
                            <td class="bt-meta">@lang('Expire pending P2P orders that passed expiry time and refund escrow.')</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                type="button"
                data-coreui-toggle="collapse"
                data-coreui-target="#supervisor-block"
                aria-expanded="false">
            <x-icon name="cil-settings" height="13" width="13"/>
            @lang('Show Supervisor Config')
        </button>
        <div class="collapse mt-2" id="supervisor-block">
            <div class="bt-card">
                <div class="p-3">
                    <div class="bt-meta mb-2">
                        @lang('Save as') <code>/etc/supervisor/conf.d/laravel-worker.conf</code>,
                        @lang('then run:') <code>supervisorctl reread && supervisorctl update && supervisorctl start laravel-worker:*</code>
                    </div>
                    <div class="bt-code-card">
                        <code>[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php {{ base_path() }}/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile={{ storage_path('logs/worker.log') }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
