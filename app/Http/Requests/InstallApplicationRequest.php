<?php

namespace App\Http\Requests;

use App\Support\InstallationManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class InstallApplicationRequest extends FormRequest
{
    /**
     * Top-level URL segments already owned by the application. Picking one
     * of these as the admin prefix would shadow the matching public route,
     * so the installer rejects them before settings are persisted.
     */
    private const RESERVED_ADMIN_PREFIXES = [
        'admin', 'api', 'api-docs', 'auth', 'blog', 'home', 'install',
        'login', 'logout', 'payment', 'payment-link', 'public', 'register',
        'status', 'storage', 'summernote', 'user', 'webhook',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'admin_prefix'          => strtolower(trim((string) $this->input('admin_prefix', ''))),
            'default_currency_code' => strtoupper(trim((string) $this->input('default_currency_code', ''))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reserved = array_values(array_diff(self::RESERVED_ADMIN_PREFIXES, ['admin']));

        return [
            'app_name'                    => ['required', 'string', 'max:80'],
            'app_url'                     => ['required', 'url', 'max:255'],
            'admin_prefix'                => ['required', 'string', 'min:2', 'max:20', 'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', Rule::notIn($reserved)],
            'default_currency_code'       => ['required', 'string', 'size:3', 'alpha', Rule::in(array_keys(InstallationManager::currencyCatalog()))],
            'db_connection'               => ['required', Rule::in(['mysql', 'mariadb', 'sqlite'])],
            'db_host'                     => ['required_unless:db_connection,sqlite', 'nullable', 'string', 'max:255'],
            'db_port'                     => ['required_unless:db_connection,sqlite', 'nullable', 'integer', 'min:1', 'max:65535'],
            'db_database'                 => ['required', 'string', 'max:255'],
            'db_username'                 => ['required_unless:db_connection,sqlite', 'nullable', 'string', 'max:255'],
            'db_password'                 => ['nullable', 'string', 'max:255'],
            'admin_name'                  => ['required', 'string', 'max:120'],
            'admin_email'                 => ['required', 'email:rfc', 'max:255'],
            'admin_password'              => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'admin_password_confirmation' => ['required'],
            'seed_demo_data'              => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'db_host.required_unless'        => __('Database host is required for MySQL/MariaDB installs.'),
            'db_port.required_unless'        => __('Database port is required for MySQL/MariaDB installs.'),
            'db_username.required_unless'    => __('Database username is required for MySQL/MariaDB installs.'),
            'admin_prefix.regex'             => __('Admin URL prefix may only contain lowercase letters, numbers, and dashes (and must start and end with a letter or number).'),
            'admin_prefix.not_in'            => __('This prefix is reserved by the application. Choose another value such as :example.', ['example' => 'admin']),
            'default_currency_code.in'       => __('Pick a supported currency from the dropdown.'),
            'default_currency_code.required' => __('Choose the default currency that buyers see across the application.'),
        ];
    }
}
