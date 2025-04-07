<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class GetNotificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $notifs = Notification::where('user_id', auth()->user()->id)->latest()->limit(5)->get();

        return response()->json($notifs);
    }
}
