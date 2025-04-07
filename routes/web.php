<?php

use App\Http\Controllers\CategoryItemController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CreateCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GetNotificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//? CREATING THE DEFAULT CATEGORIES VALUES  -------------

Route::middleware(['user', 'prevent-back', 'change-pass'])->group(function () {
    Route::post('/create_categories', CreateCategoryController::class)->name('categories.create');
    Route::get('/get-notifications', GetNotificationController::class);
    Route::get('/item-category/{id}', [CategoryItemController::class, 'index']);
    Route::get('/legal-information', fn () => Inertia::render('LegalInformation/LegalInformation'));
    Route::post('/messages/mark-as-read', [ChatController::class, 'markAsRead']);
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

// Dashboard route for both guests and authenticated users

// Authenticated dashboard route with verified middleware
Route::middleware(['auth', 'prevent-back', 'change-pass', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('authenticated.dashboard');
});

//? EMAIL VERIFICATION ==================================================
// Route::middleware('auth')->group(function () {
//     Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');

//     Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//         $request->fulfill();
//         $request->user()->sendEmailVerificationNotification();
//         return redirect('/home');
//     })->middleware('signed')->name('verification.verify');

//     Route::post('/email/verification-notification', function (Request $request) {
//         $request->user()->sendEmailVerificationNotification();
//         return back()->with('message', 'Verification link sent!');
//     })->middleware('throttle:6,1')->name('verification.send');
// });

Route::get('/post/items', function (Request $request) {
    return app(DashboardController::class)->getItems($request->query('count', 4));
});
