<?php

namespace App\Jobs;

use App\Events\testEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class notifJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $id, public string $itemName, public string $type, public int $userId)
    {
        //
    }

    public $tries = 5;

    public $retryAfter = 20;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new testEvent($this->id, $this->itemName, $this->type, $this->userId));
    }
}
