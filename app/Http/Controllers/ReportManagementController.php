<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserReportResource;
use App\Jobs\UserReportMailJob;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportManagementController extends Controller
{
    public function index()
    {
        $reports = UserReport::query()
            ->select(['id', 'user_id', 'concern', 'description', 'status', 'created_at'])
            ->with([
                'user:id,name,level',
                'images' => fn ($query) => $query->select('id', 'user_report_id', 'image')->limit(1),
            ])
            ->whereIn('status', ['pending', 'dismissed', 'processed'])
            ->latest()
            ->get()
            ->groupBy('status');

        return Inertia::render('Admin/Reports/ReportsManagement', [
            'pending' => UserReportResource::collection($reports->get('pending', collect())->take(10)),
            'dismissed' => UserReportResource::collection($reports->get('dismissed', collect())->take(10)),
            'approved' => UserReportResource::collection($reports->get('processed', collect())->take(10)),
        ]);
    }

    public function show(UserReport $report)
    {
        $report->load(['user', 'images']);

        return Inertia::render('Admin/Reports/ReportDetails', [
            'report' => $report,
        ]);
    }

    public function dismissReport($id)
    {
        $report = UserReport::find($id);

        if ($report) {
            $report->update(['status' => 'dismissed']);

            return redirect()->route('admin.reports.index')->with('success', 'Report dismissed successfully.');
        }

        return redirect()->route('admin.reports.index')->with('error', 'Report not found.');
    }

    public function processReport(UserReport $report, Request $request)
    {

        if ($report) {
            $validate = $request->validate([
                'level' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:10',
                    function ($attribute, $value, $fail) use ($report) {
                        $currentLevel = (int) str_replace('level ', '', $report->user->level);
                        if ($value <= $currentLevel) {
                            $fail("The warning level must be greater than the user's current level ({$currentLevel}).");
                        }
                    },
                ],
                'notes' => 'nullable|string|max:300',
            ]);

            // Update report status
            $report->update([
                'status' => 'processed',
                'admin_notes' => $validate['notes'],
            ]);

            // Update user's warning level and apply punishment
            $user = $report->user;
            $warningLevel = (int) $validate['level'];
            $user->update([
                'level' => 'level '.$warningLevel,
            ]);

            // Apply punishment based on warning level
            switch ($warningLevel) {
                case 1:
                    // Level 1: Only email notification
                    break;
                case 2:
                    // Level 2: 3-day suspension
                    $user->update([
                        'is_suspended' => true,
                        'suspended_until' => now()->addDays(3),
                    ]);
                    break;
                case 3:
                    // Level 3: 7-day suspension
                    $user->update([
                        'is_suspended' => true,
                        'suspended_until' => now()->addDays(7),
                    ]);
                    break;
                case 4:
                    // Level 4: Permanent ban
                    // Clear any existing suspension when banning
                    $user->update([
                        'is_suspended' => false,
                        'suspended_until' => null,
                        'is_banned' => true,
                        'banned_at' => now(),
                    ]);
                    break;
            }

            // Send email notification
            dispatch(new UserReportMailJob($user->email, 'level '.$warningLevel));

            return redirect()->route('admin.reports.index')->with('success', 'Warning issued successfully.');
        }

        return redirect()->route('admin.reports.index')->with('error', 'Report not found.');
    }
}
