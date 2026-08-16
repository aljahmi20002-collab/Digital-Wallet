@php
    $role = $role ?? null;
    $selectedPermissions = collect(old('permission', $rolePermissions ?? []))
        ->map(fn ($permissionId): int => (int) $permissionId)
        ->filter(fn (int $permissionId): bool => $permissionId > 0)
        ->unique()
        ->values();
@endphp

<div class="role-mgmt-page role-mgmt-page--manage">
    <form action="{{ $action }}" method="post" class="role-mgmt-form">
        @csrf
        @isset($method)
            @method($method)
        @endisset

        <section class="role-mgmt-card">
            <div class="role-mgmt-card__header">
                <div class="role-mgmt-card__title">
                    <span class="role-mgmt-card__title-icon">
                        <x-icon name="badge-account" height="22" width="22"/>
                    </span>
                    <div>
                        <h5>{{ __('Role Identity') }}</h5>
                    </div>
                </div>
                <a href="{{ route('admin.role.index') }}" class="btn role-mgmt-secondary-action">
                    <x-icon name="back" height="18" width="18"/>
                    {{ __('Back') }}
                </a>
            </div>

            <div class="role-mgmt-card__body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label" for="role_name">{{ __('Role Name') }}</label>
                        <input type="text"
                               id="role_name"
                               name="role_name"
                               class="form-control @error('role_name') is-invalid @enderror"
                               value="{{ old('role_name', $role?->name) }}"
                               placeholder="{{ __('Operations Manager') }}">
                        @error('role_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-8">
                        <label class="form-label" for="description">{{ __('Role Description') }}</label>
                        <input type="text"
                               id="description"
                               name="description"
                               class="form-control @error('description') is-invalid @enderror"
                               value="{{ old('description', $role?->description) }}"
                               placeholder="{{ __('Can review finance activity and manage customer support tasks.') }}">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="role-mgmt-card role-mgmt-permission-card">
            <div class="role-mgmt-card__header">
                <div class="role-mgmt-card__title">
                    <span class="role-mgmt-card__title-icon">
                        <x-icon name="feature-management" height="22" width="22"/>
                    </span>
                    <div>
                        <h5>{{ __('Permission Matrix') }}</h5>
                    </div>
                </div>
                <span class="role-mgmt-selection-pill">
                    {{ trans_choice('{0} No permissions selected|{1} :count selected|[2,*] :count selected', $selectedPermissions->count(), ['count' => $selectedPermissions->count()]) }}
                </span>
            </div>

            <div class="role-mgmt-permission-shell">
                <aside class="role-mgmt-permission-nav" id="role-permission-tabs" role="tablist" aria-orientation="vertical">
                    <header class="role-mgmt-permission-nav__header">
                        <span class="role-mgmt-permission-nav__icon">
                            <x-icon name="cil-menu" height="18" width="18"/>
                        </span>
                        <span>
                            <strong>{{ __('Permission Groups') }}</strong>
                        </span>
                    </header>
                    <div class="role-mgmt-permission-nav__scroll">
                        @foreach($permissions as $category => $permissionList)
                            @php
                                $categoryPermission = $permissionList->first();
                                $categoryName = $categoryPermission?->category_display_name ?: title($category);
                                $categoryIcon = $categoryPermission?->category_icon ?: $category;
                                $categorySummary = $categoryPermission?->category_summary ?: $categoryName;
                            @endphp
                            <button class="role-mgmt-permission-tab {{ $loop->first ? 'active' : '' }}"
                                    id="role-permission-{{ $category }}-tab"
                                    data-coreui-toggle="pill"
                                    data-coreui-target="#role-permission-{{ $category }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="role-permission-{{ $category }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <span class="role-mgmt-permission-tab__icon">
                                    <x-icon :name="$categoryIcon" height="20" width="20"/>
                                </span>
                                <span class="role-mgmt-permission-tab__text">
                                    <span class="role-mgmt-permission-tab__title">{{ $categoryName }}</span>
                                    <span class="role-mgmt-permission-tab__meta">
                                        {{ $categorySummary }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </aside>

                <div class="role-mgmt-permission-content tab-content" id="role-permission-tab-content">
                    @foreach($permissions as $category => $permissionList)
                        @php
                            $categoryPermission = $permissionList->first();
                            $categoryName = $categoryPermission?->category_display_name ?: title($category);
                            $categoryIcon = $categoryPermission?->category_icon ?: $category;
                            $categoryDescription = $categoryPermission?->category_description ?: $categoryName;
                        @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                             id="role-permission-{{ $category }}"
                             role="tabpanel"
                             aria-labelledby="role-permission-{{ $category }}-tab"
                             tabindex="0">
                            <div class="role-mgmt-permission-group">
                                <header class="role-mgmt-permission-group__header">
                                    <div class="role-mgmt-permission-group__title">
                                        <span class="role-mgmt-permission-group__icon">
                                            <x-icon :name="$categoryIcon" height="20" width="20"/>
                                        </span>
                                        <div>
                                            <h3>{{ $categoryName }}</h3>
                                            <p>{{ $categoryDescription }}</p>
                                        </div>
                                    </div>
                                    <div class="role-mgmt-permission-header-actions">
                                        <a href="{{ route('admin.role.index') }}" class="btn role-mgmt-permission-cancel">
                                            {{ __('Cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary role-mgmt-permission-save">
                                            <x-icon name="check" height="16" width="16"/>
                                            {{ $submitLabel }}
                                        </button>
                                    </div>
                                </header>

                                <div class="role-mgmt-permission-grid">
                                    @foreach($permissionList as $permission)
                                        @php($isChecked = $selectedPermissions->contains((int) $permission->id))
                                        <label class="role-mgmt-permission-toggle" for="permission-{{ $permission->id }}">
                                            <input class="role-mgmt-permission-toggle__input"
                                                   type="checkbox"
                                                   role="switch"
                                                   name="permission[{{ $permission->id }}]"
                                                   value="{{ $permission->id }}"
                                                   id="permission-{{ $permission->id }}"
                                                   @checked($isChecked)>
                                            <span class="role-mgmt-permission-toggle__copy">
                                                <span class="role-mgmt-permission-toggle__title">{{ $permission->display_name ?: title($permission->name) }}</span>
                                                <span class="role-mgmt-permission-toggle__hint">{{ $permission->description }}</span>
                                            </span>
                                            <span class="role-mgmt-permission-toggle__track" aria-hidden="true"></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @error('permission')
                <div class="role-mgmt-permission-error">{{ $message }}</div>
            @enderror
        </section>

    </form>
</div>
