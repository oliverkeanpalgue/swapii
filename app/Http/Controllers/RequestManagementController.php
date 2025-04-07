<?php

namespace App\Http\Controllers;

use App\Jobs\notifJob;
use App\Models\Item;
use App\Models\TradeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RequestManagementController extends Controller
{
    public function index($id)
    {
        $item = Item::with(['images', 'tradeRequests' => function ($query) {
            $query->whereNotIn('status', ['rejected', 'cancelled'])->latest();
        }, 'tradeRequests.images', 'tradeRequests.user', 'category', 'tags', 'tradeRequests.cancellation'])
            ->withCount('tradeRequests')
            ->where('id', $id)
            ->first();

        if ($item->tradeRequests) {
            $item->tradeRequests->transform(function ($request) {
                $requestArray = $request->toArray();
                $requestArray['time'] = $request->getRawOriginal('time');

                return $requestArray;
            });
        }

        return Inertia::render('Post/NumberOfReq', ['item' => $item]);
    }

    public function accept(TradeRequest $req)
    {
        $update = $req->update([
            'status' => 'processing',
        ]);

        $req->item->update([
            'status' => 'requested',
        ]);

        if (! $update) {
            return back()->with('error', 'Something went wrong, try again!');
        }

        dispatch(new notifJob($req->item->id, $req->item->item_name, 'trade_confirm', $req->user_id));
    }

    public function updateTradeRequestStatus(TradeRequest $req, string $status)
    {
        $req->update(['status' => $status]);

        $item = $req->item;

        if ($item->tradeRequests()->whereNotIn('status', ['cancelled', 'rejected'])->doesntExist()) {
            $item->update(['status' => 'available']);
        }
    }

    public function cancel(TradeRequest $req)
    {
        $this->updateTradeRequestStatus($req, 'cancelled');

        // Update to match the frontend parameter name
        if (request()->has('cancel_reason')) {
            $req->cancellation()->create([
                'reason' => request()->cancel_reason,
            ]);
        }

        dispatch(new notifJob($req->item->id, $req->item->item_name, 'trade_cancel', $req->user_id));
    }

    public function cancelRequestor(TradeRequest $req)
    {
        $this->updateTradeRequestStatus($req, 'cancelled');
        dispatch(new notifJob($req->item->id, $req->item->item_name, 'trade_cancel', $req->item->user_id));
    }

    public function reject(TradeRequest $req)
    {
        // dd('reached');
        $this->updateTradeRequestStatus($req, 'rejected');
        dispatch(new notifJob($req->item->id, $req->item->item_name, 'trade_reject', $req->user_id));
    }

    public function markComplete(TradeRequest $req)
    {
        $isRequestor = Auth::id() === $req->user_id;

        // Update the completion status based on who is marking complete
        if ($isRequestor) {
            $req->requestor_completed = true;
            $req->save();
        } else {
            $req->owner_completed = true;
            $req->save();
        }

        // Check if both parties have completed
        if ($req->requestor_completed && $req->owner_completed) {
            // Update trade request and item statuses
            $req->status = 'success';
            $req->save();

            $req->item->update([
                'status' => 'traded',
            ]);

            // Reject other trade requests for this item
            TradeRequest::where('item_id', $req->item_id)
                ->where('id', '!=', $req->id)
                ->where('status', '!=', 'cancelled')
                ->update([
                    'status' => 'rejected',
                ]);

            // Handle image for the original item owner (current authenticated user)
            $imagePath = $this->copyItemImage($req->item);
            $imagePath2 = $this->copyItemImage($req);

            // Store acquired item for the original item owner
            Auth::user()->acquiredItems()->create([
                'item_name' => $req->name,
                'item_image' => $imagePath2,
                'trader' => $req->user->name,
            ]);

            // Store acquired item for the trade requestor
            $req->user->acquiredItems()->create([
                'item_name' => $req->item->item_name,
                'item_image' => $imagePath,
                'trader' => $req->item->user->name,
            ]);

            // Dispatch notification jobs for both users
            dispatch(new notifJob($req->item->id, $req->item->item_name, 'trade_success', $req->user_id));
            dispatch(new notifJob($req->item->id, $req->item->item_name, 'trade_success', $req->item->user_id));

            return back()->with('message', 'Trade completed successfully');
        }

        return back()->with('message', 'Trade marked as complete, waiting for other party');
    }

    public function complete(TradeRequest $req)
    {
        // Update trade request and item statuses
        $req->update([
            'status' => 'success',
        ]);

        $req->item->update([
            'status' => 'traded',
        ]);

        // Reject other trade requests for this item
        TradeRequest::where('item_id', $req->item_id)
            ->where('id', '!=', $req->id)
            ->where('status', '!=', 'cancelled')
            ->update([
                'status' => 'rejected',
            ]);

        // Handle image for the original item owner (current authenticated user)
        $imagePath = $this->copyItemImage($req->item);
        $imagePath2 = $this->copyItemImage($req);
        // Store acquired item for the original item owner
        Auth::user()->acquiredItems()->create([
            'item_name' => $req->name,
            'item_image' => $imagePath2,
            'trader' => $req->user->name, // The person who requested the trade
        ]);

        // Store acquired item for the trade requestor
        $req->user->acquiredItems()->create([
            'item_name' => $req->item->item_name,
            'item_image' => $imagePath,
            'trader' => $req->item->user->name, // The original item owner
        ]);

        // Dispatch notification jobs for both users
        dispatch(new notifJob($req->item->id, $req->item->item_name, 'trade_success', $req->user_id));
        dispatch(new notifJob($req->item->id, $req->name, 'trade_success', $req->item->user_id));
    }

    /**
     * Copy item image and return the new path
     *
     * @param  Item  $item
     * @return string|null
     */
    private function copyItemImage($item)
    {
        $imagePath = null;
        $originalImage = $item->images->first();

        if ($originalImage && Storage::disk('public')->exists($originalImage->image)) {
            try {
                // Generate unique filename
                $newFilename = 'acquired_'.Str::uuid().'_'.Str::slug($item->item_name).'.'.
                               pathinfo($originalImage->image, PATHINFO_EXTENSION);

                // Ensure the Acquired Items directory exists
                Storage::disk('public')->makeDirectory('acquired_items');

                // Copy with error handling
                Storage::disk('public')->copy(
                    $originalImage->image,
                    'acquired_items/'.$newFilename
                );

                $imagePath = 'acquired_items/'.$newFilename;
            } catch (\Exception $e) {
                // Log the error instead of silently failing
                Log::error('Failed to copy acquired item image: '.$e->getMessage());
            }
        }

        return $imagePath;
    }

    public function show($id)
    {
        $trade_request = TradeRequest::with(['images', 'user', 'item'])
            ->findOrFail($id);

        if ($trade_request) {
            $requestArray = $trade_request->toArray();
            $requestArray['time'] = $trade_request->getRawOriginal('time');
            $trade_request = $requestArray;
        }

        return Inertia::render('Item/TradeDescription', [
            'trade_request' => $trade_request,
        ]);
    }
}
