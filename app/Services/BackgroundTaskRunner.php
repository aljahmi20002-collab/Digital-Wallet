<?php

namespace App\Services;

use App\Models\BackgroundTaskLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Throwable;

class BackgroundTaskRunner
{
    private const LOCK_TTL = 300;

    public function __construct(private BackgroundTaskRegistry $registry) {}

    /**
     * @param array<string, mixed> $options
     *
     * @throws \InvalidArgumentException if task key is unknown
     * @throws \RuntimeException         if task is already running
     */
    public function run(string $taskKey, array $options = [], ?int $adminId = null): BackgroundTaskLog
    {
        $task = $this->registry->get($taskKey);

        if ($task === null) {
            throw new \InvalidArgumentException("Unknown task key: {$taskKey}");
        }

        $lockKey = "background_task:{$taskKey}:running";
        $lock    = Cache::lock($lockKey, self::LOCK_TTL);

        if (! $lock->get()) {
            throw new \RuntimeException(__('Task ":task" is already running. Please wait for it to finish.', ['task' => $task['label']]));
        }

        $log = BackgroundTaskLog::query()->create([
            'task_key'          => $taskKey,
            'command_signature' => $task['signature'],
            'status'            => 'running',
            'options'           => $options ?: null,
            'started_at'        => now(),
            'executed_by'       => $adminId,
            'trigger_type'      => 'manual',
        ]);

        $startMs = (int) round(microtime(true) * 1000);

        try {
            $artisanParams = [];
            foreach ($options as $name => $value) {
                $artisanParams["--{$name}"] = $value;
            }

            Artisan::call($task['signature'], $artisanParams);
            $output     = trim(Artisan::output());
            $durationMs = (int) round(microtime(true) * 1000) - $startMs;

            $log->update([
                'status'      => 'success',
                'output'      => $output !== '' ? $output : null,
                'finished_at' => now(),
                'duration_ms' => $durationMs,
            ]);
        } catch (Throwable $e) {
            $durationMs = (int) round(microtime(true) * 1000) - $startMs;

            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
                'duration_ms'   => $durationMs,
            ]);
        } finally {
            $lock->release();
        }

        return $log->refresh();
    }
}
