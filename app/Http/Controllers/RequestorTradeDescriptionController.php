<?php

namespace App\Http\Controllers;

use App\Models\TradeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RequestorTradeDescriptionController extends Controller
{
    public function show($id)
    {
        $trade_request = TradeRequest::with([
            'images',
            'user',
            'item.images',
            'item.user',
            'cancellation',
        ])
            ->where('id', $id)
            ->where('user_id', Auth::id()) // Ensure the requestor is viewing their own request
            ->firstOrFail();

        // Format the time
        $trade_request = $trade_request->toArray();
        $trade_request['time'] = TradeRequest::find($id)->getRawOriginal('time');

        return Inertia::render('Item/RequestorSideTradeDescription', [
            'trade_request' => $trade_request,
        ]);
    }
}
