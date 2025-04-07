<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class ShowNotifItem extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Notification $notif)
    {
        $notif->update(['is_read' => true]);

        // Redirect based on notification type
        switch ($notif->type) {
            case 'trade_req':
                return redirect()->route('trade.list.show', $notif->item_id);

            case 'trade_reject':
            case 'trade_cancel':
                return redirect()->route('dashboard');
            case 'trade_confirm':
                return redirect()->route('trade.index');
            case 'item_unlist':
                return redirect()->route('post-management.index');
            case 'mod_post':
                return redirect()->route('mod.post.show', $notif->item_id);

            default:
                return redirect()->route('item.description', $notif->item_id);
        }
    }
}
