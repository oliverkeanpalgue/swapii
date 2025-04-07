<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\MostViewedItemsController;
use App\Http\Controllers\DashBoardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemDescriptionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestManagementController;
use App\Http\Controllers\RequestorTradeDescriptionController;
use App\Http\Controllers\ShowNotifItem;
use App\Http\Controllers\SmartSuggestionController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\TradeProcessController;
use App\Http\Controllers\TraderController;
use App\Http\Controllers\TradeRequestController;
use App\Http\Controllers\UploadTempImgController;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

//? AUTHENTICATED USER ROUTE GROUP ==================================================

Route::middleware(['auth', 'user', 'prevent-back', 'change-pass', 'verified'])->group(function () {

    // CHAT ROUTES =============================================
    Route::get('/friends', [ChatController::class, 'friends'])->name('chat.friends');
    Route::get('/chat/{id}', [ChatController::class, 'chat'])->name('chat.messages');
    Route::post('/send-msg/{id}', [ChatController::class, 'sendMsg'])->name('chat.send');
    Route::get('/room-messages/{id}', [ChatController::class, 'chatRoomMessages']);
    Route::post('/send-msg-img/{id}', [ChatController::class, 'sendMsgImg'])->name('chat.send.img');

    // POST MANAGEMENT =============================================
    Route::get('/post-item', function () {
        return Inertia::render('Post/AddProductForm');
    })->name('post-item');
    Route::resource('/post-management', PostController::class);
    Route::get('/edit-post/{id}', [PostController::class, 'edit']);
    Route::delete('/delete-post/{id}', [PostController::class, 'destroy']);
    Route::patch('/reupload-post/{id}', [PostController::class, 'reupload'])->name('post.reupload');
    Route::patch('/unlist-post/{id}', [PostController::class, 'unlist'])->name('post.unlist');
    Route::put('/post-management/{id}/resubmit', [PostController::class, 'resubmit'])->name('post.resubmit');

    // TRADE MANAGEMENT =============================================
    Route::get('/trade-list/{id}', [RequestManagementController::class, 'index'])->name('trade.list.show');
    Route::get('/trade-description/{id}', [RequestManagementController::class, 'show'])->name('trade-description');
    Route::get('/requestor-trade-description/{id}', [RequestorTradeDescriptionController::class, 'show'])->name('requestor-trade-description.show');
    Route::patch('/trade-reject/{req}', [RequestManagementController::class, 'reject'])->name('trade-reject');
    Route::patch('/trade-cancel/{req}', [RequestManagementController::class, 'cancel'])->name('trade-cancel');
    Route::patch('/trade-cancel/requestor/{req}', [RequestManagementController::class, 'cancelRequestor'])->name('trade.cancel.requestor');
    Route::patch('/trade-accept/{req}', [RequestManagementController::class, 'accept'])->name('trade-accept');
    Route::patch('/trade-complete/{req}', [RequestManagementController::class, 'complete'])->name('trade-complete');
    Route::post('/trade-requests/{req}/mark-complete', [RequestManagementController::class, 'markComplete'])->name('trade-requests.mark-complete');
    Route::delete('/trade-image/{id}', [TradeRequestController::class, 'deleteImage'])->name('trade.delete-image');
    Route::put('/edit-request/{id}', [TradeRequestController::class, 'update'])->name('trade.update');

    // CATEGORIES AND TAGS =============================================
    Route::get('/categories_list', function () {
        return Category::all();
    });
    Route::get('/tags', function () {
        return Tag::all();
    });

    Route::get('/fetch-items/{count}', [DashBoardController::class, 'getItems'])->name('items.fetch');
    Route::get('/fetch-recommendations', [DashBoardController::class, 'getRecommendations'])->name('recommendations.fetch');

    // ITEM DESCRIPTION =============================================
    Route::get('/item-description/{id}', ItemDescriptionController::class)->name('item.description');

    // FRIENDS AND ITEMS =============================================
    Route::resource('/items', ItemController::class);

    // TEMPORARY IMAGE UPLOAD =============================================
    Route::post('/upload-temp-images', [UploadTempImgController::class, 'uploadTempImg']);
    Route::delete('/revert/{folder}', [UploadTempImgController::class, 'revert']);
    Route::delete('/revert-all', [UploadTempImgController::class, 'revertAll']);
    // Route::middleware('can:fully-verified')->group(function () {
    // });

    // TAG MANAGEMENT =============================================
    Route::post('/add-new-tags', function (Request $request) {
        $existing = Tag::where('tag', $request->tag)->first();

        if (! $existing) {
            Tag::create([
                'tag' => $request->tag,
                'code' => $request->code,
            ]);

        }
    });

    // NOTIFICATION ROUTES ====================================
    Route::get('/get-notifications', function () {
        try {
            $notifications = auth()->user()->notifications()
                ->with('item')
                ->where('is_read', false)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'data' => $notification->data,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                        'item' => $notification->item,
                    ];
                });

            return response()->json($notifications);
        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: '.$e->getMessage());

            return response()->json([], 500);
        }
    })->name('notifications.get');

    // NOTIFICATIONS AND FEEDBACK =============================================
    Route::get('/notification', function () {
        return Inertia::render('Notification/ManageNotification', [
            'notifications' => auth()->user()->notifications()
                ->with('item')
                ->latest()
                ->paginate(15)
                ->through(fn ($notif) => [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'data' => $notif->data,
                    'created_at' => $notif->created_at,
                    'item_name' => $notif->item->item_name,
                    'item_id' => $notif->item_id,
                ]),
        ]);
    })->middleware(['auth', 'throttle:60,1']); // Rate limit: 60 requests per minute

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // TRADE REQUESTS =============================================
    Route::get('/trade', [TradeRequestController::class, 'index'])->name('trade.index');
    Route::get('/edit-request/{id}', [TradeRequestController::class, 'edit']);
    Route::get('/request-trade/{id}', [TradeProcessController::class, 'index'])->name('request.trade');
    Route::post('/send-request', [TradeProcessController::class, 'store']);

    // USER PROFILE =============================================
    Route::get('/view-user/{id}', [TraderController::class, 'trader'])->name('trader');
    Route::post('/rating', [TraderController::class, 'ratingStore'])->name('ratings.store');
    Route::get('/rating_list/{id}', [TraderController::class, 'traderRatings'])->name('trader.ratings');
    Route::post('/report-user/{id}', [TraderController::class, 'reportStore'])->name('reports.store');
    Route::post('/report-item/{item}', [TraderController::class, 'reportPostStore'])->name('reports.post.store');
    // Route::get('/report-user', fn() => Inertia::render('TraderProfile/ReportUser'));

    // PROFILE MANAGEMENT =============================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/verify', [ProfileController::class, 'verify'])->name('profile.verify');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // DELETE ITEM IMAGE =============================================
    Route::delete('/edit-image-delete/{item}', function (ItemImage $item) {
        $filePath = $item->image;
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
            $item->delete();

            return back()->with('success', 'Image deleted Successfully');
        } else {
            return back()->with('error', 'image not found');
        }
    });

    // TESTING ROUTE FOR LEGAL INFORMATION
    //Route::get('/legal-information', fn() => Inertia::render('LegalInformation/LegalInformation'));

    // CHANGE PASSWORD ==============================================

    //ACQUIRED ITEMS ==============================================
    Route::get('/acquired-items', function () {
        $acquiredItems = auth()->user()->acquiredItems()
            ->latest()
            ->paginate(8)
            ->map(fn ($acquiredItem) => [
                'id' => $acquiredItem->id,
                'item_name' => $acquiredItem->item_name,
                'created_at' => $acquiredItem->created_at,
                'item_image' => $acquiredItem->item_image,
                'trader' => $acquiredItem->trader,
            ]);

        return Inertia::render('AcquiredItems/Items', [
            'acquiredItems' => $acquiredItems,
        ]);
    });

    //NOTIFICATION REDIRECTS ============================
    Route::get('/notif-read/{notif}', ShowNotifItem::class)->name('notif.show');

    Route::get('/suggested-items', [SuggestionController::class, 'index'])->name('suggested-items');

    // MOST VIEWED ITEMS ========================
    Route::get('/most-viewed-items', MostViewedItemsController::class)->name('most-viewed-items');
    Route::get('/view-count/{item}', function(Item $item) {
        return $item->view_count;
    })->name('view-count');


    //SMART SUGGESTIONS =========================
    Route::get('/smart-suggestions',[ SmartSuggestionController::class, 'index'])->name('smart-suggestions');
});
