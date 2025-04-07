<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class MostViewedItemsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $items = Item::with(['tags', 'images'])
            ->where('user_id', '!=', Auth::user()->id)
            ->whereDoesntHave('tradeRequests', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })
            ->whereIn('status', ['requested', 'available'])
            ->orderBy('view_count', 'desc')->take(4)->get();

        return response()->json($items);
    }
}
