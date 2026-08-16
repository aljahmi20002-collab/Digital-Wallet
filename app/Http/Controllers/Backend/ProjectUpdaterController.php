<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\NotifyErrorException;
use App\Http\Requests\Backend\ActivateProjectLicenseRequest;
use App\Http\Requests\Backend\DownloadProjectBackupRequest;
use App\Http\Requests\Backend\InstallProjectUpdateRequest;
use App\Models\ProjectUpdate;
use App\Services\ProjectUpdaterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectUpdaterController extends BaseController
{
    public function __construct(private readonly ProjectUpdaterService $updater) {}

    public static function permissions(): array
    {
        return [
            'index|check'                     => 'project-updater-view',
            'activate|install|backupDownload' => 'project-updater-manage',
        ];
    }

    public function index(): View
    {
        return view('backend.app.updater', $this->updater->overview());
    }

    public function activate(ActivateProjectLicenseRequest $request): RedirectResponse
    {
        try {
            $this->updater->activate((string) $request->validated('purchase_code'));
            notifyEvs('success', __('Project license activated successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('admin.app.updater.index');
    }

    public function check(): RedirectResponse
    {
        try {
            $update = $this->updater->checkForUpdates();

            notifyEvs(
                $update->status === 'available' ? 'success' : 'info',
                $update->status === 'available'
                    ? __('Version :version is available.', ['version' => $update->version])
                    : __('Your application is already up to date.')
            );
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('admin.app.updater.index');
    }

    public function install(InstallProjectUpdateRequest $request, ProjectUpdate $update): RedirectResponse
    {
        try {
            $this->updater->install($update, $request->boolean('backup_database_storage'));
            notifyEvs('success', __('Project update installed successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('admin.app.updater.index');
    }

    public function backupDownload(DownloadProjectBackupRequest $request): BinaryFileResponse|RedirectResponse
    {
        try {
            $backup = $this->updater->createDownloadableRecoveryBackup();

            notifyEvs('success', __('Recovery backup is ready to download.'));

            return response()->download($backup['path'], $backup['name']);
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return redirect()->route('admin.app.updater.index');
    }
}
