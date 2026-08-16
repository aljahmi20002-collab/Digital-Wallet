<?php

namespace App\Http\Controllers\Backend;

use App\Services\QueueManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QueueManagementController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'failed|retry|retryAll|forget|flush' => 'queue-manage',
        ];
    }

    public function __construct(private QueueManagementService $service) {}

    public function failed(): View
    {
        $failedJobs = $this->service->getFailedJobs(20);
        $count      = $this->service->getFailedJobsCount();

        return view('backend.background_tasks.queue', compact('failedJobs', 'count'));
    }

    public function retry(string $id): RedirectResponse
    {
        try {
            $this->service->retryJob($id);
            notifyEvs('success', __('Job #:id has been queued for retry.', ['id' => $id]));
        } catch (\Throwable $e) {
            notifyEvs('error', __('Failed to retry job: :message', ['message' => $e->getMessage()]));
        }

        return redirect()->back();
    }

    public function retryAll(): RedirectResponse
    {
        try {
            $this->service->retryAll();
            notifyEvs('success', __('All failed jobs have been queued for retry.'));
        } catch (\Throwable $e) {
            notifyEvs('error', __('Failed to retry all jobs: :message', ['message' => $e->getMessage()]));
        }

        return redirect()->back();
    }

    public function forget(string $id): RedirectResponse
    {
        try {
            $this->service->forgetJob($id);
            notifyEvs('success', __('Failed job #:id has been deleted.', ['id' => $id]));
        } catch (\Throwable $e) {
            notifyEvs('error', __('Failed to delete job: :message', ['message' => $e->getMessage()]));
        }

        return redirect()->back();
    }

    public function flush(): RedirectResponse
    {
        try {
            $this->service->flushAll();
            notifyEvs('success', __('All failed jobs have been flushed.'));
        } catch (\Throwable $e) {
            notifyEvs('error', __('Failed to flush jobs: :message', ['message' => $e->getMessage()]));
        }

        return redirect()->back();
    }
}
