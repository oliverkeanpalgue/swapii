<?php

namespace App\Listeners;

use App\Events\ModNewNotifEvent;
use App\Events\ModPostEvent;
use App\Models\ModNotif;

class ModPostListener
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
    public function handle(ModPostEvent $event): void
    {
        $message = ModNotif::create([
            'message' => $event->item_name.' has been posted, review it!',
            'item_id' => $event->item_id,
        ]);

        event(new ModNewNotifEvent($message));
    }
}
