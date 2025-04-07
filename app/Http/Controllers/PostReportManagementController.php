<?php

namespace App\Http\Controllers;

use App\Models\PostReport;
use Inertia\Inertia;

class PostReportManagementController extends Controller
{
    public function index()
    {
        $statuses = ['pending', 'processed', 'dismissed'];
        $reports = [];

        foreach ($statuses as $status) {
            $reports[$status] = PostReport::with(['item', 'images'])
                ->where('status', $status)
                ->latest()
                ->paginate(100);
        }

        return Inertia::render('Moderator/Reports/PostsReportsManagement', [
            'pendingReports' => $reports['pending'],
            'processedReports' => $reports['processed'],
            'dismissedReports' => $reports['dismissed'],
        ]);
    }

    public function unlist(PostReport $report)
    {
        // dd($report);
        $report->item()->update([
            'status' => 'unlisted']);

        $report->update([
            'status' => 'processed',
        ]);

        return back()->with('success', 'Item has been unlisted');
    }

    public function dismiss(PostReport $report)
    {
        $report->update([
            'status' => 'dismissed',
        ]);

        return back()->with('success', 'Report has been dismissed');
    }
}
