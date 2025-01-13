<?php

namespace App\Listeners;

use App\Jobs\ProcessTransactionJob;

class HandleTransactionSuccess
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        ProcessTransactionJob::dispatch($event->transactionData);
    }
}
