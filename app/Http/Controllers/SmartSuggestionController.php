<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Preference;
use App\Models\Item;

class SmartSuggestionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch the user's posted items and split item names into individual words
        $userPostedItems = [];
        $userItems = $user->items()->pluck('item_name')->toArray();
        foreach ($userItems as $itemName) {
            $terms = preg_split('/\s+/', $itemName); // Split by whitespace
            $userPostedItems = array_merge($userPostedItems, $terms); // Merge into a single array
        }

        // dd($userPostedItems);

        // Find items posted by other users that have preferences matching the user's posted item names
        $matchingItems = Item::with(['tags', 'images'])
            // ->whereIn('item_name', $userPostedItems)
            ->whereHas('preferences', function ($query) use ($userPostedItems) {
                $query->whereIn('preference', $userPostedItems);

                // foreach ($userPostedItems as $term) {
                //     $query->orWhere('preference', 'LIKE', '%' . $term . '%'); // Use LIKE for partial matches
                // }
            })
            ->whereDoesntHave('tradeRequests', function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->whereIn('status', ['pending', 'processing']);
            })
            ->whereIn('status', ['pending', 'available', 'requested']) // Filter by status
            ->where('user_id', '!=', $user->id) // Exclude items created by the authenticated user
            ->get();

        return inertia('SmartSuggestion/Page', ['items' => $matchingItems]); // Pass items to the view
    }
}
