<?php

namespace App\Http\Requests\Backend\Role;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($this->route('role'))
                    ->where('guard_name', 'admin'),
            ],
            'description'  => ['required', 'string', 'max:255'],
            'permission'   => ['required', 'array', 'min:1'],
            'permission.*' => [
                'required',
                'integer',
                Rule::exists('permissions', 'id')->where('guard_name', 'admin'),
            ],
        ];
    }

    /**
     * @return list<int>
     */
    public function selectedPermissionIds(): array
    {
        return collect($this->validated('permission', []))
            ->map(fn (mixed $permissionId): int => (int) $permissionId)
            ->filter(fn (int $permissionId): bool => $permissionId > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permission.required' => __('Select at least one permission before saving this role.'),
            'permission.min'      => __('Select at least one permission before saving this role.'),
        ];
    }
}
