<?php

namespace App\Http\Controllers;

use App\Exceptions\NotifyErrorException;
use App\Http\Requests\InstallApplicationRequest;
use App\Http\Requests\TestInstallDatabaseConnectionRequest;
use App\Support\InstallationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InstallerController extends Controller
{
    public function __construct(private readonly InstallationManager $installer) {}

    public function index(): View|RedirectResponse
    {
        if ($this->installer->isInstalled()) {
            return redirect()->route('home');
        }

        $defaultConnection = (string) config('database.default', 'mysql');
        $defaultDatabase   = $defaultConnection === 'sqlite'
            ? (string) config('database.connections.sqlite.database', 'database/database.sqlite')
            : (string) config("database.connections.{$defaultConnection}.database", 'digitalwallet');

        return view('installer.index', [
            'status'   => $this->installer->status(),
            'defaults' => [
                'app_name'              => config('app.name', 'DigitalWallet'),
                'app_url'               => config('app.url', 'http://localhost'),
                'admin_prefix'          => InstallationManager::DEFAULT_ADMIN_PREFIX,
                'default_currency_code' => InstallationManager::DEFAULT_CURRENCY_CODE,
                'db_connection'         => $defaultConnection,
                'db_host'               => config("database.connections.{$defaultConnection}.host", config('database.connections.mysql.host', '127.0.0.1')),
                'db_port'               => config("database.connections.{$defaultConnection}.port", config('database.connections.mysql.port', 3306)),
                'db_database'           => $defaultDatabase,
                'db_username'           => config("database.connections.{$defaultConnection}.username", config('database.connections.mysql.username', 'root')),
            ],
            'currencyCatalog' => InstallationManager::currencyCatalog(),
        ]);
    }

    public function testDatabase(TestInstallDatabaseConnectionRequest $request): JsonResponse
    {
        if ($this->installer->isInstalled()) {
            return response()->json([
                'ok'       => false,
                'status'   => 'error',
                'message'  => __('The application is already installed.'),
                'guidance' => $this->databaseGuidance(),
            ], 409);
        }

        try {
            return response()->json($this->installer->testDatabaseConnection($request->validated()));
        } catch (NotifyErrorException $e) {
            return response()->json([
                'ok'       => false,
                'status'   => 'error',
                'message'  => $e->getMessage(),
                'guidance' => $this->databaseGuidance(),
            ], 422);
        }
    }

    public function store(InstallApplicationRequest $request): RedirectResponse
    {
        try {
            $result = $this->installer->install($request->validated());

            notifyEvs('success', __('Installation completed. Sign in with your admin account.'));

            return redirect($result['admin_url']);
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());

            return back()
                ->withInput($request->except([
                    'admin_password',
                    'admin_password_confirmation',
                    'db_password',
                ]))
                ->with('install_error', [
                    'message'  => $e->getMessage(),
                    'guidance' => $this->installer->permissionGuidance(),
                ]);
        }
    }

    /**
     * @return list<string>
     */
    private function databaseGuidance(): array
    {
        return [
            __('Confirm DB host and port. Local servers usually use 127.0.0.1 and port 3306.'),
            __('Make sure the database username and password are correct.'),
            __('Give the database user CREATE, ALTER, INDEX, INSERT, UPDATE, DELETE, and SELECT permissions.'),
            __('On cPanel, open MySQL Databases, add the user to the database, then choose All Privileges.'),
            __('On VPS/Linux, grant privileges from MySQL/MariaDB and restart the web server if PHP extensions were changed.'),
        ];
    }
}
