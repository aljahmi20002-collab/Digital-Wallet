<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestInstallDatabaseConnectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'db_connection' => ['required', Rule::in(['mysql', 'mariadb', 'sqlite'])],
            'db_host'       => ['required_unless:db_connection,sqlite', 'nullable', 'string', 'max:255'],
            'db_port'       => ['required_unless:db_connection,sqlite', 'nullable', 'integer', 'min:1', 'max:65535'],
            'db_database'   => ['required', 'string', 'max:255'],
            'db_username'   => ['required_unless:db_connection,sqlite', 'nullable', 'string', 'max:255'],
            'db_password'   => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'db_host.required_unless'     => __('Database host is required for MySQL/MariaDB installs.'),
            'db_port.required_unless'     => __('Database port is required for MySQL/MariaDB installs.'),
            'db_username.required_unless' => __('Database username is required for MySQL/MariaDB installs.'),
        ];
    }
}
