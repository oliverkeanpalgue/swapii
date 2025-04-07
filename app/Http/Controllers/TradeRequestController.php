<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\TemporaryImage;
use App\Models\TradeRequest;
use App\Models\TradeRequestItemImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TradeRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $search = request()->input('search');
        $itemsQuery = $user->itemRequests()
            ->with(['item.images', 'images', 'cancellation']);

        if ($search) {
            $itemsQuery->where('name', 'like', '%'.$search.'%')
                ->orWhereHas('item', function ($query) use ($search) {
                    $query->where('item_name', 'like', '%'.$search.'%');
                });
        }

        $items = $itemsQuery->latest()->get();

        $formattedItems = $items->map(function ($item) {
            $itemArray = $item->toArray();
            $itemArray['time'] = $item->getRawOriginal('time');

            return $itemArray;
        });

        $groupedItems = $formattedItems->groupBy('status');

        return Inertia::render('Trades/TradePage', [
            'items' => $formattedItems,
            'pending' => $groupedItems->get('pending', collect([]))->values()->all(),
            'processing' => $groupedItems->get('processing', collect([]))->values()->all(),
            'cancelled' => $groupedItems->get('cancelled', collect([]))->values()->all(),
            'rejected' => $groupedItems->get('rejected', collect([]))->values()->all(),
            'success' => $groupedItems->get('success', collect([]))->values()->all(),
            'filters' => request()->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new trade request.
     */
    public function create($id)
    {
        $item = Item::with(['images', 'user', 'category', 'tags'])
            ->findOrFail($id);

        return Inertia::render('Trades/Forms/TradeRequestForm', [
            'item' => $item,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $trade = TradeRequest::with(['images', 'item'])->findOrFail($id);

        // Get the raw time value
        $rawTime = $trade->getRawOriginal('time');
        $trade->time = Carbon::parse($rawTime)->toIso8601String();

        return Inertia::render('Trades/Forms/EditTradeForm', [
            'trade' => $trade,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $trade = TradeRequest::findOrFail($id);

        // Check if there are existing images or temporary images
        $hasExistingImages = $trade->images()->count() > 0;
        $hasTempImages = false;

        if ($request->has('trade_image') && ! empty($request->trade_image)) {
            $tempImages = TemporaryImage::whereIn('folder', $request->trade_image)->count();
            $hasTempImages = $tempImages > 0;
        }

        // Set validation rules based on existing or temporary images
        $imageValidation = ($hasExistingImages || $hasTempImages) ? 'nullable|array' : 'required|array';
        $imageItemValidation = ($hasExistingImages || $hasTempImages) ? 'nullable|string' : 'required|string';

        $validated = $request->validate([
            'trade_item' => 'required|string|max:255',
            'trade_meetup' => 'required|string|max:255',
            'trade_date_meetup' => 'required|date',
            'trade_description' => 'required|string',
            'trade_image' => $imageValidation,
            'trade_image.*' => $imageItemValidation,
        ]);

        // Store time in UTC
        $time = Carbon::parse($validated['trade_date_meetup'])->format('Y-m-d H:i:s');

        // Update trade request details
        $trade->update([
            'name' => $validated['trade_item'],
            'place' => $validated['trade_meetup'],
            'time' => $time,
            'description' => $validated['trade_description'],
        ]);

        // Handle image uploads if any
        if ($request->has('trade_image') && ! empty($request->trade_image)) {
            $tempImages = TemporaryImage::whereIn('folder', $request->trade_image)->get();

            foreach ($tempImages as $img) {
                Storage::disk('public')->copy(
                    'images/tmp/'.$img->folder.'/'.$img->file,
                    'uploadedTradeImages/'.$img->file
                );

                // Create new image record instead of updating existing one
                $trade->images()->create([
                    'image' => 'uploadedTradeImages/'.$img->file,
                ]);

                // Delete the temporary image
                Storage::disk('public')->deleteDirectory('images/tmp/'.$img->folder);
                $img->delete();
            }
        }

        return redirect()->route('trade.index');
    }

    /**
     * Delete a trade request image.
     */
    public function deleteImage(string $id)
    {
        $image = TradeRequestItemImage::findOrFail($id);

        // Delete the file from storage
        if (Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        // Delete the database record
        $image->delete();

        return redirect()->back();
    }
}
