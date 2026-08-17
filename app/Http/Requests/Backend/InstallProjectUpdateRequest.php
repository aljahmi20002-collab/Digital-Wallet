<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class InstallProjectUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirm_backup'          => ['accepted'],
            'confirm_local_backup'    => ['accepted'],
            'backup_database_storage' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_backup.accepted'       => __('Confirm that you have reviewed the backup notice before installing.'),
            'confirm_local_backup.accepted' => __('Confirm that you downloaded a local recovery backup or accept the restore risk before installing.'),
        ];
    }
}
