<?php

namespace App\Listeners;

use App\Events\newNotifEvent;
use App\Events\testEvent;
use App\Models\Notification;

class testListener
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
    public function handle(testEvent $event): void
    {

        $data = match ($event->type) {
            'trade_req' => 'There is a new request for your item: '.$event->itemName,
            'trade_reject' => 'Your trade request for '.$event->itemName.' has been rejected',
            'trade_confirm' => 'Your trade request for '.$event->itemName.' has been accepted',
            'trade_cancel' => 'The trade request for '.$event->itemName.' has been cancelled',
            'trade_success' => $event->itemName.' Has been traded successfully',
            'item_unlist' => 'Your item: '.$event->itemName.' has been unlisted due to lack of requests, you can still restore this item',
            default => null,
        };

        if ($data) {
            $notif = Notification::create([
                'user_id' => $event->userId,
                'type' => $event->type,
                'data' => $data,
                'item_id' => $event->id,
            ]);

            event(new newNotifEvent($notif));
        }
    }
}
