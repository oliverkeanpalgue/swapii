<?php

use App\Jobs\notifJob;
use App\Models\Item;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    logger('Scheduler is running');
})->everyTenSeconds();

// New scheduled task to update item status
// Schedule::call(function () {
//     $weekAgo = now()->subWeek();

//     // Find items with no trade requests in the last week
//     $inactiveItems = Item::whereDoesntHave('tradeRequests', function ($query) use ($weekAgo) {
//         $query->where('created_at', '>=', $weekAgo);
//     })
//     ->where('status', '!=', 'unlisted')
//     ->update(['status' => 'unlisted']);

//     logger('Items without requests for a week have been set to unlisted', [
//         'count' => $inactiveItems
//     ]);
// })->daily();

Schedule::call(function () {
    $minAgo = now()->subDays(7);

    // Use chunks to handle large datasets efficiently
    Item::where('status', 'available')
        ->where('updated_at', '<=', $minAgo)
        ->whereDoesntHave('tradeRequests', function ($query) use ($minAgo) {
            $query->where('created_at', '>=', $minAgo);
        })
        ->chunkById(100, function ($items) {
            // Collect IDs for bulk update
            $itemIds = $items->pluck('id')->toArray();

            // Bulk update status
            Item::whereIn('id', $itemIds)
                ->update(['status' => 'unlisted']);

            // Dispatch notifications in bulk
            $jobs = $items->map(function ($item) {
                return new notifJob($item->id, $item->item_name, 'item_unlist', $item->user_id);
            })->toArray();

            Bus::chain($jobs)->dispatch();
        });
})->daily();
