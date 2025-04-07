<?php

namespace App\Http\Controllers;

use App\Models\TradeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTransactionController extends Controller
{
    public function getMonthlyTransactions(Request $request)
    {
        $range = $request->input('range', 'monthly');

        if ($range === 'yearly') {
            // Get data for the last 5 years
            $endYear = now()->year;
            $startYear = $endYear - 4; // 5 years ago

            $transactions = TradeRequest::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_trades'),
                DB::raw('SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_trades'),
                DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_trades'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_trades'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_trades')
            )
                ->whereYear('created_at', '>=', $startYear)
                ->whereYear('created_at', '<=', $endYear)
                ->groupBy(DB::raw('YEAR(created_at)'))
                ->orderBy(DB::raw('YEAR(created_at)'))
                ->get()
                ->map(function ($item) {
                    $item->period = $item->year;

                    return $item;
                });

            // Fill in missing years with zero values
            $allYears = collect();
            for ($year = $startYear; $year <= $endYear; $year++) {
                $yearData = $transactions->firstWhere('year', $year);
                if (! $yearData) {
                    $allYears->push((object) [
                        'year' => $year,
                        'period' => $year,
                        'total_trades' => 0,
                        'pending_trades' => 0,
                        'processing_trades' => 0,
                        'successful_trades' => 0,
                        'cancelled_trades' => 0,
                        'rejected_trades' => 0,
                    ]);
                } else {
                    $allYears->push($yearData);
                }
            }

            return response()->json($allYears);
        } elseif ($range === 'custom') {
            try {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format provided'], 400);
            }

            if (! $startDate || ! $endDate) {
                return response()->json(['error' => 'Start date and end date are required for custom range'], 400);
            }

            if ($endDate->lt($startDate)) {
                return response()->json(['error' => 'End date must be after start date'], 400);
            }

            // Get transactions for the date range
            $transactions = TradeRequest::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_trades'),
                DB::raw('SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_trades'),
                DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_trades'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_trades'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_trades')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'))
                ->get()
                ->map(function ($item) {
                    $date = Carbon::parse($item->date);
                    $item->period = $date->format('M d, Y');

                    return $item;
                });

            // Fill in missing dates
            $allDates = collect();
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dateData = $transactions->firstWhere('date', $dateStr);

                if (! $dateData) {
                    $allDates->push((object) [
                        'date' => $dateStr,
                        'period' => $currentDate->format('M d, Y'),
                        'total_trades' => 0,
                        'pending_trades' => 0,
                        'processing_trades' => 0,
                        'successful_trades' => 0,
                        'cancelled_trades' => 0,
                        'rejected_trades' => 0,
                    ]);
                } else {
                    $allDates->push($dateData);
                }

                $currentDate->addDay();
            }

            return response()->json($allDates);
        } elseif ($range === 'daily') {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));

            $transactions = TradeRequest::select(
                DB::raw('DAY(created_at) as day'),
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_trades'),
                DB::raw('SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_trades'),
                DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_trades'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_trades'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_trades')
            )
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            // Get the number of days in the selected month
            $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

            // Fill in missing days with zero values
            $filledData = collect(range(1, $daysInMonth))->map(function ($day) use ($transactions, $year, $month) {
                $dayData = $transactions->firstWhere('day', $day);
                if (! $dayData) {
                    $date = Carbon::createFromDate($year, $month, $day);

                    return (object) [
                        'day' => $day,
                        'period' => $date->format('M d, Y'),
                        'total_trades' => 0,
                        'pending_trades' => 0,
                        'processing_trades' => 0,
                        'successful_trades' => 0,
                        'cancelled_trades' => 0,
                        'rejected_trades' => 0,
                    ];
                }
                $date = Carbon::createFromDate($year, $month, $day);
                $dayData->period = $date->format('M d, Y');

                return $dayData;
            });

            return response()->json($filledData);
        } else {
            $year = $request->input('year', date('Y'));

            $transactions = TradeRequest::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_trades'),
                DB::raw('SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_trades'),
                DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_trades'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_trades'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_trades')
            )
                ->whereYear('created_at', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Fill in missing months with zero values
            $filledData = collect(range(1, 12))->map(function ($month) use ($transactions, $year) {
                $monthData = $transactions->firstWhere('month', $month);
                if (! $monthData) {
                    $date = Carbon::createFromDate($year, $month, 1);

                    return (object) [
                        'month' => $month,
                        'period' => $date->format('F'),
                        'total_trades' => 0,
                        'pending_trades' => 0,
                        'processing_trades' => 0,
                        'successful_trades' => 0,
                        'cancelled_trades' => 0,
                        'rejected_trades' => 0,
                    ];
                }
                $date = Carbon::createFromDate($year, $month, 1);
                $monthData->period = $date->format('F');

                return $monthData;
            });

            return response()->json($filledData);
        }
    }
}
