<?php

namespace App\Http\Controllers;

use App\Events\testEvent;
use App\Models\Item;
use App\Models\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TradeProcessController extends Controller
{
    public function index($id)
    {

        $item = Item::with(['images', 'tags', 'category', 'user'])->where('id', $id)->first();

        if (! $item->images) {
            $item->images = [];
        }

        return Inertia::render('Trades/Forms/TradeForm', [
            'item' => $item,
        ]);
    }

    public function store(request $request)
    {
        $request->validate([
            'trade_item' => 'required|string|max:255',
            'trade_description' => 'required|string|max:1000',
            'trade_meetup' => 'required|string|max:255',
            'trade_date_meetup' => 'required|date|after:now',
            'trade_image' => 'required|array|min:1',
            'trade_image.*' => 'string',
            'item_id' => 'required|exists:items,id',
        ], [
            'trade_date_meetup.after' => 'The meetup date must be a future date and time.',
            'trade_image.required' => 'Please upload at least one image.',
        ]);

        $item = Item::with('user')->findOrFail($request->item_id);
        $user = Auth::user();
        $prevReq = $user->itemRequests()->where('item_id', $item->id)->where('status', 'pending')->first();

        if (! $prevReq) {
            $tradeRequest = $item->tradeRequests()->create([
                'name' => $request->trade_item,
                'description' => $request->trade_description,
                'place' => $request->trade_meetup,
                'time' => $request->trade_date_meetup,
                'user_id' => Auth::user()->id,
            ]);

            $item->update([
                'status' => 'requested',
            ]);
            $tempImages = TemporaryImage::whereIn('folder', values: $request->trade_image)->get();

            foreach ($tempImages as $img) {
                Storage::disk('public')->copy('images/tmp/'.$img->folder.'/'.$img->file, to: 'uploadedRequestedItemsImages/'.$img->file);
                $tradeRequest->images()->create([
                    'image' => 'uploadedRequestedItemsImages/'.$img->file,
                ]);
                Storage::disk('public')->deleteDirectory('images/tmp/'.$img->folder);
            }

            event(new testEvent($item->id, $item->item_name, 'trade_req', $item->user->id));

            return redirect('/trade')->with('success', "You've sent a trade request successfully!");
        }

        return redirect()->back()->with('error', 'You already have a pending trade request for this item.');
    }
}
