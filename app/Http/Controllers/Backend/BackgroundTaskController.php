<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\RunBackgroundTaskRequest;
use App\Models\BackgroundTaskLog;
use App\Services\BackgroundTaskRegistry;
use App\Services\BackgroundTaskRunner;
use App\Services\QueueManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackgroundTaskController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|logs|scheduler' => 'background-task-list',
            'run'                  => 'background-task-run',
        ];
    }

    public function __construct(
        private BackgroundTaskRegistry $registry,
        private BackgroundTaskRunner $runner,
        private QueueManagementService $queueService,
    ) {}

    public function index(): View
    {
        $tasks    = $this->registry->all();
        $enriched = [];

        foreach ($tasks as $key => $task) {
            $enriched[$key] = array_merge($task, [
                'last_log'  => BackgroundTaskLog::query()->where('task_key', $key)->latest()->first(),
                'run_count' => BackgroundTaskLog::query()->where('task_key', $key)->count(),
            ]);
        }

        $stats = [
            'total_tasks'  => $this->registry->count(),
            'last_success' => BackgroundTaskLog::query()->where('status', 'success')->latest()->value('finished_at'),
            'last_failed'  => BackgroundTaskLog::query()->where('status', 'failed')->latest()->value('finished_at'),
            'failed_jobs'  => $this->queueService->getFailedJobsCount(),
        ];

        $recentFailedJobs = $this->queueService->getFailedJobs(5);

        return view('backend.background_tasks.index', compact('enriched', 'stats', 'recentFailedJobs'));
    }

    public function run(RunBackgroundTaskRequest $request): RedirectResponse
    {
        $taskKey = $request->validated('task_key');
        $task    = $this->registry->get($taskKey);
        $options = [];

        if (isset($task['options'])) {
            foreach ($task['options'] as $name => $config) {
                if (($config['type'] ?? null) === 'boolean') {
                    if ($request->boolean($name, (bool) ($config['default'] ?? false))) {
                        $options[$name] = true;
                    }

                    continue;
                }

                if (($config['type'] ?? null) === 'integer') {
                    $options[$name] = (int) ($request->validated($name) ?? $config['default']);
                }
            }
        }

        try {
            $log = $this->runner->run($taskKey, $options, auth()->id());

            if ($log->isSuccess()) {
                notifyEvs('success', __('Task ":task" completed successfully.', ['task' => $task['label']]));
            } else {
                notifyEvs('error', __('Task ":task" failed. See logs for details.', ['task' => $task['label']]));
            }
        } catch (\RuntimeException $e) {
            notifyEvs('error', $e->getMessage());
        } catch (\Throwable $e) {
            notifyEvs('error', __('Task execution error: :message', ['message' => $e->getMessage()]));
        }

        return redirect()->route('admin.background-tasks.index');
    }

    public function scheduler(): View
    {
        return view('backend.background_tasks.scheduler');
    }

    public function logs(Request $request): View
    {
        $query = BackgroundTaskLog::query()->with('executor')->latest();

        if ($request->filled('task_key')) {
            $query->where('task_key', $request->input('task_key'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $logs  = $query->paginate(20)->withQueryString();
        $tasks = $this->registry->all();

        return view('backend.background_tasks.logs', compact('logs', 'tasks'));
    }
}
