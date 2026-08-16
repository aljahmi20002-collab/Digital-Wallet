<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\Role\StoreRoleRequest;
use App\Http\Requests\Backend\Role\UpdateRoleRequest;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index'        => 'role-list',
            'create|store' => 'role-create',
            'edit|update'  => 'role-edit',
            'destroy'      => 'role-delete',
        ];
    }

    public function index(): View
    {
        $roles = Role::query()
            ->with('users')
            ->withCount(['users', 'permissions'])
            ->orderBy('id')
            ->paginate(10);

        $summary = [
            'totalRoles'    => Role::query()->count(),
            'assignedStaff' => DB::table(config('permission.table_names.model_has_roles'))
                ->where('model_type', Admin::class)
                ->distinct()
                ->count('model_id'),
            'totalPermissions' => Permission::query()->where('guard_name', 'admin')->count(),
        ];

        return view('backend.role.index', compact('roles', 'summary'));
    }

    public function create(): View
    {
        $permissions = $this->groupedPermissions();

        return view('backend.role.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create([
            'name'        => $validated['role_name'],
            'description' => $validated['description'],
            'guard_name'  => 'admin',
        ]);

        $role->syncPermissions($request->selectedPermissionIds());

        notifyEvs('success', 'Role created successfully');

        return redirect()->route('admin.role.index');
    }

    public function edit(int $id): View
    {
        $role            = Role::findOrFail($id);
        $permissions     = $this->groupedPermissions();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('backend.role.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::findOrFail($id);
        $role->update([
            'name'        => $validated['role_name'],
            'description' => $validated['description'],
        ]);

        $role->syncPermissions($request->selectedPermissionIds());

        notifyEvs('success', 'Role updated successfully');

        return redirect()->route('admin.role.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'super-admin') {
            notifyEvs('error', 'The super-admin role cannot be deleted');
        } else {

            $assignedStaffCount = DB::table(config('permission.table_names.model_has_roles'))
                ->join('admins', config('permission.table_names.model_has_roles').'.model_id', '=', 'admins.id')
                ->where('role_id', $role->id)
                ->where('model_type', Admin::class)
                ->count();
            if ($assignedStaffCount > 0) {
                notifyEvs('error', 'This role is assigned to staff and cannot be deleted');

                return redirect()->route('admin.role.index');
            }

            DB::transaction(function () use ($role): void {
                DB::table(config('permission.table_names.role_has_permissions'))
                    ->where($this->rolePivotKey(), $role->id)
                    ->delete();

                DB::table(config('permission.table_names.model_has_roles'))
                    ->where($this->rolePivotKey(), $role->id)
                    ->where(function ($query): void {
                        $query->where('model_type', '!=', Admin::class)
                            ->orWhereNotExists(function ($subQuery): void {
                                $subQuery->selectRaw('1')
                                    ->from('admins')
                                    ->whereColumn('admins.id', config('permission.table_names.model_has_roles').'.model_id');
                            });
                    })
                    ->delete();

                $role->delete();
            });

            notifyEvs('success', 'Role deleted successfully');
        }

        return redirect()->route('admin.role.index');
    }

    private function groupedPermissions(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'admin')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }

    private function rolePivotKey(): string
    {
        return config('permission.column_names.role_pivot_key') ?: 'role_id';
    }
}
