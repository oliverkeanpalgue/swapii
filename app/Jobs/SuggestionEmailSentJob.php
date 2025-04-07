<?php

namespace App\Jobs;

use App\Mail\SuggestionReceivedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SuggestionEmailSentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $email)
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
        Mail::to($this->email)->send(new SuggestionReceivedMail);
    }
}
