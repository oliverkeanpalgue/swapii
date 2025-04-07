<?php

namespace App\Jobs;

use App\Events\ModPostEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ModPostJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $item_name, public int $item_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new ModPostEvent($this->item_name, $this->item_id));
    }
}
