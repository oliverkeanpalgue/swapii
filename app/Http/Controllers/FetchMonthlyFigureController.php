<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FetchMonthlyFigureController extends Controller
{
    public function __invoke(Request $request)
    {
        $range = $request->input('range', 'monthly');
        $year = $request->input('year');
        $month = $request->input('month');

        switch ($range) {
            case 'yearly':
                return $this->getYearlyData(); // This will always return 5 years

            case 'daily':
                if ($year && $month) {
                    $date = Carbon::createFromDate($year, $month, 1);

                    return $this->getSpecificMonthData($date->format('Y-m'));
                }

                return $this->getDailyData();

            case 'custom':
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                if ($startDate && $endDate) {
                    return $this->getCustomRangeData($startDate, $endDate);
                }

                return response()->json(['error' => 'Invalid date range'], 400);

            case 'monthly':
                if ($year) {
                    return $this->getMonthlyData($year);
                }

                return $this->getMonthlyData(Carbon::now()->year);

            default:
                return $this->getMonthlyData(Carbon::now()->year);
        }
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
                YEAR(created_at) as year,
                COUNT(CASE WHEN approval = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN approval = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN approval = 'rejected' THEN 1 END) as rejected
            ")
            ->whereYear('created_at', '>=', $startYear)
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('year');

        $yearlyData = $query->get();

        // Generate all years in range
        $allYears = collect();
        for ($year = $startYear; $year <= $endYear; $year++) {
            $yearData = $yearlyData->firstWhere('year', $year);

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
        $month = request()->input('month', Carbon::now()->month);
        $year = request()->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $data = DB::table('items')
            ->selectRaw('
                DAY(created_at) as day,
                SUM(CASE WHEN approval = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval = "rejected" THEN 1 ELSE 0 END) as rejected
            ')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy(DB::raw('DAY(created_at)'))
            ->orderBy('day')
            ->get();

        $allDays = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayData = $data->firstWhere('day', $day);
            $allDays[] = [
                'day' => $day,
                'pending' => $dayData ? (int) $dayData->pending : 0,
                'approved' => $dayData ? (int) $dayData->approved : 0,
                'rejected' => $dayData ? (int) $dayData->rejected : 0,
            ];
        }

        $totals = [
            'pending' => array_sum(array_column($allDays, 'pending')),
            'approved' => array_sum(array_column($allDays, 'approved')),
            'rejected' => array_sum(array_column($allDays, 'rejected')),
        ];

        return response()->json([
            'range' => 'daily',
            'data' => $allDays,
            'month' => (int) $month,
            'year' => (int) $year,
            'monthName' => $startDate->format('F Y'),
            'daysInMonth' => $daysInMonth,
            'totals' => $totals,
        ]);
    }

    private function getMonthlyData($year = null)
    {
        if (! $year) {
            $year = Carbon::now()->year;
        }

        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();

        $query = DB::table('items')
            ->selectRaw("
                MONTH(created_at) as month,
                COUNT(CASE WHEN approval = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN approval = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN approval = 'rejected' THEN 1 END) as rejected
            ")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month');

        $monthlyData = $query->get();

        // Create an array for all 12 months with 0 counts
        $formattedData = collect(range(1, 12))->map(function ($month) use ($monthlyData) {
            $monthData = $monthlyData->firstWhere('month', $month);

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
            'year' => (int) $year,
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
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

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

        $rangeData = $query->get();

        // Generate data for all days in the range
        $allDays = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayData = $rangeData->firstWhere('date', $dateStr);

            $allDays->push([
                'date' => $dateStr,
                'pending' => $dayData ? (int) $dayData->pending : 0,
                'approved' => $dayData ? (int) $dayData->approved : 0,
                'rejected' => $dayData ? (int) $dayData->rejected : 0,
            ]);

            $currentDate->addDay();
        }

        // Calculate totals
        $totals = [
            'pending' => $allDays->sum('pending'),
            'approved' => $allDays->sum('approved'),
            'rejected' => $allDays->sum('rejected'),
            'total' => $allDays->sum('pending') + $allDays->sum('approved') + $allDays->sum('rejected'),
        ];

        return response()->json([
            'range' => 'custom',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'data' => $allDays,
            'totals' => $totals,
        ]);
    }
}
