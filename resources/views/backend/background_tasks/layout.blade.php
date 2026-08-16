@extends('backend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/background-tasks.css?v=' . config('app.version')) }}">
@endpush

@section('content')
<div class="bt-admin">

    {{-- Module Header --}}
    <div class="bt-module-header">
        <div class="bt-module-header__left">
            <h1 class="bt-module-title">
                <span class="bt-module-title__icon">
                    <x-icon name="{{ trim($__env->yieldContent('bt_icon', 'task')) ?: 'task' }}" height="22" width="22"/>
                </span>
                @yield('bt_title', __('Background Tasks'))
            </h1>
            <p class="bt-module-subtitle">
                @yield('bt_subtitle', __('Monitor and manually run scheduled background commands and manage queue operations.'))
            </p>
        </div>
        @hasSection('bt_action')
            <div class="bt-module-header__right">
                @yield('bt_action')
            </div>
        @endif
    </div>

    {{-- Shell Card: Tab Nav + Content --}}
    <div class="bt-shell">

        {{-- Tab Navigation --}}
        <div class="bt-shell__nav">
            <ul class="nav bt-tab-nav">
                @can('background-task-list')
                    <li class="nav-item">
                        <a class="nav-link bt-tab-nav__link {{ isActive('admin.background-tasks.index') }}"
                           href="{{ route('admin.background-tasks.index') }}">
                            <x-icon name="task" height="15" width="15"/>
                            @lang('Commands')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bt-tab-nav__link {{ isActive('admin.background-tasks.logs') }}"
                           href="{{ route('admin.background-tasks.logs') }}">
                            <x-icon name="history" height="15" width="15"/>
                            @lang('Run History')
                        </a>
                    </li>
                @endcan
                @can('queue-manage')
                    <li class="nav-item">
                        <a class="nav-link bt-tab-nav__link {{ isActive('admin.queue.failed') }}"
                           href="{{ route('admin.queue.failed') }}">
                            <x-icon name="warning-2" height="15" width="15"/>
                            @lang('Failed Jobs')
                        </a>
                    </li>
                @endcan
                @can('background-task-list')
                    <li class="nav-item">
                        <a class="nav-link bt-tab-nav__link {{ isActive('admin.background-tasks.scheduler') }}"
                           href="{{ route('admin.background-tasks.scheduler') }}">
                            <x-icon name="schedule" height="15" width="15"/>
                            @lang('Scheduler Guide')
                        </a>
                    </li>
                @endcan
            </ul>
        </div>

        {{-- Page Body --}}
        <div class="bt-shell__body">
            @yield('bt_content')
        </div>
    </div>

</div>
@endsection
