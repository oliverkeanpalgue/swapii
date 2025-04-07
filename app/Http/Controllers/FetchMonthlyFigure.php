<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fetchMontlyFigure extends Controller
{
    public function __invoke(Request $request)
    {
        $range = $request->input('range', 'monthly');
        $month = $request->input('month');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        return match ($range) {
            'daily' => $this->getDailyData(),
            'monthly' => $this->getMonthlyData(),
            'yearly' => $this->getYearlyData(),
            'specific-month' => $this->getSpecificMonthData($month),
            'weekly' => $this->getWeeklyData(),
            'custom' => $this->getCustomRangeData($startDate, $endDate),
            default => response()->json(['error' => 'Invalid range'], 400),
        };
    }

    private function getSpecificMonthData($selectedMonth)
    {
        if (! $selectedMonth) {
            $selectedMonth = Carbon::now()->format('Y-m');
        }

        $date = Carbon::createFromFormat('Y-m', $selectedMonth);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        $daysInMonth = $date->daysInMonth;

        $query = DB::table('items')
            ->selectRaw("
                DATE(created_at) as date,
                COUNT(CASE WHEN approval = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN approval = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN approval = 'rejected' THEN 1 END) as rejected
            ")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date');

        $monthData = $query->get();

        // Generate data for all days in the month
        $allDays = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayData = $monthData->firstWhere('date', $dateStr);

            $allDays->push([
                'date' => $dateStr,
                'day' => $currentDate->format('d'),
                'pending' => $dayData ? (int) $dayData->pending : 0,
                'approved' => $dayData ? (int) $dayData->approved : 0,
                'rejected' => $dayData ? (int) $dayData->rejected : 0,
            ]);

            $currentDate->addDay();
        }

        // Calculate totals for the month
        $totals = [
            'pending' => $allDays->sum('pending'),
            'approved' => $allDays->sum('approved'),
            'rejected' => $allDays->sum('rejected'),
            'total' => $allDays->sum('pending') + $allDays->sum('approved') + $allDays->sum('rejected'),
        ];

        return response()->json([
            'range' => 'specific-month',
            'month' => $selectedMonth,
            'monthName' => $date->format('F Y'),
            'data' => $allDays,
            'totals' => $totals,
        ]);
    }

    private function getYearlyData()
    {
        $startYear = Carbon::now()->subYears(4)->year; // Last 5 years
        $endYear = Carbon::now()->year;

        $query = DB::table('items')
            ->selectRaw("
                strftime('%Y', created_at) as year,
                COUNT(CASE WHEN approval = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN approval = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN approval = 'rejected' THEN 1 END) as rejected
            ")
            ->whereYear('created_at', '>=', $startYear)
            ->groupBy('year')
            ->orderBy('year');

        $yearlyData = $query->get();

        // Generate all years in range
        $allYears = collect();
        for ($year = $startYear; $year <= $endYear; $year++) {
            $yearData = $yearlyData->firstWhere('year', (string) $year);

            $allYears->push([
                'year' => $year,
                'pending' => $yearData ? (int) $yearData->pending : 0,
                'approved' => $yearData ? (int) $yearData->approved : 0,
                'rejected' => $yearData ? (int) $yearData->rejected : 0,
            ]);
        }

        return response()->json([
            'range' => 'yearly',
            'data' => $allYears,
        ]);
    }

    private function getDailyData()
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $query = DB::table('items')
            ->selectRaw("
                DATE(created_at) as date,
                COUNT(CASE WHEN approval = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN approval = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN approval = 'rejected' THEN 1 END) as rejected
            ")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date');

        $dailyData = $query->get();

        // Generate all dates in range
        $allDates = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayData = $dailyData->firstWhere('date', $dateStr);

            $allDates->push([
                'date' => $dateStr,
                'pending' => $dayData ? (int) $dayData->pending : 0,
                'approved' => $dayData ? (int) $dayData->approved : 0,
                'rejected' => $dayData ? (int) $dayData->rejected : 0,
            ]);

            $currentDate->addDay();
        }

        return response()->json([
            'range' => 'daily',
            'data' => $allDates,
        ]);
    }

    private function getMonthlyData()
    {
        $query = DB::table('items')
            ->selectRaw("
                strftime('%m', created_at) as month,
                COUNT(CASE WHEN approval = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN approval = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN approval = 'rejected' THEN 1 END) as rejected
            ")
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month');

        $monthlyData = $query->get();

        // Create an array for all 12 months with 0 counts
        $formattedData = collect(range(1, 12))->map(function ($month) use ($monthlyData) {
            $monthStr = sprintf('%02d', $month);
            $monthData = $monthlyData->firstWhere('month', $monthStr);

            return [
                'month' => $month,
                'pending' => $monthData ? (int) $monthData->pending : 0,
                'approved' => $monthData ? (int) $monthData->approved : 0,
                'rejected' => $monthData ? (int) $monthData->rejected : 0,
            ];
        });

        return response()->json([
            'range' => 'monthly',
            'data' => $formattedData,
        ]);
    }

    private function getWeeklyData()
    {
        $endDate = now();
        $startDate = $endDate->copy()->subDays(6); // Last 7 days including today

        $data = DB::table('items')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(CASE WHEN approval = "pending" THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN approval = "approved" THEN 1 ELSE 0 END) as approved')
            ->selectRaw('SUM(CASE WHEN approval = "rejected" THEN 1 ELSE 0 END) as rejected')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing days with zeros
        $result = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayData = $data->firstWhere('date', $dateStr);

            $result[] = [
                'date' => $dateStr,
                'pending' => $dayData->pending ?? 0,
                'approved' => $dayData->approved ?? 0,
                'rejected' => $dayData->rejected ?? 0,
                'dayName' => $currentDate->format('D'), // Add day name (Mon, Tue, etc.)
            ];

            $currentDate->addDay();
        }

        return [
            'range' => 'weekly',
            'data' => $result,
        ];
    }

    private function getCustomRangeData($startDate, $endDate)
    {
        if (! $startDate || ! $endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        if ($endDate->diffInDays($startDate) > 90) {
            return response()->json(['error' => 'Date range cannot exceed 90 days'], 400);
        }

        $data = DB::table('items')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(CASE WHEN approval = "pending" THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN approval = "approved" THEN 1 ELSE 0 END) as approved')
            ->selectRaw('SUM(CASE WHEN approval = "rejected" THEN 1 ELSE 0 END) as rejected')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing days with zeros
        $result = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayData = $data->firstWhere('date', $dateStr);

            $result[] = [
                'date' => $dateStr,
                'pending' => $dayData->pending ?? 0,
                'approved' => $dayData->approved ?? 0,
                'rejected' => $dayData->rejected ?? 0,
                'dayName' => $currentDate->format('M j'), // Format as "Jan 1"
            ];

            $currentDate->addDay();
        }

        return [
            'range' => 'custom',
            'data' => $result,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }
}
