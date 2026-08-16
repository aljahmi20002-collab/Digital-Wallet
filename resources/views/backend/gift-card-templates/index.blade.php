@extends('backend.layouts.app')
@section('title', __('Gift Card Templates'))

@section('content')
    @php
        $statusMap = [
            'active'   => ['label' => __('Active'),   'cls' => 'success'],
            'draft'    => ['label' => __('Draft'),    'cls' => 'warning'],
            'inactive' => ['label' => __('Inactive'), 'cls' => 'secondary'],
        ];
    @endphp

    {{--
        Wrapper for the whole template-manager page. The admin layout
        doesn't ship Alpine.js, so this view uses vanilla jQuery + the
        CoreUI Modal API (same toolkit as the rest of the admin) for
        the edit flow. The trailing inline script registers click
        listeners, drives the live preview, and swaps swatch / chip
        active classes.
    --}}
    <div class="gift-card-template-manager gift-card-admin"
         data-next-sort-order="{{ ($stats['total'] ?? 0) + 1 }}">

    {{-- Page header — matches All Gift Cards screen for visual parity. --}}
    <div class="gift-card-admin__header">
        <div class="gift-card-admin__header-text">
            <span class="gift-card-admin__eyebrow">
                <i class="fa-solid fa-palette" aria-hidden="true"></i>
                {{ __('Catalog') }}
            </span>
            <h1 class="gift-card-admin__title">{{ __('Gift Card Templates') }}</h1>
            <p class="gift-card-admin__subtitle">{{ __('Manage the designs available to users when creating a gift card.') }}</p>
        </div>
        <div class="gift-card-admin__header-actions">
            @can('gift-card-list')
                <a href="{{ route('admin.gift-cards.index') }}" class="btn btn-light gift-card-admin__action gift-card-admin__action--ghost">
                    <x-icon name="apps" width="16" height="16"/>
                    <span>{{ __('All Gift Cards') }}</span>
                </a>
            @endcan
            @can('gift-card-template-manage')
                <button type="button"
                        class="btn btn-primary gift-card-admin__action js-gctm-create">
                    <x-icon name="add" width="16" height="16"/>
                    <span>{{ __('Add Template') }}</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- KPI strip --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#DBEAFE; --kpi-fg:#1D4ED8;">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="gift-card-admin-kpi__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M7.5 8a1 1 0 1 1 0 .01M16 8a1 1 0 1 1 0 .01M9 15a1 1 0 1 1 0 .01M15 16a1 1 0 1 1 0 .01"/>
                            <path d="M12 3a4 4 0 0 0 4 4M12 21a4 4 0 0 1-4-4"/>
                        </svg>
                    </span>
                    <div>
                        <div class="gift-card-admin-kpi__label">{{ __('Total templates') }}</div>
                        <div class="gift-card-admin-kpi__value">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#DCFCE7; --kpi-fg:#15803D;">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="gift-card-admin-kpi__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <div>
                        <div class="gift-card-admin-kpi__label">{{ __('Active') }}</div>
                        <div class="gift-card-admin-kpi__value">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#FEF3C7; --kpi-fg:#92400E;">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="gift-card-admin-kpi__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"/>
                            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4z"/>
                        </svg>
                    </span>
                    <div>
                        <div class="gift-card-admin-kpi__label">{{ __('Drafts') }}</div>
                        <div class="gift-card-admin-kpi__value">{{ number_format($stats['drafts']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#FCE7F3; --kpi-fg:#BE185D;">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="gift-card-admin-kpi__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 12 20 22 4 22 4 12"/>
                            <rect x="2" y="7" width="20" height="5"/>
                            <line x1="12" y1="22" x2="12" y2="7"/>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                        </svg>
                    </span>
                    <div>
                        <div class="gift-card-admin-kpi__label">{{ __('Cards sent this month') }}</div>
                        <div class="gift-card-admin-kpi__value">{{ number_format($stats['sent_mtd']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table — full width. The editor is a Bootstrap modal that
         lives outside the row so it doesn't reserve column space
         while it's hidden. --}}
    <div class="row g-3 align-items-start">
        <div class="col-12">
            <div class="card border-0 gift-card-admin__card">
                <div class="card-body">
                    {{-- Toolbar --}}
                    <form method="GET" class="gift-card-admin__filter mb-3">
                        <div class="gift-card-admin__filter-search">
                            <i class="fa-solid fa-magnifying-glass gift-card-admin__filter-search-icon" aria-hidden="true"></i>
                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   class="form-control gift-card-admin__filter-input"
                                   placeholder="{{ __('Search templates by name') }}">
                        </div>
                        <select name="status" class="form-select gift-card-admin__filter-select">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach($statusMap as $key => $meta)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary gift-card-admin__filter-submit">
                            <x-icon name="filter" width="16" height="16"/>
                            <span>{{ __('Filter') }}</span>
                        </button>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 gc-admin-table">
                            <thead>
                                <tr>
                                    @can('gift-card-template-manage')
                                        <th class="gift-card-template-manager__drag-col text-center"><span class="visually-hidden">{{ __('Reorder') }}</span></th>
                                    @endcan
                                    <th>{{ __('Design') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Used') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody
                                id="gift-card-template-sortable"
                                data-position-url="{{ route('admin.gift-card-templates.position-update') }}"
                                data-csrf-token="{{ csrf_token() }}"
                                data-success-message="{{ __('Template order updated successfully.') }}"
                                data-error-message="{{ __('Unable to update template order right now.') }}"
                            >
                                @forelse($templates as $t)
                                    @php
                                        $meta = $statusMap[$t->status] ?? ['label' => $t->status, 'cls' => 'secondary'];
                                        /*
                                         * Pre-compute the edit payload here. Inlining it as
                                         * @json($t->only([...])) inside @click breaks Blade's
                                         * @json compiler — it splits the expression on commas
                                         * (because @json supports a second flags arg) and
                                         * truncates the array at the first inner comma.
                                         */
                                        $editPayload = $t->only([
                                            'id', 'name', 'category', 'preset_key',
                                            'background_color', 'text_color', 'ribbon_text',
                                            'default_amount', 'status', 'sort_order',
                                        ]);
                                    @endphp
                                    <tr data-id="{{ $t->id }}">
                                        @can('gift-card-template-manage')
                                            <td class="text-center gift-card-template-manager__drag-cell">
                                                <span class="gift-card-template-manager__drag-grip" title="{{ __('Drag to sort') }}" data-coreui-toggle="tooltip">
                                                    <i class="fa-solid fa-grip-vertical drag-handle" aria-hidden="true"></i>
                                                </span>
                                            </td>
                                        @endcan
                                        <td style="width:106px;">
                                            <div class="gift-card-admin-thumb">
                                                <x-gift-card-design :preset="$t->preset_key" :amount="$t->default_amount ?: 50" recipient="—" sender="—" :width="84"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="gc-admin-person">
                                                <span class="gc-admin-person__name">{{ $t->name }}</span>
                                                <span class="gc-admin-person__meta">#GC-{{ str_pad($t->id, 3, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="gc-admin-category">{{ $t->category }}</span>
                                        </td>
                                        <td>
                                            <span class="gc-admin-pill gc-admin-pill--{{ $meta['cls'] }}">
                                                <span class="gc-admin-pill__dot" aria-hidden="true"></span>
                                                {{ $meta['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="gc-admin-used">
                                                <span class="gc-admin-used__count">{{ number_format($t->used_count) }}</span>
                                                <span class="gc-admin-used__label">{{ __('cards') }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="gc-admin-actions">
                                                @can('gift-card-template-manage')
                                                    <button type="button"
                                                            class="btn btn-outline-primary btn-sm gc-admin-btn js-gctm-edit"
                                                            data-template='@json($editPayload)'>
                                                        <x-icon name="edit" width="14" height="14"/>
                                                        <span>{{ __('Edit') }}</span>
                                                    </button>
                                                    {{--
                                                        Use the project-wide delete handler defined in
                                                        public/general/js/helpers.js — it listens for
                                                        clicks on .delete, reads data-url, points the
                                                        global #delete-form-modal at it and pops
                                                        #delete_modal. Matches blog/role/page admin.
                                                    --}}
                                                    <a href="javascript:void(0)"
                                                       data-url="{{ route('admin.gift-card-templates.destroy', $t->id) }}"
                                                       class="btn btn-outline-danger btn-sm gc-admin-btn delete">
                                                        <x-icon name="delete-3" width="14" height="14"/>
                                                        <span>{{ __('Delete') }}</span>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">{{ __('Read-only') }}</span>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="@can('gift-card-template-manage') 7 @else 6 @endcan">
                                            <x-admin-not-found
                                                :title="__('No templates yet')"
                                                :message="__('Add your first design so users can pick from a catalog when creating a gift card.')"
                                                icon="fa-palette"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                                @can('gift-card-template-manage')
                                    <tr class="gift-card-template-manager__drag-hint" data-drag-hint>
                                        <td colspan="7">
                                            <div class="gift-card-template-manager__drag-hint-inner">
                                                <i class="fa-solid fa-arrows-up-down"></i>
                                                <span>{{ __('Drag rows by the grip icon to reorder gift card templates.') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endcan
                            </tbody>
                        </table>
                    </div>

                    @if($templates->hasPages())
                        <div class="mt-3">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div> {{-- /.row table-only --}}

    {{--
        Edit / Create template modal.
        Lives outside the .row so it never reserves layout space
        while hidden — Bootstrap's .modal style handles that with
        display:none. Opens whenever Alpine `editing` becomes
        non-null (see the x-effect handler below).
    --}}
    <div class="modal fade gift-card-template-modal"
         id="giftCardTemplateModal"
         tabindex="-1"
         aria-labelledby="giftCardTemplateModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg gift-card-template-modal__dialog">
            <div class="modal-content shadow-lg">

                {{-- Header: eyebrow + dynamic title + close --}}
                <div class="modal-header gift-card-template-modal__head">
                    <div class="gift-card-template-modal__head-text">
                        <span class="gift-card-template-modal__eyebrow js-gctm-eyebrow">{{ __('New template') }}</span>
                        <h5 class="gift-card-template-modal__title js-gctm-heading"
                            id="giftCardTemplateModalLabel">{{ __('New template') }}</h5>
                    </div>
                    <button type="button"
                            class="btn-close gift-card-template-modal__close"
                            data-coreui-dismiss="modal"
                            aria-label="{{ __('Close') }}"></button>
                </div>

                <form action="{{ route('admin.gift-card-templates.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="js-gctm-form"
                      data-store-url="{{ route('admin.gift-card-templates.store') }}"
                      data-update-base="{{ url('admin/gift-card-templates') }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST" class="js-gctm-method">

                    <div class="modal-body gift-card-template-modal__body">

                        {{-- Live preview --}}
                        <div class="gift-card-template-modal__preview js-gctm-preview"></div>

                        {{-- Background image — project-standard image uploader.
                             Uses the same uploader component blog/category/etc.
                             reach for, so behaviour is shared with the global
                             jQuery handleImagePreview() handler. --}}
                        <div class="gift-card-template-modal__field">
                            <label class="gift-card-template-modal__label">{{ __('Background image') }}</label>
                            <x-img name="image"/>
                        </div>

                        {{-- Name + Category --}}
                        <div class="gift-card-template-modal__row">
                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label" for="gctm-name">{{ __('Name') }}</label>
                                <input type="text"
                                       id="gctm-name"
                                       name="name"
                                       class="form-control gift-card-template-modal__input js-gctm-name"
                                       required>
                            </div>
                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label" for="gctm-category">{{ __('Category') }}</label>
                                <select id="gctm-category"
                                        name="category"
                                        class="form-select gift-card-template-modal__input">
                                    @foreach(\App\Models\GiftCardTemplate::CATEGORIES as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Design preset + Ribbon text --}}
                        <div class="gift-card-template-modal__row">
                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label" for="gctm-preset">{{ __('Design preset') }}</label>
                                <select id="gctm-preset"
                                        name="preset_key"
                                        class="form-select gift-card-template-modal__input js-gctm-preset">
                                    @foreach(\App\Models\GiftCardTemplate::PRESETS as $p)
                                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label" for="gctm-ribbon">{{ __('Ribbon text') }}</label>
                                <input type="text"
                                       id="gctm-ribbon"
                                       name="ribbon_text"
                                       class="form-control gift-card-template-modal__input js-gctm-ribbon"
                                       placeholder="{{ __('A Gift For You') }}">
                            </div>
                        </div>

                        {{--
                            Color pickers — premium two-row design.
                            Top row shows a large chip + the resolved hex (or
                            "AUTO" when the picker is in fallback mode) and a
                            clear button for resetting to AUTO. Bottom row is
                            the preset swatches followed by a custom-color
                            picker that opens the browser's native picker
                            (input type=color) and reads back live as the user
                            drags through the gradient.
                        --}}
                        <div class="gift-card-template-modal__row">

                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label">
                                    <span>{{ __('Background color') }}</span>
                                    <span class="gctm-color-picker__hint">{{ __('Overrides the preset gradient') }}</span>
                                </label>
                                <div class="gctm-color-picker" data-target="background_color">
                                    <div class="gctm-color-picker__display">
                                        <span class="gctm-color-picker__chip js-gctm-swatch-preview" data-target="background_color"></span>
                                        <div class="gctm-color-picker__meta">
                                            <span class="gctm-color-picker__meta-label">{{ __('Current') }}</span>
                                            <code class="gctm-color-picker__hex js-gctm-swatch-code" data-target="background_color">AUTO</code>
                                        </div>
                                        <button type="button"
                                                class="gctm-color-picker__reset js-gctm-color-reset"
                                                data-target="background_color"
                                                title="{{ __('Reset to preset gradient') }}"
                                                aria-label="{{ __('Reset to preset gradient') }}">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                                <polyline points="3 4 3 10 9 10"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="gctm-color-picker__swatches">
                                        @foreach(['#FB7185', '#3B82F6', '#10B981', '#FBBF24', '#8B5CF6', '#0F172A'] as $color)
                                            <button type="button"
                                                    class="gctm-color-picker__swatch js-gctm-swatch"
                                                    data-target="background_color"
                                                    data-value="{{ $color }}"
                                                    style="--swatch:{{ $color }}"
                                                    aria-label="{{ $color }}">
                                                <svg class="gctm-color-picker__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                            </button>
                                        @endforeach
                                        <label class="gctm-color-picker__custom"
                                               data-target="background_color"
                                               title="{{ __('Pick a custom color') }}">
                                            <input type="color"
                                                   class="gctm-color-picker__native js-gctm-native"
                                                   data-target="background_color"
                                                   value="#FB7185">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <line x1="12" y1="5" x2="12" y2="19"/>
                                                <line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" name="background_color" class="js-gctm-input" data-target="background_color">
                            </div>

                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label">
                                    <span>{{ __('Text color') }}</span>
                                    <span class="gctm-color-picker__hint">{{ __('Defaults to preset ink') }}</span>
                                </label>
                                <div class="gctm-color-picker" data-target="text_color">
                                    <div class="gctm-color-picker__display">
                                        <span class="gctm-color-picker__chip js-gctm-swatch-preview" data-target="text_color"></span>
                                        <div class="gctm-color-picker__meta">
                                            <span class="gctm-color-picker__meta-label">{{ __('Current') }}</span>
                                            <code class="gctm-color-picker__hex js-gctm-swatch-code" data-target="text_color">#FFFFFF</code>
                                        </div>
                                        <button type="button"
                                                class="gctm-color-picker__reset js-gctm-color-reset"
                                                data-target="text_color"
                                                title="{{ __('Reset to preset ink') }}"
                                                aria-label="{{ __('Reset to preset ink') }}">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                                <polyline points="3 4 3 10 9 10"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="gctm-color-picker__swatches">
                                        @foreach(['#FFFFFF', '#0F172A', '#FCD34D', '#FECDD3'] as $color)
                                            <button type="button"
                                                    class="gctm-color-picker__swatch js-gctm-swatch"
                                                    data-target="text_color"
                                                    data-value="{{ $color }}"
                                                    style="--swatch:{{ $color }}"
                                                    aria-label="{{ $color }}">
                                                <svg class="gctm-color-picker__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                            </button>
                                        @endforeach
                                        <label class="gctm-color-picker__custom"
                                               data-target="text_color"
                                               title="{{ __('Pick a custom color') }}">
                                            <input type="color"
                                                   class="gctm-color-picker__native js-gctm-native"
                                                   data-target="text_color"
                                                   value="#FFFFFF">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <line x1="12" y1="5" x2="12" y2="19"/>
                                                <line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" name="text_color" class="js-gctm-input" data-target="text_color">
                            </div>

                        </div>

                        {{--
                            Status + Default amount row.
                              · Status: toggle-switch UI matching the admin
                                settings page (settings-site-switch-card).
                                ON = active, OFF = inactive. Drafts surface
                                as OFF; sort order moved to drag-and-drop.
                              · Default amount: optional preset that
                                pre-fills the gift card amount when a user
                                picks this design. Empty = no suggestion
                                (user types their own).
                        --}}
                        <div class="gift-card-template-modal__row">
                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label">{{ __('Status') }}</label>
                                <div class="settings-site-switch-control gift-card-template-modal__switch">
                                    <input type="hidden" name="status" class="js-gctm-input" data-target="status" value="active">
                                    <label class="settings-site-switch-card" for="gctm-status-switch">
                                        <input class="form-check-input coevs-switch settings-site-switch-card__input js-gctm-status"
                                               type="checkbox"
                                               role="switch"
                                               id="gctm-status-switch">
                                        <span class="settings-site-switch-card__track" aria-hidden="true"></span>
                                        <span class="settings-site-switch-card__meta">
                                            <span class="settings-site-switch-card__state settings-site-switch-card__state--enabled">{{ __('Active') }}</span>
                                            <span class="settings-site-switch-card__state settings-site-switch-card__state--disabled">{{ __('Inactive') }}</span>
                                        </span>
                                    </label>
                                </div>
                                {{-- Matching hint slot — gives Status the same
                                     label + control + hint stack as the Default
                                     amount field on its right, so the two
                                     columns end at the exact same height. --}}
                                <div class="gift-card-template-modal__field-hint">{{ __('Toggle to hide from the user create page') }}</div>
                            </div>

                            <div class="gift-card-template-modal__field">
                                <label class="gift-card-template-modal__label" for="gctm-default-amount">{{ __('Default amount') }}</label>
                                <div class="gift-card-template-modal__amount">
                                    <span class="gift-card-template-modal__amount-symbol">{{ siteCurrency('symbol') ?? '$' }}</span>
                                    <input type="number"
                                           id="gctm-default-amount"
                                           name="default_amount"
                                           inputmode="decimal"
                                           step="0.01"
                                           min="1"
                                           class="form-control gift-card-template-modal__input js-gctm-default-amount"
                                           placeholder="50.00">
                                </div>
                                <div class="gift-card-template-modal__field-hint">{{ __('Optional · prefills on selection') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer gift-card-template-modal__foot">
                        <button type="button" class="btn btn-light" data-coreui-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>{{ __('Save changes') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div> {{-- /.gift-card-template-manager wrapper --}}

    @push('styles')
        <style>
            /* ──────────────────────────────────────────────────────────
               Gift Card admin — premium polish shared with All Gift
               Cards page. Scoped to .gift-card-admin so the global
               admin layout is untouched.
               ────────────────────────────────────────────────────────── */

            /* Section header */
            .gift-card-admin__header {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 16px;
                flex-wrap: wrap;
                padding: 18px 0 22px;
            }
            .gift-card-admin__header-text { min-width: 0; }
            .gift-card-admin__header-actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                align-items: center;
            }
            .gift-card-admin__eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 10.5px;
                font-weight: 800;
                color: #1D4ED8;
                background: #EFF6FF;
                padding: 4px 10px;
                border-radius: 999px;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                margin-bottom: 8px;
            }
            .gift-card-admin__eyebrow i { font-size: 10px; }
            .gift-card-admin__title {
                margin: 0;
                font-size: 1.45rem;
                font-weight: 800;
                color: #0F172A;
                letter-spacing: -0.02em;
                line-height: 1.15;
            }
            .gift-card-admin__subtitle {
                margin: 6px 0 0;
                color: #64748B;
                font-size: 0.825rem;
                font-weight: 500;
            }
            .gift-card-admin__action {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 9px 16px;
                font-size: 0.825rem;
                font-weight: 700;
                border-radius: 10px;
                line-height: 1;
            }
            .gift-card-admin__action:not(.gift-card-admin__action--ghost) {
                box-shadow: 0 4px 12px rgba(29, 78, 216, 0.18);
            }
            .gift-card-admin__action--ghost {
                background: #F1F5F9;
                color: #1E293B;
                border: 1px solid #E2E8F0;
            }
            .gift-card-admin__action--ghost:hover {
                background: #E2E8F0;
                color: #0F172A;
            }

            /* KPI cards */
            .gift-card-admin-kpi {
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 1px 1px rgba(15, 23, 42, .03);
                border-radius: 14px;
                transition: transform 0.18s ease, box-shadow 0.18s ease;
            }
            .gift-card-admin-kpi:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 24px -12px rgba(15, 23, 42, 0.12), 0 4px 8px -4px rgba(15, 23, 42, 0.06);
            }
            .gift-card-admin-kpi__icon {
                display: inline-grid;
                place-items: center;
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: var(--kpi-bg, #F1F5F9);
                color: var(--kpi-fg, #475569);
                flex-shrink: 0;
            }
            .gift-card-admin-kpi__icon svg { width: 22px; height: 22px; }
            .gift-card-admin-kpi__label {
                font-size: 11.5px;
                color: #64748B;
                font-weight: 700;
                line-height: 1.2;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }
            .gift-card-admin-kpi__value {
                font-size: 22px;
                font-weight: 800;
                letter-spacing: -0.02em;
                color: #0F172A;
                line-height: 1.15;
                margin-top: 4px;
                font-variant-numeric: tabular-nums;
            }

            /* Card wrapper */
            .gift-card-admin__card {
                border-radius: 14px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 1px 1px rgba(15, 23, 42, .03);
            }
            .gift-card-admin__card > .card-body {
                padding: 18px 20px 20px;
            }

            /* Filter bar */
            .gift-card-admin__filter {
                display: grid;
                grid-template-columns: 1fr auto auto;
                gap: 10px;
                align-items: stretch;
            }
            @media (max-width: 575.98px) {
                .gift-card-admin__filter { grid-template-columns: 1fr; }
            }
            .gift-card-admin__filter-search { position: relative; }
            .gift-card-admin__filter-search-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #94A3B8;
                font-size: 13px;
                pointer-events: none;
            }
            .gift-card-admin__filter-input {
                height: 40px;
                padding-left: 38px;
                border-radius: 10px;
                border-color: #E6EAF3;
                font-size: 0.85rem;
                box-shadow: none;
            }
            .gift-card-admin__filter-input:focus,
            .gift-card-admin__filter-select:focus {
                border-color: #93C5FD;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
            }
            .gift-card-admin__filter-select {
                height: 40px;
                min-width: 160px;
                border-radius: 10px;
                border-color: #E6EAF3;
                font-size: 0.85rem;
                font-weight: 600;
                color: #1E293B;
                box-shadow: none;
            }
            .gift-card-admin__filter-submit {
                height: 40px;
                padding: 0 18px;
                border-radius: 10px;
                font-size: 0.825rem;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            /* ─── Table ────────────────────────────────────────────────── */
            .gc-admin-table { margin: 0; }
            .gc-admin-table thead th {
                background: #F8FAFC;
                color: #64748B;
                font-size: 10.5px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                padding: 12px 14px;
                border-bottom: 1px solid #E6EAF3;
                border-top: 0;
                white-space: nowrap;
            }
            .gc-admin-table thead th:first-child { border-top-left-radius: 10px; }
            .gc-admin-table thead th:last-child  { border-top-right-radius: 10px; }
            .gc-admin-table tbody td {
                padding: 14px;
                vertical-align: middle;
                border-top: 1px solid #EEF1F7;
                font-size: 0.85rem;
                color: #0F172A;
            }
            .gc-admin-table tbody tr {
                transition: background-color 0.12s ease;
            }
            .gc-admin-table tbody tr:hover td {
                background-color: #FAFBFE;
            }
            /* Don't highlight the trailing drag-hint row on hover —
               it isn't a real row. */
            .gc-admin-table tbody tr.gift-card-template-manager__drag-hint:hover td {
                background-color: transparent;
            }

            /* Person cell (name + meta) reused for template name */
            .gc-admin-person { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
            .gc-admin-person__name {
                font-weight: 700;
                color: #0F172A;
                font-size: 0.875rem;
                line-height: 1.25;
                letter-spacing: -0.005em;
            }
            .gc-admin-person__meta {
                color: #94A3B8;
                font-size: 0.7rem;
                font-weight: 600;
                line-height: 1.2;
                font-variant-numeric: tabular-nums;
            }

            /* Category chip — quiet pill that doesn't fight the design
               thumbnail for attention. */
            .gc-admin-category {
                display: inline-flex;
                align-items: center;
                padding: 4px 10px;
                border-radius: 999px;
                background: #EEF2FF;
                color: #4338CA;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.01em;
            }

            /* Used-count column */
            .gc-admin-used { display: inline-flex; align-items: baseline; gap: 4px; }
            .gc-admin-used__count {
                font-weight: 800;
                font-size: 0.9rem;
                color: #0F172A;
                font-variant-numeric: tabular-nums;
            }
            .gc-admin-used__label {
                color: #94A3B8;
                font-size: 0.72rem;
                font-weight: 600;
            }

            /* Status pill — soft pastel + dot indicator. */
            .gc-admin-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px 4px 8px;
                border-radius: 999px;
                font-size: 0.72rem;
                font-weight: 700;
                line-height: 1;
                letter-spacing: 0.01em;
                white-space: nowrap;
            }
            .gc-admin-pill__dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: currentColor;
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.6);
                flex-shrink: 0;
            }
            .gc-admin-pill--success  { background: #DCFCE7; color: #15803D; }
            .gc-admin-pill--warning  { background: #FEF3C7; color: #92400E; }
            .gc-admin-pill--danger   { background: #FEE2E2; color: #B91C1C; }
            .gc-admin-pill--info     { background: #DBEAFE; color: #1D4ED8; }
            .gc-admin-pill--primary  { background: #EDE9FE; color: #6D28D9; }
            .gc-admin-pill--secondary{ background: #E2E8F0; color: #475569; }

            /* Compact outlined action buttons */
            .gc-admin-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
            .gc-admin-btn {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 6px 11px;
                font-size: 0.74rem;
                font-weight: 700;
                border-radius: 8px;
                line-height: 1;
                border-width: 1px;
            }
            .gc-admin-btn svg { flex-shrink: 0; }
            .gc-admin-btn.btn-outline-primary { border-color: #DBEAFE; color: #1D4ED8; background: #fff; }
            .gc-admin-btn.btn-outline-primary:hover {
                background: #1D4ED8;
                color: #fff;
                border-color: #1D4ED8;
                box-shadow: 0 4px 10px rgba(29, 78, 216, 0.22);
            }
            .gc-admin-btn.btn-outline-danger { border-color: #FECACA; color: #B91C1C; background: #fff; }
            .gc-admin-btn.btn-outline-danger:hover {
                background: #DC2626;
                color: #fff;
                border-color: #DC2626;
                box-shadow: 0 4px 10px rgba(220, 38, 38, 0.22);
            }

            /* Design thumb cell — tight premium card preview frame.
               Padding intentionally minimal (~3px) so the gradient
               backdrop reads as a hairline frame rather than a thick
               border. Inner card width 84 × aspect ratio (~1.586:1)
               ≈ 53px tall — fits inside the content area
               (96 - 2*3 = 90 wide × 62 - 2*3 = 56 tall) with a couple
               of px breathing room on each side so the 1px frame
               border stays visible on all 4 edges. */
            .gift-card-admin-thumb {
                width: 96px;
                height: 62px;
                border-radius: 8px;
                overflow: hidden;
                display: grid;
                place-items: center;
                padding: 3px;
                background: linear-gradient(180deg, #F8FAFC 0%, #EEF2FF 100%);
                border: 1px solid #E6EAF3;
                box-sizing: border-box;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                position: relative;
            }
            .gift-card-admin-thumb .gc-card {
                /* Reduced shadow to match the tighter frame — keeps
                   the card visually lifted without bleeding past
                   the thumbnail's edges. */
                box-shadow: 0 2px 6px -2px rgba(15, 23, 42, 0.20) !important;
            }

            /* Drag-to-sort column — narrow, centered grip with a soft
               background chip that lights up on hover. */
            .gift-card-template-manager__drag-col { width: 44px; }
            .gift-card-template-manager__drag-cell { width: 44px; padding-left: 8px !important; padding-right: 8px !important; }
            .gift-card-template-manager__drag-grip {
                display: inline-grid;
                place-items: center;
                width: 28px;
                height: 28px;
                border-radius: 8px;
                background: #F1F5F9;
                color: #94A3B8;
                cursor: grab;
                transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
            }
            .gc-admin-table tbody tr:hover .gift-card-template-manager__drag-grip {
                background: #DBEAFE;
                color: #1D4ED8;
            }
            .gift-card-template-manager__drag-grip:active { cursor: grabbing; transform: scale(0.96); }
            .gift-card-template-manager__drag-grip .drag-handle { font-size: 13px; }

            /* Sortable ghost / drag preview */
            #gift-card-template-sortable .sortable-ghost { background: #EFF6FF !important; }
            #gift-card-template-sortable .sortable-ghost td { background: #EFF6FF !important; }
            #gift-card-template-sortable .sortable-chosen { box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.25); }

            /* Drag-hint row — sits at the bottom of the table as a soft
               instructional banner. Not a real row, so it doesn't get
               the standard hover tint or border treatment. */
            .gift-card-template-manager__drag-hint td {
                background: linear-gradient(180deg, #F8FAFC 0%, #F1F5F9 100%);
                border-top: 1px dashed #E2E8F0;
                padding: 12px 16px;
            }
            .gift-card-template-manager__drag-hint-inner {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 0.74rem;
                font-weight: 600;
                color: #64748B;
                width: 100%;
                justify-content: center;
            }
            .gift-card-template-manager__drag-hint-inner i {
                color: #94A3B8;
                font-size: 0.78rem;
            }

            /* Toggle switch reused from the admin settings page — same
               visual track, same enabled/disabled label pair. The shared
               .settings-site-switch-card already renders its own bordered
               pill, so we DON'T wrap it in another bordered container
               (that produced a visible double-border). The wrapper just
               enforces a min-height that matches the amount input on the
               right so both columns sit on the same baseline. */
            .gift-card-template-modal__switch {
                margin-top: 0;
                display: flex;
                align-items: stretch;
                min-height: 44px;
            }
            .gift-card-template-modal__switch .settings-site-switch-card {
                width: 100%;
                margin: 0;
            }

            /* Default amount input — symbol prefix on the left, numeric
               input fills the rest. Sits at the same visual weight as the
               status switch so the row reads balanced. */
            .gift-card-template-modal__amount {
                position: relative;
            }
            .gift-card-template-modal__amount-symbol {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.95rem;
                font-weight: 800;
                color: #64748B;
                pointer-events: none;
                font-variant-numeric: tabular-nums;
            }
            .gift-card-template-modal__amount .js-gctm-default-amount {
                padding-left: 28px;
                font-variant-numeric: tabular-nums;
                font-weight: 700;
            }
            /* Small helper text shown directly under an input — softer,
               smaller, and dimmer than the label so it reads as a
               secondary cue rather than a competing field. */
            .gift-card-template-modal__field-hint {
                font-size: 10.5px;
                font-weight: 400;
                color: #B0BAC9;
                margin-top: 5px;
                line-height: 1.35;
                letter-spacing: 0.01em;
            }
            /* Each field in a 2-column row uses the same label + control +
               hint stack, top-aligned. Both columns end at the same
               baseline because every field has a hint slot (the Status
               hint is intentionally added for layout symmetry, even
               though the label "Status" alone is self-explanatory). */
            .gift-card-template-modal__row > .gift-card-template-modal__field {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
            }

            /* ─── Edit / Create template modal ───────────────────────── */
            .gift-card-template-modal .modal-content {
                border: 0;
                border-radius: 18px;
                overflow: hidden;
            }

            /* Header */
            .gift-card-template-modal__head {
                padding: 16px 24px 12px;
                border: 0;
                border-bottom: 1px solid #EEF1F7;
                align-items: flex-start;
            }
            .gift-card-template-modal__head-text { min-width: 0; }
            .gift-card-template-modal__eyebrow {
                display: block;
                font-size: 10.5px;
                font-weight: 800;
                color: #1D4ED8;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                margin-bottom: 4px;
            }
            .gift-card-template-modal__title {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 800;
                color: #0F172A;
                letter-spacing: -0.01em;
                line-height: 1.2;
            }
            .gift-card-template-modal__close {
                opacity: 0.55;
                margin: 4px 0 0;
                transition: opacity 0.15s ease;
            }
            .gift-card-template-modal__close:hover,
            .gift-card-template-modal__close:focus { opacity: 1; }

            /* Body & spacing — modal-lg gives us ~800px to work with so
               the 2-column rows now sit comfortably without label wrap. */
            .gift-card-template-modal__dialog { max-width: 760px; }
            .gift-card-template-modal__body {
                padding: 4px 24px 20px;
                display: grid;
                gap: 16px;
            }
            .gift-card-template-modal__row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            .gift-card-template-modal__field { min-width: 0; }
            /* Default to a clean stacked label — vertical title + hint.
               The earlier inline-flex layout was wrapping awkwardly at
               narrow column widths. Block-stack reads at any width. */
            .gift-card-template-modal__label {
                display: block;
                font-size: 12px;
                font-weight: 700;
                color: #1E293B;
                margin-bottom: 6px;
                letter-spacing: -0.003em;
            }
            .gift-card-template-modal__input {
                font-size: 0.875rem;
                padding: 9px 12px;
                border-radius: 9px;
                border-color: #E6EAF3;
                min-height: 38px;
            }
            .gift-card-template-modal__input:focus {
                border-color: #93C5FD;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
            }

            /* Preview card area — compact frame with a proper soft drop
               shadow on the card so it reads as a physical object lifted
               from the gradient backdrop. We avoid double-shadowing: the
               wrapper itself is borderless/shadowless, all elevation
               lives on .gc-card via box-shadow. */
            .gift-card-template-modal__preview {
                background: linear-gradient(180deg, #F4F6FB 0%, #EEF2FF 100%);
                border-radius: 12px;
                padding: 14px;
                border: 1px solid #E6EAF3;
                display: grid;
                place-items: center;
            }
            .gift-card-template-modal__preview .gc-card {
                transform: scale(0.78);
                transform-origin: center;
                margin: -22px 0; /* offset the visual height taken by scale */
                box-shadow:
                    0 18px 32px -14px rgba(15, 23, 42, 0.30),
                    0 8px 16px -8px rgba(15, 23, 42, 0.18),
                    inset 0 0 0 1px rgba(255, 255, 255, 0.10) !important;
            }

            /* File upload — drag-drop style */
            .gift-card-template-modal__upload {
                display: grid;
                grid-template-columns: auto 1fr auto;
                gap: 12px;
                align-items: center;
                padding: 10px 12px;
                border: 1.5px dashed #CBD5E1;
                border-radius: 11px;
                background: #F8FAFC;
                cursor: pointer;
                transition: border-color 0.15s ease, background-color 0.15s ease;
                margin: 0;
            }
            .gift-card-template-modal__upload:hover {
                border-color: #93C5FD;
                background: #EFF6FF;
            }
            .gift-card-template-modal__upload-thumb {
                display: inline-grid;
                place-items: center;
                width: 38px;
                height: 38px;
                border-radius: 8px;
                background: #fff;
                border: 1px solid #E6EAF3;
                color: #1D4ED8;
                flex-shrink: 0;
            }
            .gift-card-template-modal__upload-thumb svg { width: 18px; height: 18px; }
            .gift-card-template-modal__upload-meta {
                display: flex;
                flex-direction: column;
                gap: 1px;
                min-width: 0;
                line-height: 1.25;
            }
            .gift-card-template-modal__upload-name {
                font-size: 0.82rem;
                font-weight: 700;
                color: #0F172A;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .gift-card-template-modal__upload-size {
                font-size: 0.7rem;
                color: #64748B;
            }
            .gift-card-template-modal__upload-action {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 11px;
                font-size: 0.72rem;
                font-weight: 700;
                background: #fff;
                border: 1px solid #E6EAF3;
                border-radius: 7px;
                color: #1E293B;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                flex-shrink: 0;
            }

            /* ─── Premium color picker ──────────────────────────────────
               Two-row layout: display chip + hex + reset on top, swatches
               + custom-color trigger on the bottom. Designed to feel like
               a single tactile control card rather than a loose row of
               buttons.

               Labels are deliberately a vertical stack (title + hint)
               instead of a horizontal flex — at narrow column widths a
               flex label wraps onto 2 lines and looks broken. The hint
               sits as a smaller, dimmer subtitle directly under the
               title which reads cleanly at any width.
            */
            .gift-card-template-modal__label {
                display: flex;
                flex-direction: column;
                gap: 2px;
                align-items: flex-start;
            }
            .gift-card-template-modal__label > span:first-child {
                color: #1E293B;
                font-weight: 700;
            }
            .gctm-color-picker__hint {
                font-size: 11px;
                font-weight: 500;
                color: #94A3B8;
                letter-spacing: 0;
                text-transform: none;
                line-height: 1.2;
            }

            .gctm-color-picker {
                border: 1px solid #E6EAF3;
                border-radius: 10px;
                background: #fff;
                overflow: hidden;
                transition: border-color 0.18s ease;
            }
            .gctm-color-picker:hover {
                border-color: #CBD5E1;
            }
            .gctm-color-picker:focus-within {
                border-color: #93C5FD;
            }

            /* Top row: chip + meta + reset — tightened. */
            .gctm-color-picker__display {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 10px;
                border-bottom: 1px solid #EEF1F7;
                background: #FAFBFE;
            }
            .gctm-color-picker__chip {
                width: 26px;
                height: 26px;
                border-radius: 7px;
                background: var(--chip-bg, #fff);
                border: 1px solid rgba(15, 23, 42, 0.10);
                flex-shrink: 0;
                position: relative;
            }
            /* When the picker is in AUTO mode, show a subtle diagonal pattern
               on the chip so it's visually obvious nothing is overriding. */
            .gctm-color-picker.is-auto .gctm-color-picker__chip {
                background:
                    repeating-linear-gradient(135deg,
                        #F1F5F9 0px, #F1F5F9 4px,
                        #E2E8F0 4px, #E2E8F0 8px) !important;
            }
            /* Meta column — show the hex inline with a tiny label so the
               display row stays single-line and compact. */
            .gctm-color-picker__meta {
                display: flex;
                align-items: baseline;
                gap: 8px;
                min-width: 0;
                flex: 1;
                line-height: 1.2;
            }
            .gctm-color-picker__meta-label {
                font-size: 9px;
                font-weight: 700;
                color: #94A3B8;
                text-transform: uppercase;
                letter-spacing: 0.10em;
            }
            .gctm-color-picker__hex {
                font-family: ui-monospace, "SF Mono", Menlo, monospace;
                font-size: 0.75rem;
                color: #0F172A;
                font-weight: 800;
                letter-spacing: 0.04em;
                line-height: 1.15;
            }
            .gctm-color-picker.is-auto .gctm-color-picker__hex {
                color: #64748B;
                letter-spacing: 0.12em;
            }
            .gctm-color-picker__reset {
                display: inline-grid;
                place-items: center;
                width: 26px;
                height: 26px;
                border-radius: 7px;
                border: 1px solid #E6EAF3;
                background: #fff;
                color: #64748B;
                cursor: pointer;
                transition: opacity 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.15s ease, background-color 0.15s ease;
                opacity: 0;
                pointer-events: none;
                flex-shrink: 0;
            }
            .gctm-color-picker:not(.is-auto) .gctm-color-picker__reset {
                opacity: 1;
                pointer-events: auto;
            }
            .gctm-color-picker__reset:hover {
                color: #DC2626;
                border-color: #FECACA;
                background: #FEF2F2;
                transform: rotate(-25deg);
            }

            /* Bottom row: preset swatches + native-picker custom trigger */
            .gctm-color-picker__swatches {
                display: flex;
                align-items: center;
                gap: 7px;
                padding: 8px 10px;
                flex-wrap: wrap;
            }
            .gctm-color-picker__swatch {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                background: var(--swatch, #cbd5e1);
                border: 0;
                padding: 0;
                cursor: pointer;
                flex-shrink: 0;
                position: relative;
                display: grid;
                place-items: center;
                box-shadow:
                    inset 0 0 0 2px #fff,
                    0 0 0 1px rgba(15, 23, 42, 0.08),
                    0 1px 2px rgba(15, 23, 42, 0.06);
                transition:
                    transform 0.18s cubic-bezier(0.2, 0.8, 0.2, 1),
                    box-shadow 0.18s ease;
            }
            .gctm-color-picker__swatch:hover {
                transform: translateY(-1px) scale(1.08);
                box-shadow:
                    inset 0 0 0 2px #fff,
                    0 0 0 1px rgba(15, 23, 42, 0.12),
                    0 4px 10px rgba(15, 23, 42, 0.16);
            }
            .gctm-color-picker__swatch:focus-visible {
                outline: none;
                box-shadow:
                    inset 0 0 0 2px #fff,
                    0 0 0 2px #1D4ED8,
                    0 4px 10px rgba(15, 23, 42, 0.16);
            }
            .gctm-color-picker__check {
                width: 12px;
                height: 12px;
                color: #fff;
                opacity: 0;
                transform: scale(0.5);
                transition:
                    opacity 0.18s ease,
                    transform 0.18s cubic-bezier(0.2, 0.8, 0.2, 1);
                /* Tiny shadow so the white check stays legible on light swatches. */
                filter: drop-shadow(0 1px 1px rgba(15, 23, 42, 0.25));
            }
            .gctm-color-picker__swatch.is-active {
                box-shadow:
                    inset 0 0 0 2px #fff,
                    0 0 0 2px #1D4ED8;
                transform: scale(1.05);
            }
            .gctm-color-picker__swatch.is-active .gctm-color-picker__check {
                opacity: 1;
                transform: scale(1);
            }
            /* Light swatches need dark check ink to stay readable. JS toggles
               this class based on the chosen colour's luminance. */
            .gctm-color-picker__swatch.is-light .gctm-color-picker__check {
                color: #0F172A;
                filter: drop-shadow(0 1px 1px rgba(15, 23, 42, 0.18));
            }

            /* Custom-color trigger — opens native <input type="color"> when
               clicked. Rendered as a conic-gradient ring so it reads as
               "any colour" at a glance. */
            .gctm-color-picker__custom {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                cursor: pointer;
                flex-shrink: 0;
                position: relative;
                display: grid;
                place-items: center;
                background:
                    conic-gradient(from 180deg,
                        #FB7185, #FBBF24, #10B981, #3B82F6, #8B5CF6, #FB7185);
                box-shadow:
                    inset 0 0 0 2px #fff,
                    0 0 0 1px rgba(15, 23, 42, 0.12),
                    0 1px 2px rgba(15, 23, 42, 0.08);
                transition:
                    transform 0.18s cubic-bezier(0.2, 0.8, 0.2, 1),
                    box-shadow 0.18s ease;
                margin: 0;
            }
            .gctm-color-picker__custom:hover {
                transform: translateY(-1px) scale(1.08) rotate(15deg);
                box-shadow:
                    inset 0 0 0 2px #fff,
                    0 0 0 1px rgba(15, 23, 42, 0.18),
                    0 4px 12px rgba(15, 23, 42, 0.20);
            }
            .gctm-color-picker__custom > svg {
                position: relative;
                z-index: 1;
                color: #fff;
                filter: drop-shadow(0 1px 2px rgba(15, 23, 42, 0.45));
            }
            /* Hide the native <input type="color"> visually but keep it in
               the layout so its built-in picker still opens when the label
               is clicked. */
            .gctm-color-picker__native {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                cursor: pointer;
                border: 0;
                padding: 0;
                background: transparent;
            }
            .gctm-color-picker__custom.is-active {
                box-shadow:
                    inset 0 0 0 2px #fff,
                    0 0 0 2px #1D4ED8,
                    0 6px 14px rgba(29, 78, 216, 0.32);
                transform: scale(1.05);
            }

            /* Compact the project-wide image-uploader inside this modal —
               the shared component renders .avatar-preview at 100px tall by
               default, which is more vertical space than this dialog needs.
               NOTE: do NOT write the component tag literally in any comment
               here. Blade's compiler scans the WHOLE file (CSS / JS / Blade
               comments alike) and treats anything matching the x-component
               regex as a real component tag, which produces a half-opened
               `if ($component->shouldRender())` and breaks PHP syntax. */
            .gift-card-template-modal .avatar-upload .avatar-preview { height: 64px; }
            .gift-card-template-modal .avatar-upload .avatar-edit { top: 6px; right: 6px; }
            .gift-card-template-modal .avatar-upload .avatar-edit label.imageUpload,
            .gift-card-template-modal .avatar-upload .avatar-edit label.imageRemove {
                width: 26px;
                height: 26px;
            }

            /* Footer */
            .gift-card-template-modal__foot {
                border-top: 1px solid #EEF1F7;
                padding: 12px 24px 14px;
                gap: 8px;
                justify-content: flex-end;
            }
            .gift-card-template-modal__foot .btn {
                padding: 7px 16px;
                font-size: 0.85rem;
                font-weight: 600;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            'use strict';

            /**
             * Gift Card Template manager — vanilla JS + jQuery + CoreUI Modal.
             *
             * The admin layout doesn't ship Alpine.js, so this module replaces
             * the previous Alpine implementation entirely. It:
             *
             *   • Reads $editPayload off Edit buttons (data-template attribute)
             *     and populates the modal form imperatively.
             *   • Resets the modal to "create" defaults when Add Template
             *     is clicked.
             *   • Drives the colour swatches by swapping an `.is-active`
             *     class + writing the chosen value to the matching hidden
             *     input. Status uses the global settings-page switch UI.
             *   • Re-renders the live preview card on every relevant change.
             *   • Switches the form's action / _method between store and
             *     update based on whether an id is present.
             */
            (function () {
                /*
                 * Visual definitions for each preset. Mirrors the x-gift-card-design
                 * Blade component so the live modal preview renders identically to
                 * what end users see (rich motif, ribbon, DigiKash brand mark).
                 */
                const PRESET_DEFS = {
                    birthday:    { bg: 'linear-gradient(135deg, #FB7185 0%, #F472B6 45%, #C026D3 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.22)', motif: 'confetti', ribbon: 'Happy Birthday' },
                    holiday:     { bg: 'linear-gradient(135deg, #064E3B 0%, #065F46 40%, #0F766E 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.18)', motif: 'snow',     ribbon: "Season's Greetings" },
                    thankyou:    { bg: 'linear-gradient(135deg, #FDE68A 0%, #FBBF24 55%, #D97706 100%)', ink: '#3F2A05', chip: 'rgba(63,42,5,.10)',    motif: 'rays',     ribbon: 'With Gratitude' },
                    anniversary: { bg: 'linear-gradient(135deg, #4C1D95 0%, #7E22CE 45%, #BE185D 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.20)', motif: 'hearts',   ribbon: 'Happy Anniversary' },
                    congrats:    { bg: 'linear-gradient(135deg, #1E3A8A 0%, #3B82F6 45%, #06B6D4 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.20)', motif: 'sparkles', ribbon: 'Congratulations' },
                    premium:     { bg: 'linear-gradient(135deg, #0B1330 0%, #14245F 45%, #1E3A8A 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.14)', motif: 'mesh',     ribbon: 'A Gift For You' },
                };
                // Backwards-compatible gradient lookup for code that previously
                // pulled the raw gradient string out of PRESETS[key].
                const PRESETS = Object.fromEntries(
                    Object.entries(PRESET_DEFS).map(([k, v]) => [k, v.bg])
                );
                const RIBBON_DEFAULT = @json(__('A Gift For You'));
                const NAME_PLACEHOLDER = @json(__('Template name'));
                const SITE_SYMBOL = @json(siteCurrency('symbol') ?? '$');
                const EDITING_LABEL = @json(__('Editing template'));
                const NEW_LABEL = @json(__('New template'));
                const CREATE_HEADING = @json(__('Create new template'));
                const EDIT_HEADING_PREFIX = @json(__('Edit'));
                const EDIT_HEADING_FALLBACK = @json(__('Edit template'));

                const wrapper = document.querySelector('.gift-card-template-manager');
                const modalEl = document.getElementById('giftCardTemplateModal');
                if (! wrapper || ! modalEl) return;

                const form        = modalEl.querySelector('.js-gctm-form');
                const methodInput = modalEl.querySelector('.js-gctm-method');
                const eyebrow     = modalEl.querySelector('.js-gctm-eyebrow');
                const heading     = modalEl.querySelector('.js-gctm-heading');
                const nameInput   = modalEl.querySelector('.js-gctm-name');
                const presetInput = modalEl.querySelector('.js-gctm-preset');
                const ribbonInput = modalEl.querySelector('.js-gctm-ribbon');
                const defaultAmountInput = modalEl.querySelector('.js-gctm-default-amount');
                const statusSwitch = modalEl.querySelector('.js-gctm-status');
                const categorySelect = modalEl.querySelector('select[name="category"]');
                const previewEl   = modalEl.querySelector('.js-gctm-preview');
                const swatches    = modalEl.querySelectorAll('.js-gctm-swatch');
                const nativePickers = modalEl.querySelectorAll('.js-gctm-native');
                const resetBtns   = modalEl.querySelectorAll('.js-gctm-color-reset');
                const customTriggers = modalEl.querySelectorAll('.gctm-color-picker__custom');
                const pickerCards = {
                    background_color: modalEl.querySelector('.gctm-color-picker[data-target="background_color"]'),
                    text_color:       modalEl.querySelector('.gctm-color-picker[data-target="text_color"]'),
                };
                const hiddenInputs = {
                    background_color: modalEl.querySelector('.js-gctm-input[data-target="background_color"]'),
                    text_color:       modalEl.querySelector('.js-gctm-input[data-target="text_color"]'),
                    status:           modalEl.querySelector('.js-gctm-input[data-target="status"]'),
                };

                let state = defaultState();

                function defaultState() {
                    return {
                        id: null,
                        name: '',
                        category: categorySelect ? categorySelect.options[0]?.value : 'Birthday',
                        preset_key: 'premium',
                        background_color: null,
                        text_color: null,
                        ribbon_text: '',
                        default_amount: null,
                        status: 'active',
                        // sort_order is no longer set from the modal — it's
                        // managed by drag-and-drop in the list view. Keep
                        // a placeholder so server-side validation never
                        // sees a missing field.
                        sort_order: parseInt(wrapper.dataset.nextSortOrder, 10) || 1,
                    };
                }

                /**
                 * Extract the dominant solid colour from a preset gradient string so
                 * the swatch preview circle and the bare hex code in the toolbar
                 * still show *something* meaningful while background_color is null
                 * (i.e. the preset gradient is in effect, no custom override).
                 */
                function presetPrimaryColor(presetKey) {
                    const grad = PRESETS[presetKey] || PRESETS.premium;
                    const match = grad.match(/#([0-9A-Fa-f]{6})/);
                    return match ? `#${match[1].toUpperCase()}` : '#0B1330';
                }

                /**
                 * Detect whether a hex colour is visually light so we can
                 * swap the white "check" overlay for a dark one (otherwise
                 * the active state is invisible on pale swatches). Uses the
                 * standard sRGB luminance formula.
                 */
                function isLightColor(hex) {
                    if (typeof hex !== 'string') return false;
                    const m = hex.replace('#', '');
                    if (m.length !== 6) return false;
                    const r = parseInt(m.slice(0, 2), 16);
                    const g = parseInt(m.slice(2, 4), 16);
                    const b = parseInt(m.slice(4, 6), 16);
                    const lum = (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;
                    return lum > 0.7;
                }

                function setSelectValue(select, value) {
                    if (! select) return;
                    const target = String(value || '').toLowerCase();
                    for (const opt of select.options) {
                        if (String(opt.value).toLowerCase() === target) {
                            select.value = opt.value;
                            return;
                        }
                    }
                    if (select.options.length > 0) {
                        select.value = select.options[0].value;
                    }
                }

                function applyState() {
                    // Header — eyebrow + heading both react to id presence so the
                    // user can always tell at a glance whether they're editing or
                    // creating, even if the name field is blank.
                    if (state.id) {
                        eyebrow.textContent = EDITING_LABEL;
                        heading.textContent = state.name
                            ? `${EDIT_HEADING_PREFIX}: ${state.name}`
                            : EDIT_HEADING_FALLBACK;
                    } else {
                        eyebrow.textContent = NEW_LABEL;
                        heading.textContent = CREATE_HEADING;
                    }

                    // Form fields
                    if (nameInput)   nameInput.value   = state.name || '';
                    if (ribbonInput) ribbonInput.value = state.ribbon_text || '';
                    if (defaultAmountInput) {
                        defaultAmountInput.value = state.default_amount != null
                            ? Number(state.default_amount)
                            : '';
                    }
                    setSelectValue(categorySelect, state.category);
                    setSelectValue(presetInput, state.preset_key);

                    // Status switch — ON = active, OFF = inactive. Any
                    // legacy "draft" rows surface as OFF in the modal so
                    // the admin can either flip them on or leave them.
                    if (statusSwitch) {
                        statusSwitch.checked = state.status === 'active';
                    }

                    // Hidden inputs that mirror swatch / picker choice. Both colour
                    // fields stay empty when no custom override is chosen so the
                    // server stores null and the preset's default (gradient bg,
                    // ink colour) remains the single source of truth.
                    hiddenInputs.background_color.value = state.background_color || '';
                    hiddenInputs.text_color.value       = state.text_color       || '';
                    hiddenInputs.status.value           = state.status           || 'active';

                    // Swatch active state — only the swatch matching an *explicit*
                    // override is highlighted. When the field is null, none of the
                    // preset swatches are marked active (preset default applies).
                    const customMatches = { background_color: false, text_color: false };
                    swatches.forEach((btn) => {
                        const target = btn.dataset.target;
                        const value  = (btn.dataset.value || '').toUpperCase();
                        const current = (hiddenInputs[target]?.value || '').toUpperCase();
                        const matches = current !== '' && value === current;
                        btn.classList.toggle('is-active', matches);
                        btn.classList.toggle('is-light', isLightColor(value));
                        if (matches) customMatches[target] = true;
                    });

                    // Custom-color trigger highlights when the chosen hex doesn't
                    // match any of the preset swatches — i.e. the user picked
                    // something via the native colour input.
                    customTriggers.forEach((trigger) => {
                        const target = trigger.dataset.target;
                        const current = hiddenInputs[target]?.value || '';
                        trigger.classList.toggle('is-active', current !== '' && ! customMatches[target]);
                    });

                    // Picker card AUTO mode — toggles the diagonal pattern on the
                    // big chip + greys out the hex label, and shows / hides the
                    // reset (↺) button via CSS.
                    Object.entries(pickerCards).forEach(([target, card]) => {
                        if (! card) return;
                        const isAuto = ! (hiddenInputs[target]?.value || '');
                        card.classList.toggle('is-auto', isAuto);
                    });

                    // Big chip preview + hex code label. When a target is in AUTO
                    // mode we still show *something* informative — the preset's
                    // dominant colour for background, the preset's ink for text —
                    // and label it "AUTO" so the admin knows the picker is idle.
                    modalEl.querySelectorAll('.js-gctm-swatch-preview').forEach((el) => {
                        const target = el.dataset.target;
                        const current = hiddenInputs[target]?.value || '';
                        let fill = current;
                        if (! fill) {
                            fill = (target === 'background_color')
                                ? presetPrimaryColor(state.preset_key)
                                : ((PRESET_DEFS[state.preset_key] || PRESET_DEFS.premium).ink);
                        }
                        el.style.background = fill || '#FFFFFF';
                    });
                    modalEl.querySelectorAll('.js-gctm-swatch-code').forEach((el) => {
                        const target = el.dataset.target;
                        const current = (hiddenInputs[target]?.value || '').toUpperCase();
                        el.textContent = current || 'AUTO';
                    });

                    // Keep the native <input type="color"> in sync with state so
                    // re-opening the picker starts at the current colour rather
                    // than jumping back to the browser default.
                    nativePickers.forEach((input) => {
                        const target = input.dataset.target;
                        const current = hiddenInputs[target]?.value;
                        const fallback = (target === 'background_color')
                            ? presetPrimaryColor(state.preset_key)
                            : ((PRESET_DEFS[state.preset_key] || PRESET_DEFS.premium).ink);
                        input.value = current || fallback || '#FFFFFF';
                    });

                    // Live preview
                    previewEl.innerHTML = renderPreview();

                    // Form action / method depending on create vs update
                    if (state.id) {
                        form.action = form.dataset.updateBase + '/' + state.id;
                        methodInput.value = 'PUT';
                    } else {
                        form.action = form.dataset.storeUrl;
                        methodInput.value = 'POST';
                    }
                }

                /*
                 * Build one of the six motif SVG overlays. These mirror the
                 * shapes baked into the x-gift-card-design Blade component
                 * (confetti rects, snow circles, sun rays, hearts, sparkles,
                 * mesh radial gradients). Keeping the maths identical means
                 * the live admin preview matches the rendered card pixel
                 * for pixel — what the admin sees is exactly what users
                 * receive in their gift card.
                 */
                function renderMotif(motif, uid) {
                    const wrap = 'position:absolute;inset:0;width:100%;height:100%;pointer-events:none';
                    if (motif === 'confetti') {
                        const colors = ['#FDE68A','#FCA5A5','#A7F3D0','#BFDBFE','#FBCFE8'];
                        let rects = '';
                        for (let i = 0; i < 28; i++) {
                            const x = (i * 47) % 400;
                            const y = (i * 31) % 252;
                            const r = (i % 3) * 4 + 6;
                            const rot = (i * 23) % 360;
                            rects += `<rect x="${x}" y="${y}" width="${r}" height="${r*0.5}" fill="${colors[i % 5]}" opacity=".55" transform="rotate(${rot} ${x} ${y})" rx="1"/>`;
                        }
                        return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${rects}</svg>`;
                    }
                    if (motif === 'snow') {
                        let circles = '';
                        for (let i = 0; i < 40; i++) {
                            const x = (i * 41) % 400, y = (i * 19) % 252;
                            const op = (0.25 + (i % 3) * 0.15).toFixed(2);
                            circles += `<circle cx="${x}" cy="${y}" r="${(i % 3) + 1.2}" fill="#fff" opacity="${op}"/>`;
                        }
                        return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${circles}</svg>`;
                    }
                    if (motif === 'rays') {
                        let lines = '';
                        for (let i = 0; i < 8; i++) {
                            lines += `<line x1="80" y1="0" x2="${i*60}" y2="252" stroke="#fff" stroke-opacity="0.06" stroke-width="1"/>`;
                        }
                        return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">
                            <defs><radialGradient id="gc-ray-${uid}" cx="20%" cy="0%" r="80%">
                                <stop offset="0%" stop-color="#fff" stop-opacity=".55"/>
                                <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
                            </radialGradient></defs>
                            <rect width="400" height="252" fill="url(#gc-ray-${uid})"/>${lines}</svg>`;
                    }
                    if (motif === 'hearts') {
                        let dots = '';
                        for (let i = 0; i < 14; i++) {
                            const x = (i * 53) % 400, y = (i * 37) % 252;
                            const s = 8 + (i % 3) * 4;
                            dots += `<circle cx="${x}" cy="${y + s * 0.3}" r="${(s * 0.4).toFixed(2)}" fill="#fff" opacity="0.10"/>`;
                        }
                        return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${dots}</svg>`;
                    }
                    if (motif === 'sparkles') {
                        let stars = '';
                        for (let i = 0; i < 18; i++) {
                            const x = (i * 41) % 400, y = (i * 23) % 252, s = 4 + (i % 3) * 3;
                            stars += `<g opacity="0.4" transform="translate(${x} ${y})">
                                <path d="M0 -${s} L${(s*0.3).toFixed(2)} -${(s*0.3).toFixed(2)} L${s} 0 L${(s*0.3).toFixed(2)} ${(s*0.3).toFixed(2)} L0 ${s} L-${(s*0.3).toFixed(2)} ${(s*0.3).toFixed(2)} L-${s} 0 L-${(s*0.3).toFixed(2)} -${(s*0.3).toFixed(2)} Z" fill="#FDE68A"/>
                            </g>`;
                        }
                        return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${stars}</svg>`;
                    }
                    if (motif === 'mesh') {
                        return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">
                            <defs>
                                <radialGradient id="gc-m1-${uid}" cx="85%" cy="20%" r="50%">
                                    <stop offset="0%" stop-color="#60A5FA" stop-opacity=".55"/>
                                    <stop offset="100%" stop-color="#60A5FA" stop-opacity="0"/>
                                </radialGradient>
                                <radialGradient id="gc-m2-${uid}" cx="10%" cy="90%" r="55%">
                                    <stop offset="0%" stop-color="#FBBF24" stop-opacity=".30"/>
                                    <stop offset="100%" stop-color="#FBBF24" stop-opacity="0"/>
                                </radialGradient>
                                <pattern id="gc-dots-${uid}" width="14" height="14" patternUnits="userSpaceOnUse">
                                    <circle cx="1" cy="1" r="1" fill="#fff" opacity="0.08"/>
                                </pattern>
                            </defs>
                            <rect width="400" height="252" fill="url(#gc-m1-${uid})"/>
                            <rect width="400" height="252" fill="url(#gc-m2-${uid})"/>
                            <rect width="400" height="252" fill="url(#gc-dots-${uid})"/>
                        </svg>`;
                    }
                    return '';
                }

                function renderPreview() {
                    // Pull all visual attributes from the matching preset
                    // (motif, default ink, chip background, ribbon). Custom
                    // background_color and text_color still override their
                    // respective preset defaults when the admin picks one.
                    const def = PRESET_DEFS[state.preset_key] || PRESET_DEFS.premium;
                    const bg = state.background_color ? state.background_color : def.bg;
                    const ink = state.text_color || def.ink || '#FFFFFF';
                    const chipBg = def.chip;
                    const ribbon = state.ribbon_text || def.ribbon || RIBBON_DEFAULT;
                    const name = state.name || NAME_PLACEHOLDER;
                    const isLightInk = String(ink).toUpperCase() === '#FFFFFF';
                    const chipBorder = isLightInk ? 'rgba(255,255,255,.30)' : 'rgba(63,42,5,.18)';
                    const uid = (state.preset_key || 'premium') + '-' + Date.now();

                    // Preview amount — admin's chosen default, or a sensible
                    // $50 fallback so the card never looks empty while the
                    // field is unset. Uses tabular-nums + thousands grouping
                    // so multi-digit values stay legible.
                    const previewAmount = Number(state.default_amount > 0 ? state.default_amount : 50);
                    const previewAmountText = SITE_SYMBOL + previewAmount.toFixed(2)
                        .replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                    // Sizing follows the Blade component's 1.586:1 aspect.
                    // 320×202 fits the modal preview area nicely.
                    const w = 320;
                    const h = Math.round(w / 1.586);
                    const motifSvg = renderMotif(def.motif, uid);

                    return `
                        <div style="width:${w}px; height:${h}px; position:relative; background:${bg}; color:${ink}; border-radius:${w >= 320 ? 18 : 12}px; overflow:hidden; box-shadow:0 26px 50px -12px rgba(15,23,42,.45), 0 12px 24px -8px rgba(15,23,42,.25), inset 0 0 0 1px rgba(255,255,255,.10); font-family:'Plus Jakarta Sans', system-ui, sans-serif;">
                            ${motifSvg}
                            <div style="position:absolute; top:${(w*0.045).toFixed(1)}px; left:${(w*0.045).toFixed(1)}px; display:flex; align-items:center; gap:${(w*0.018).toFixed(1)}px; font-weight:800; letter-spacing:-.02em; font-size:${(w*0.044).toFixed(1)}px; line-height:1;">
                                <div style="width:${(w*0.062).toFixed(1)}px; height:${(w*0.062).toFixed(1)}px; border-radius:${(w*0.014).toFixed(1)}px; background:rgba(255,255,255,.22); border:1px solid rgba(255,255,255,.35); display:grid; place-items:center;">
                                    <svg viewBox="0 0 24 24" width="${(w*0.04).toFixed(1)}" height="${(w*0.04).toFixed(1)}" fill="none">
                                        <path d="M4 7h12a4 4 0 0 1 0 8H4V7z" fill="${ink}"/>
                                    </svg>
                                </div>
                                DigiKash
                            </div>
                            <div style="position:absolute; top:${(w*0.05).toFixed(1)}px; right:${(w*0.05).toFixed(1)}px; background:${chipBg}; border:1px solid ${chipBorder}; padding:${(w*0.014).toFixed(1)}px ${(w*0.028).toFixed(1)}px; border-radius:999px; font-size:${(w*0.028).toFixed(1)}px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
                                ${escapeHtml(ribbon)}
                            </div>
                            <div style="position:absolute; left:${(w*0.05).toFixed(1)}px; right:${(w*0.05).toFixed(1)}px; top:38%;">
                                <div style="font-size:${(w*0.028).toFixed(1)}px; letter-spacing:.16em; text-transform:uppercase; font-weight:700; opacity:.78; margin-bottom:${(w*0.01).toFixed(1)}px;">{{ __('Gift Card Value') }}</div>
                                <div style="font-size:${(w*0.16).toFixed(1)}px; font-weight:800; letter-spacing:-.03em; line-height:.95; ${isLightInk ? 'text-shadow:0 2px 12px rgba(0,0,0,.18);' : ''} font-variant-numeric:tabular-nums;">${previewAmountText}</div>
                            </div>
                            <div style="position:absolute; left:${(w*0.05).toFixed(1)}px; right:${(w*0.05).toFixed(1)}px; bottom:${(w*0.05).toFixed(1)}px; display:flex; justify-content:space-between; align-items:flex-end; gap:${(w*0.04).toFixed(1)}px;">
                                <div style="min-width:0; flex:1;">
                                    <div style="font-size:${(w*0.028).toFixed(1)}px; letter-spacing:.12em; text-transform:uppercase; font-weight:700; opacity:.72;">{{ __('TO') }}</div>
                                    <div style="font-size:${(w*0.045).toFixed(1)}px; font-weight:700; margin-top:${(w*0.005).toFixed(1)}px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(name)}</div>
                                </div>
                                <div style="min-width:0; flex:1; text-align:right;">
                                    <div style="font-size:${(w*0.028).toFixed(1)}px; letter-spacing:.12em; text-transform:uppercase; font-weight:700; opacity:.72;">{{ __('FROM') }}</div>
                                    <div style="font-size:${(w*0.045).toFixed(1)}px; font-weight:700; margin-top:${(w*0.005).toFixed(1)}px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">DigiKash</div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                function escapeHtml(str) {
                    return String(str).replace(/[&<>"']/g, (c) => ({
                        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                    }[c]));
                }

                function openModal() {
                    if (window.coreui && window.coreui.Modal) {
                        window.coreui.Modal.getOrCreateInstance(modalEl).show();
                    } else if (window.bootstrap && window.bootstrap.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                }

                // ─── Wire events ────────────────────────────────────────────
                wrapper.querySelectorAll('.js-gctm-create').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        state = defaultState();
                        applyState();
                        openModal();
                    });
                });

                wrapper.querySelectorAll('.js-gctm-edit').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        try {
                            const payload = JSON.parse(btn.dataset.template || '{}');
                            state = Object.assign(defaultState(), payload);
                            // Preserve null/empty colour fields so the preset's
                            // own defaults stay in effect — only normalise when
                            // a real custom override exists.
                            state.background_color = state.background_color
                                ? String(state.background_color).toUpperCase()
                                : null;
                            state.text_color = state.text_color
                                ? String(state.text_color).toUpperCase()
                                : null;
                            state.status           = state.status     || 'active';
                            state.preset_key       = state.preset_key || 'premium';
                            state.ribbon_text      = state.ribbon_text || '';
                            // Normalise default_amount: number or null.
                            // The server casts the column to float so the
                            // payload usually arrives as a number, but
                            // tolerate strings too.
                            {
                                const da = parseFloat(state.default_amount);
                                state.default_amount = isFinite(da) && da > 0 ? da : null;
                            }
                        } catch (e) {
                            state = defaultState();
                        }
                        applyState();
                        openModal();
                    });
                });

                // Swatch clicks → update hidden input, re-render. Clicking the
                // already-active swatch a second time clears the override
                // (works for both bg and text colour pickers now) — handy
                // escape hatch when the admin wants to revert to defaults
                // without reloading the modal.
                swatches.forEach((swatch) => {
                    swatch.addEventListener('click', () => {
                        const target = swatch.dataset.target;
                        const value  = swatch.dataset.value;
                        const current = (state[target] || '').toUpperCase();
                        if (current === String(value).toUpperCase()) {
                            state[target] = null;
                        } else {
                            state[target] = value;
                        }
                        applyState();
                    });
                });

                // Custom-colour native input (input type=color). Fires on
                // every change while the user drags through the picker, so
                // the preview updates live just like a swatch click.
                nativePickers.forEach((input) => {
                    input.addEventListener('input', () => {
                        const target = input.dataset.target;
                        state[target] = String(input.value).toUpperCase();
                        applyState();
                    });
                });

                // Reset button — clears the override back to AUTO so the
                // preset gradient / ink reasserts itself. The button is
                // CSS-hidden in AUTO mode, so this only fires when the
                // admin has explicitly picked a colour.
                resetBtns.forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const target = btn.dataset.target;
                        state[target] = null;
                        applyState();
                    });
                });

                // Status switch — flipping the toggle updates state.status
                // (active / inactive). We do a partial update of just the
                // hidden input + state object since nothing visual on the
                // card preview depends on the status flag.
                if (statusSwitch) {
                    statusSwitch.addEventListener('change', () => {
                        state.status = statusSwitch.checked ? 'active' : 'inactive';
                        if (hiddenInputs.status) {
                            hiddenInputs.status.value = state.status;
                        }
                    });
                }

                // Live re-render on text field changes
                [nameInput, ribbonInput, presetInput, defaultAmountInput].forEach((el) => {
                    if (! el) return;
                    const eventName = (el === presetInput) ? 'change' : 'input';
                    el.addEventListener(eventName, () => {
                        if (el === nameInput)          state.name = el.value;
                        if (el === ribbonInput)        state.ribbon_text = el.value;
                        if (el === presetInput)        state.preset_key = el.value;
                        if (el === defaultAmountInput) {
                            // Treat empty / non-numeric as "no default" (null)
                            // so the preview reverts to the $50 fallback and
                            // the column stores NULL on save.
                            const raw = parseFloat(el.value);
                            state.default_amount = isFinite(raw) && raw > 0 ? raw : null;
                        }
                        // Preset change can affect the bg swatch fallback colour
                        // and the gradient, so route through applyState() to keep
                        // every dependent UI bit in sync. Name / ribbon / amount
                        // edits do a lighter partial update.
                        if (el === presetInput) {
                            applyState();
                        } else {
                            heading.textContent = state.id
                                ? (state.name ? `${EDIT_HEADING_PREFIX}: ${state.name}` : EDIT_HEADING_FALLBACK)
                                : CREATE_HEADING;
                            previewEl.innerHTML = renderPreview();
                        }
                    });
                });

                // Mirror category changes back into state (not visual)
                if (categorySelect) {
                    categorySelect.addEventListener('change', () => { state.category = categorySelect.value; });
                }

                // Initial paint so the modal isn't empty if it ever shows
                // before a click (e.g. server-side validation re-open).
                applyState();

                // ─── Drag-to-sort the templates table ───────────────────────
                // Mirrors the pattern used by Wallet Earn plans (see
                // public/backend/js/wallet-earn-admin.js). The shared
                // backend layout already loads sortable.js, so we only
                // need a small initialiser here.
                const sortableTbody = document.getElementById('gift-card-template-sortable');
                if (sortableTbody && typeof Sortable !== 'undefined' && sortableTbody.querySelector('tr[data-id]')) {
                    const endpoint  = sortableTbody.dataset.positionUrl;
                    const csrfToken = sortableTbody.dataset.csrfToken;
                    const okMsg     = sortableTbody.dataset.successMessage || 'Order updated.';
                    const errMsg    = sortableTbody.dataset.errorMessage   || 'Could not update order.';

                    new Sortable(sortableTbody, {
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        filter: '[data-drag-hint]', // exclude the help-text row
                        onEnd: function () {
                            const positions = [];
                            sortableTbody.querySelectorAll('tr[data-id]').forEach((row, index) => {
                                positions.push({ id: parseInt(row.dataset.id, 10), order: index + 1 });
                            });
                            if (! positions.length) return;
                            $.ajax({
                                url: endpoint,
                                method: 'POST',
                                data: { _token: csrfToken, positions: positions },
                                success: (resp) => notifyEvs('success', resp.message || okMsg),
                                error: () => notifyEvs('error', errMsg),
                            });
                        },
                    });
                }
            })();
        </script>
    @endpush
@endsection
