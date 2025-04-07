<?php

namespace App\Jobs;

use App\Mail\NewPassMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResetPassMail implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */

    /**
     * Create a new job instance.
     */
    public function __construct(public string $password, public string $email)
    {
        //
    }

    public $tries = 5;

    public $timeout = 20;

    public $retryAfter = 5;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->email)->send(new NewPassMail($this->password));
            Log::info("Password reset email sent successfully to {$this->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email to {$this->email}: ".$e->getMessage());
            throw $e; // Re-throw to allow job retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error('Job ResetPassMail failed: '.$exception->getMessage());
    }
}
