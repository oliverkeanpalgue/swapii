<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Rating;
use App\Models\TradeRequest;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TraderController extends Controller
{
    public function trader($id)
    {
        $authUserId = auth()->id();

        if ($id == $authUserId) {
            return back();
        }

        $user = User::with(['items' => function ($query) {
            $query->where('status', '!=', 'traded')
                ->with(['images', 'tags']);
        }, 'ratings'])->findOrFail($id);

        $hasRated = $user->ratings()->where('user_id', $authUserId)->exists();

        $ratingsCount = $user->ratings()->count();
        $successfulTrades = $this->getSuccessfulTradesCount($id);

        return Inertia::render('TraderProfile/TraderProfile', [
            'user' => $user,
            'averageRating' => number_format($user->averageRating(), 2),
            'ratingsCount' => $ratingsCount,
            'hasRated' => $hasRated,
            'successfulTrades' => $successfulTrades,
            'isVerified' => $user->is_verified,
        ]);
    }

    /**
     * Get the count of successful trades for a user
     *
     * @param  int  $userId
     * @return int
     */
    private function getSuccessfulTradesCount($userId)
    {
        return TradeRequest::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('status', 'success');
        })
            ->orWhereHas('item', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'traded');
            })
            ->count();
    }

    public function ratingStore(Request $request)
    {
        $request->validate([
            'rated_user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        Rating::create([
            'user_id' => auth()->id(),
            'rated_user_id' => $request->rated_user_id,
            'trade_request_id' => $request->trade_request_id,
            'rating' => $request->rating,
            'description' => $request->description,
            'is_anonymous' => $request->is_anonymous ?? false,
        ]);

        return back()->with('success', 'Rating submitted successfully');
    }

    public function traderRatings($id)
    {
        $user = User::findOrFail($id);
        $ratings = Rating::with(['user', 'tradeRequest'])
            ->where('rated_user_id', $id)
            ->latest()
            ->get()
            ->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'description' => $rating->description,
                    'created_at' => $rating->getRawOriginal('created_at'),
                    'user' => $rating->is_anonymous ? $this->anonymizeName($rating->user->name) : $rating->user->name,
                    'is_anonymous' => $rating->is_anonymous,
                    'trade' => $rating->tradeRequest ? [
                        'id' => $rating->tradeRequest->id,
                        'name' => $rating->tradeRequest->name,
                    ] : null,
                ];
            });

        return Inertia::render('TraderProfile/Rating', [
            'ratings' => $ratings,
            'averageRating' => number_format($user->averageRating(), 2),
            'ratingsCount' => $user->ratings()->count(),
        ]);
    }

    private function anonymizeName($name)
    {
        if (! $name) {
            return 'Unknown User';
        }

        $parts = explode(' ', $name);
        if (count($parts) < 2) {
            return str_repeat('*', strlen($name));
        }

        $firstName = $parts[0];
        $lastName = end($parts);

        $firstAnon = $firstName[0].str_repeat('*', strlen($firstName) - 1);
        $lastAnon = $lastName[0].str_repeat('*', strlen($lastName) - 1);

        return $firstAnon.' '.$lastAnon;
    }

    public function reportStore(Request $request, $id)
    {
        $validated = $request->validate([
            'concern' => 'required|string|in:Rude/Abusive,Spam,Exposing Personal Information,Offensive Content,Other',
            'description' => 'required|string|max:1000',
            'proofs' => 'nullable|array',
            'proofs.*' => 'nullable|file|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);

        // Create the report with both reported and reporter IDs
        $report = new UserReport([
            'user_id' => $id, // User being reported
            'reporter_id' => auth()->id(), // User making the report
            'concern' => $validated['concern'],
            'description' => $validated['description'],
        ]);

        // Save the report
        $report->save();

        // Handle file uploads if any
        if ($request->hasFile('proofs')) {
            foreach ($request->file('proofs') as $proof) {
                $path = $proof->store('report-proofs', 'public');
                $report->images()->create([
                    'image' => $path,
                ]);
            }
        }

        return back()->with('success', 'Report submitted successfully');
    }

    public function reportPostStore(Item $item, Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'concern' => 'required|string|in:Rude/Abusive,Spam,Exposing Personal Information,Offensive Content,Other',
            'description' => 'required|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $report = $item->reports()->create([
            'user_id' => auth()->id(),
            'concern' => $validated['concern'],
            'description' => $validated['description'],
        ]);

        if ($request->hasFile('proofs')) {
            foreach ($request->file('proofs') as $image) {
                $filename = time().'_'.$image->getClientOriginalName();
                $path = $image->storeAs('post-report-images', $filename, 'public');

                if ($path) {
                    $report->images()->create([
                        'image' => $path,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Report submitted successfully');
    }
}
