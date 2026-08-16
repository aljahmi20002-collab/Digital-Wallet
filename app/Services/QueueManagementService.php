<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class QueueManagementService
{
    public function getFailedJobs(int $perPage = 20): LengthAwarePaginator
    {
        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate($perPage);
    }

    public function getFailedJobsCount(): int
    {
        return DB::table('failed_jobs')->count();
    }

    public function retryJob(string $id): void
    {
        Artisan::call('queue:retry', ['id' => [$id]]);
    }

    public function retryAll(): void
    {
        Artisan::call('queue:retry', ['id' => ['all']]);
    }

    public function forgetJob(string $id): void
    {
        Artisan::call('queue:forget', ['id' => $id]);
    }

    public function flushAll(): void
    {
        Artisan::call('queue:flush');
    }
}
