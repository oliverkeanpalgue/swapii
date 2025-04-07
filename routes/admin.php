<?php

use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\ReportManagementController;
use App\Http\Controllers\ReportStatisticsController;
use App\Http\Controllers\ResetPassController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\UsersController;
use App\Models\ActionLog;
use App\Models\Feedback;
use App\Models\TradeRequest;
use App\Models\User;
use App\Models\UserReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//! ADMIN ROUTE GROUP ==================================================

Route::middleware(['prevent-back', 'auth', 'admin',  'verified'])->group(function () {
    // Add this new route for report statistics
    Route::get('/api/report-statistics', [ReportStatisticsController::class, 'getReportStats']);

    Route::get('/admin', function (Request $request) {

        $userCounts = User::selectRaw('
            COUNT(*) as totalUsers,
            SUM(role = 0) as adminCount,
            SUM(role = 1) as moderatorCount,
            SUM(role = 2) as userCount
        ')->first();

        $reportCounts = UserReport::selectRaw('
            COUNT(*) as totalReports,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pendingReports,
            SUM(CASE WHEN status = "processed" THEN 1 ELSE 0 END) as processedReports,
            SUM(CASE WHEN status = "dismissed" THEN 1 ELSE 0 END) as dismissedReports
        ')->first();

        $totalReports = UserReport::count();

        // Get report statistics with default monthly range
        $reportStatsController = new ReportStatisticsController;
        $reportStats = $reportStatsController->getReportStats(new Request([
            'range' => 'monthly',
            'year' => date('Y'),
        ]));

        $monthlyData = TradeRequest::selectRaw('
            MONTH(created_at) as month,
            COUNT(*) as total_trades,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_trades,
            SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_trades,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_trades,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_trades,
            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_trades
        ')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $formattedData = collect(range(1, 12))->map(function ($month) use ($monthlyData) {
            $monthData = $monthlyData->first(function ($data) use ($month) {
                return $data->month == $month;
            });

            return [
                'month' => $month,
                'total_trades' => $monthData ? $monthData->total_trades : 0,
                'pending_trades' => $monthData ? $monthData->pending_trades : 0,
                'processing_trades' => $monthData ? $monthData->processing_trades : 0,
                'successful_trades' => $monthData ? $monthData->successful_trades : 0,
                'cancelled_trades' => $monthData ? $monthData->cancelled_trades : 0,
                'rejected_trades' => $monthData ? $monthData->rejected_trades : 0,
            ];
        });

        $feedbacks = Feedback::with('user')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'title' => $feedback->title,
                    'feedback' => $feedback->feedback,
                    'user' => $feedback->user->name,
                    'created_at' => Carbon::parse($feedback->created_at)->diffForHumans(),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $userCounts->totalUsers,
                'moderatorCount' => $userCounts->moderatorCount,
                'userCount' => $userCounts->userCount,
                'totalReports' => $reportCounts->totalReports,
                'pendingReports' => $reportCounts->pendingReports,
                'processedReports' => $reportCounts->processedReports,
                'dismissedReports' => $reportCounts->dismissedReports,
            ],
            'reportStats' => $reportStats,
            'transactionStats' => $formattedData,
            'feedbacks' => $feedbacks,
        ]);
    })->name('admin');

    Route::get('/admin/user-management', [UsersController::class, 'index']);
    Route::patch('/admin/user-management/{user}/set-moderator', [UsersController::class, 'setModerator'])->name('admin.moderator.set');
    Route::patch('/admin/user-management/{user}/set-user', [UsersController::class, 'setUser'])->name('admin.user.set');

    Route::get('/admin/audit-logs', function () {
        $logs = ActionLog::with(['user', 'item'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'user' => $log->user->name ?? 'Unknown',
                'item' => $log->item->item_name ?? 'Unknown',
                'action' => $log->action,
                'created_at' => $log->created_at,
            ]);

        return Inertia::render('Admin/AuditLog/AuditLogManagement', [
            'logs' => $logs,
        ]);
    });
    Route::get('/admin/reports-management', [ReportManagementController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/{report}', [ReportManagementController::class, 'show'])->name('admin.reports.show');
    Route::get('/admin/suggestion-management', [SuggestionController::class, 'index']);
    Route::patch('/admin/dismiss-report/{id}', [ReportManagementController::class, 'dismissReport'])->name('admin.reports.dismiss');
    Route::patch('/admin/process-report/{report}', [ReportManagementController::class, 'processReport'])->name('admin.reports.process');

    Route::get('/admin/notification', fn () => Inertia::render('Admin/Notification/ManageNotification'));

    // User Verification Routes
    // Route::patch('/admin/verification/{verifDetail}/update-status', [VerificationController::class, 'updateStatus'])->name('admin.verification.update-status');

    // PROFILE MANAGEMENT =============================================
    Route::get('/admin/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::post('/admin/profile/update', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/admin/profile/picture', [AdminProfileController::class, 'updateProfilePicture'])->name('admin.profile.picture.update');

    // TRANSACTION MANAGEMENT =============================================
    // Transaction routes
    Route::get('/admin/transaction-history', function () {
        $transactions = TradeRequest::with(['user', 'item', 'item.user'])
            ->latest()
            ->get()
            ->map(fn ($trade) => [
                'id' => $trade->id,
                'trader' => $trade->item->user->name,
                'trading_item' => $trade->item->item_name,
                'recipient' => $trade->user->name,
                'offered_item' => $trade->name,
                'place' => $trade->place,
                'status' => $trade->status,
                'time' => $trade->getRawOriginal('time'),
            ]);

        $pending = TradeRequest::with(['user', 'item', 'item.user'])
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn ($trade) => [
                'id' => $trade->id,
                'trader' => $trade->item->user->name,
                'trading_item' => $trade->item->item_name,
                'recipient' => $trade->user->name,
                'offered_item' => $trade->name,
                'place' => $trade->place,
                'status' => $trade->status,
                'time' => $trade->getRawOriginal('time'),
            ]);

        $processing = TradeRequest::with(['user', 'item', 'item.user'])
            ->where('status', 'processing')
            ->latest()
            ->get()
            ->map(fn ($trade) => [
                'id' => $trade->id,
                'trader' => $trade->item->user->name,
                'trading_item' => $trade->item->item_name,
                'recipient' => $trade->user->name,
                'offered_item' => $trade->name,
                'place' => $trade->place,
                'status' => $trade->status,
                'time' => $trade->getRawOriginal('time'),
            ]);

        $success = TradeRequest::with(['user', 'item', 'item.user'])
            ->where('status', 'success')
            ->latest()
            ->get()
            ->map(fn ($trade) => [
                'id' => $trade->id,
                'trader' => $trade->item->user->name,
                'trading_item' => $trade->item->item_name,
                'recipient' => $trade->user->name,
                'offered_item' => $trade->name,
                'place' => $trade->place,
                'status' => $trade->status,
                'time' => $trade->getRawOriginal('time'),
            ]);

        $cancelled = TradeRequest::with(['user', 'item', 'item.user'])
            ->where('status', 'cancelled')
            ->latest()
            ->get()
            ->map(fn ($trade) => [
                'id' => $trade->id,
                'trader' => $trade->item->user->name,
                'trading_item' => $trade->item->item_name,
                'recipient' => $trade->user->name,
                'offered_item' => $trade->name,
                'place' => $trade->place,
                'status' => $trade->status,
                'time' => $trade->getRawOriginal('time'),
            ]);

        $rejected = TradeRequest::with(['user', 'item', 'item.user'])
            ->where('status', 'rejected')
            ->latest()
            ->get()
            ->map(fn ($trade) => [
                'id' => $trade->id,
                'trader' => $trade->item->user->name,
                'trading_item' => $trade->item->item_name,
                'recipient' => $trade->user->name,
                'offered_item' => $trade->name,
                'place' => $trade->place,
                'status' => $trade->status,
                'time' => $trade->getRawOriginal('time'),
            ]);

        return Inertia::render('Admin/TransactionHistory/TransactionManagement', [
            'transactions' => $transactions,
            'pending' => $pending,
            'processing' => $processing,
            'success' => $success,
            'cancelled' => $cancelled,
            'rejected' => $rejected,
        ]);
    });

    Route::get('/admin/monthly-transactions', [AdminTransactionController::class, 'getMonthlyTransactions']);
    Route::get('/admin/transactions/stats', [AdminTransactionController::class, 'invoke'])->name('admin.transactions.stats');
    Route::get('/admin/monthly-transactions-api', [AdminTransactionController::class, 'getMonthlyTransactionsApi']);

    // RESET PASSWORD =============================================
    Route::get('/admin/reset-password-page', [ResetPassController::class, 'index'])->name('admin.reset-password-page');
    Route::patch('/admin/reset-password/{req}', [ResetPassController::class, 'resetPass'])->name('admin.reset-password');

    // VERIFICATION =============================================
    // Route::get('/admin/user-verification', [VerificationController::class, 'index'])->name('admin.user-verification');
    // Route::get('/admin/user-verification/{id}', [VerificationController::class, 'show'])->name('admin.verification.show');
    // Route::post('/admin/verification/{verifDetail}/status', [VerificationController::class, 'updateStatus'])->name('admin.verification.update-status');
});
