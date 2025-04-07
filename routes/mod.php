<?php

use App\Http\Controllers\CategoryItemController; // Importing necessary classes
use App\Http\Controllers\FetchMonthlyFigureController; // Importing the Item model
use App\Http\Controllers\ModeratorProfileController; // Add this import at the top
use App\Http\Controllers\ModPostManagementController; // Importing Inertia
use App\Http\Controllers\PostReportManagementController;
use App\Models\Category;
use App\Models\Item;
use App\Models\ModNotif;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//! MODERATOR ROUTE GROUP ===============================================================

Route::middleware(['auth', 'web', 'mod', 'verified', 'prevent-back'])->group(function () {
    Route::get('/moderator', function () {
        $categoryCount = Category::count();

        // Calculate post statistics directly
        $postStats = [
            'totalPosts' => Item::count(),
            'approvedCount' => Item::whereIn('status', ['available', 'requested', 'traded'])->count(),
            'rejectedCount' => Item::where('status', 'rejected')->count(),
        ];

        $topCategories = Category::withCount('items')
            ->whereHas('items')
            ->orderByDesc('items_count')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->category,
                    'count' => $category->items_count,
                    'category_image' => $category->category_image,
                ];
            });

        return Inertia::render('Moderator/Dashboard', [
            'categoryCount' => $categoryCount,
            'postStats' => $postStats,
            'topCategories' => $topCategories,
        ]);
    })->name('moderator');

    Route::get('/moderator/category', function () {
        $categories = Category::withCount('items')
            ->latest()
            ->paginate(10);

        return Inertia::render('Moderator/Category/CategoryManagement', [
            'categories' => $categories,
        ]);

    });

    Route::get('/moderator/notification', fn () => Inertia::render('Moderator/Notification/ManageNotification'));
    Route::get('/moderator/notifs', function () {
        return ModNotif::latest()->limit(5)->get();
    })->name('mod.notif');

    Route::put('/moderator/category/{id}', [CategoryItemController::class, 'update'])->name('category.update');
    Route::post('/moderator/add-category', [CategoryItemController::class, 'store'])->name('category.store');
    Route::get('/moderator/view-category-list/{id}', [CategoryItemController::class, 'show'])->name('category.show');
    Route::get('/moderator/edit-category/{id}', [CategoryItemController::class, 'edit'])->name('category.edit');
    Route::delete('/moderator/category/{id}', [CategoryItemController::class, 'destroy'])->name('category.destroy');
    Route::get('/moderator/post', [ModPostManagementController::class, 'index'])->name('mod.post.index');

    Route::patch('/moderator/post/{item}', [ModPostManagementController::class, 'acceptPost'])->name('mod.post.accept');
    Route::patch('/moderator/post/{item}/reject', [ModPostManagementController::class, 'rejectPost'])->name('mod.post.reject');
    Route::get('/moderator/fetch-monthly-figures', FetchMonthlyFigureController::class);
    Route::get('/moderator/item-description/{id}', [ModPostManagementController::class, 'show'])->name('mod.post.show');

    // // ITEM DESCRIPTION =============================================
    // Route::get('/moderator/item-description/{id}', function ($id) {
    //     $item = Item::with(['tags', 'images', 'user', 'category'])->where('id', $id)->first();
    //     return Inertia::render('Moderator/Posts/PostDescription', ['item' => $item]);
    // });
    // Profile Routes
    Route::get('/moderator/profile', [ModeratorProfileController::class, 'edit'])->name('mod.profile.edit');
    Route::post('/moderator/profile', [ModeratorProfileController::class, 'update'])->name('mod.profile.update');
    Route::delete('/moderator/profile', [ModeratorProfileController::class, 'destroy'])->name('mod.profile.destroy');

    // POST REPORTS MANAGEMENT =============================================
    Route::get('/moderator/post-reports', [PostReportManagementController::class, 'index'])->name('mod.post.reports.index');
    Route::patch('/moderator/post-reports/unlist/{report}', [PostReportManagementController::class, 'unlist'])->name('mod.post.reports.unlist');
    Route::patch('/moderator/post-reports/dismiss/{report}', [PostReportManagementController::class, 'dismiss'])->name('mod.post.reports.dismiss');

    //NOTIFICATION REDIRECT ==============
    Route::get('/moderator/notification/{id}', function ($id) {
        $notification = ModNotif::findOrFail($id);
        // dd($notification->item->item_name);
        $notification->update(['is_read' => true]);

        return redirect()->route('mod.post.show', $notification->item_id);
    })->name('mod.notif.show');
});
