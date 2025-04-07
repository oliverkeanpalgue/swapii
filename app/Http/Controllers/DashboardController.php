<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\UserTag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{

    protected function getItems()
    {
        sleep(3);
        if (Auth::user()) {
            return Item::with(['tags', 'images'])
                ->whereDoesntHave('tradeRequests', function ($query) {
                    $query->where('user_id', Auth::user()->id)
                        ->whereIn('status', ['pending', 'processing']);
                })
                ->whereIn('status', ['pending', 'requested', 'available'])
                ->withCount(['tradeRequests' => function ($query) {
                    $query->whereIn('status', ['pending', 'processing']);
                }])
                ->whereNot('user_id', Auth::user()->id)
                ->paginate(8);
        }

        return Item::with(['tags', 'images'])
            ->withCount('tradeRequests')
            ->paginate(8);
    }

    protected function getMostViewedItems()
    {
        $query = Item::query()->with(['tags', 'images']);
        if(Auth::user())
        {

            $items = $query->where('user_id', '!=', Auth::user()->id)
                ->whereDoesntHave('tradeRequests', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })
                ->whereIn('status', ['requested', 'available'])
                ->orderBy('view_count', 'desc')->take(4)->get();
    
            
        }
        else
        {
            $items = $query->whereIn('status', ['requested', 'available'])
                ->orderBy('view_count', 'desc')->take(4)->get();
    
           
        }

        return $items;
    }

    public function index()
    {
        // $suggestedItems = $this->getRecommendations();
        // $items = Inertia::merge(fn() => $this->getItems()->items());
        $items = Inertia::defer(fn() => $this->getItems());
        // $currentPage = $this->getItems()->currentPage();
        $categories = Category::all();
        $mostViewedItems = $this->getMostViewedItems();
        return Inertia::render('Dashboard/Dashboard', [
            // 'suggestedItems' => $suggestedItems,
            'categories' => $categories,
            'mostViewedItems' => $mostViewedItems,
            'items' => $items,
            // 'currentPage' => $currentPage,
        ]);
    }

}
