<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TestQueueJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('TestQueueJob executed successfully at '.now());
        file_put_contents(storage_path('logs/queue_test.log'), 'Queue job executed: '.now().PHP_EOL, FILE_APPEND);
    }
}
