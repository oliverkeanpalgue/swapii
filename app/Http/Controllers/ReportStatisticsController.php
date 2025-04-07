<?php

namespace App\Http\Controllers;

use App\Models\UserReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportStatisticsController extends Controller
{
    public function getReportStats(Request $request)
    {
        $range = $request->input('range', 'monthly');
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $response = [];

        // Get chart data based on range
        switch ($range) {
            case 'yearly':
                $response['chartData'] = $this->getYearlyStats();
                break;
            case 'monthly':
                $response['chartData'] = $this->getMonthlyStats($year);
                break;
            case 'daily':
                $response['chartData'] = $this->getDailyStats($year, $month);
                break;
            case 'custom':
                $response['chartData'] = $this->getCustomRangeStats($startDate, $endDate);
                break;
            default:
                return response()->json(['error' => 'Invalid range type'], 400);
        }

        // Get top concerns for the selected date range
        $concernStartDate = null;
        $concernEndDate = null;

        switch ($range) {
            case 'yearly':
                $concernStartDate = Carbon::now()->subYears(4)->startOfYear();
                $concernEndDate = Carbon::now()->endOfYear();
                break;
            case 'monthly':
                $concernStartDate = Carbon::createFromDate($year, 1, 1);
                $concernEndDate = Carbon::createFromDate($year, 12, 31);
                break;
            case 'daily':
                $concernStartDate = Carbon::createFromDate($year, $month, 1);
                $concernEndDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                break;
            case 'custom':
                $concernStartDate = Carbon::parse($startDate);
                $concernEndDate = Carbon::parse($endDate);
                break;
        }

        $response['topConcerns'] = $this->getTopConcerns($concernStartDate, $concernEndDate);

        return response()->json($response);
    }

    private function getYearlyStats()
    {
        // Get data for the last 5 years
        $endYear = now()->year;
        $startYear = $endYear - 4; // 5 years ago

        $reports = UserReport::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total_reports'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_reports'),
            DB::raw('SUM(CASE WHEN status = "processed" THEN 1 ELSE 0 END) as processed_reports'),
            DB::raw('SUM(CASE WHEN status = "dismissed" THEN 1 ELSE 0 END) as dismissed_reports')
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
            $yearData = $reports->firstWhere('year', $year);
            if (! $yearData) {
                $allYears->push((object) [
                    'year' => $year,
                    'period' => $year,
                    'total_reports' => 0,
                    'pending_reports' => 0,
                    'processed_reports' => 0,
                    'dismissed_reports' => 0,
                ]);
            } else {
                $allYears->push($yearData);
            }
        }

        return [
            'labels' => $allYears->pluck('period')->toArray(),
            'datasets' => [
                [
                    'label' => 'Pending',
                    'data' => $allYears->pluck('pending_reports')->toArray(),
                    'backgroundColor' => '#FFA726',
                    'borderColor' => '#FB8C00',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Processed',
                    'data' => $allYears->pluck('processed_reports')->toArray(),
                    'backgroundColor' => '#66BB6A',
                    'borderColor' => '#43A047',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Dismissed',
                    'data' => $allYears->pluck('dismissed_reports')->toArray(),
                    'backgroundColor' => '#EF5350',
                    'borderColor' => '#E53935',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
        ];
    }

    private function getMonthlyStats($year)
    {
        $reports = UserReport::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total_reports'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_reports'),
            DB::raw('SUM(CASE WHEN status = "processed" THEN 1 ELSE 0 END) as processed_reports'),
            DB::raw('SUM(CASE WHEN status = "dismissed" THEN 1 ELSE 0 END) as dismissed_reports')
        )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fill in missing months with zero values
        $filledData = collect(range(1, 12))->map(function ($month) use ($reports, $year) {
            $monthData = $reports->firstWhere('month', $month);
            if (! $monthData) {
                $date = Carbon::createFromDate($year, $month, 1);

                return (object) [
                    'month' => $month,
                    'period' => $date->format('F'),
                    'total_reports' => 0,
                    'pending_reports' => 0,
                    'processed_reports' => 0,
                    'dismissed_reports' => 0,
                ];
            }
            $date = Carbon::createFromDate($year, $month, 1);
            $monthData->period = $date->format('F');

            return $monthData;
        });

        return [
            'labels' => $filledData->pluck('period')->toArray(),
            'datasets' => [
                [
                    'label' => 'Pending',
                    'data' => $filledData->pluck('pending_reports')->toArray(),
                    'backgroundColor' => '#FFA726',
                    'borderColor' => '#FB8C00',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Processed',
                    'data' => $filledData->pluck('processed_reports')->toArray(),
                    'backgroundColor' => '#66BB6A',
                    'borderColor' => '#43A047',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Dismissed',
                    'data' => $filledData->pluck('dismissed_reports')->toArray(),
                    'backgroundColor' => '#EF5350',
                    'borderColor' => '#E53935',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
        ];
    }

    private function getDailyStats($year, $month)
    {
        $reports = UserReport::select(
            DB::raw('DAY(created_at) as day'),
            DB::raw('COUNT(*) as total_reports'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_reports'),
            DB::raw('SUM(CASE WHEN status = "processed" THEN 1 ELSE 0 END) as processed_reports'),
            DB::raw('SUM(CASE WHEN status = "dismissed" THEN 1 ELSE 0 END) as dismissed_reports')
        )
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Get the number of days in the selected month
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // Fill in missing days with zero values
        $filledData = collect(range(1, $daysInMonth))->map(function ($day) use ($reports, $year, $month) {
            $dayData = $reports->firstWhere('day', $day);
            if (! $dayData) {
                $date = Carbon::createFromDate($year, $month, $day);

                return (object) [
                    'day' => $day,
                    'period' => $date->format('M d, Y'),
                    'total_reports' => 0,
                    'pending_reports' => 0,
                    'processed_reports' => 0,
                    'dismissed_reports' => 0,
                ];
            }
            $date = Carbon::createFromDate($year, $month, $day);
            $dayData->period = $date->format('M d, Y');

            return $dayData;
        });

        return [
            'labels' => $filledData->pluck('period')->toArray(),
            'datasets' => [
                [
                    'label' => 'Pending',
                    'data' => $filledData->pluck('pending_reports')->toArray(),
                    'backgroundColor' => '#FFA726',
                    'borderColor' => '#FB8C00',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Processed',
                    'data' => $filledData->pluck('processed_reports')->toArray(),
                    'backgroundColor' => '#66BB6A',
                    'borderColor' => '#43A047',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Dismissed',
                    'data' => $filledData->pluck('dismissed_reports')->toArray(),
                    'backgroundColor' => '#EF5350',
                    'borderColor' => '#E53935',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
        ];
    }

    private function getCustomRangeStats($startDate, $endDate)
    {
        try {
            if (! $startDate || ! $endDate) {
                return response()->json(['error' => 'Start date and end date are required for custom range'], 400);
            }

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            if ($endDate->lt($startDate)) {
                return response()->json(['error' => 'End date must be after start date'], 400);
            }

            $reports = UserReport::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_reports'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_reports'),
                DB::raw('SUM(CASE WHEN status = "processed" THEN 1 ELSE 0 END) as processed_reports'),
                DB::raw('SUM(CASE WHEN status = "dismissed" THEN 1 ELSE 0 END) as dismissed_reports')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Fill in missing dates with zero values
            $allDates = collect();
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dateData = $reports->firstWhere('date', $dateStr);

                if (! $dateData) {
                    $allDates->push((object) [
                        'date' => $dateStr,
                        'period' => $currentDate->format('M d, Y'),
                        'total_reports' => 0,
                        'pending_reports' => 0,
                        'processed_reports' => 0,
                        'dismissed_reports' => 0,
                    ]);
                } else {
                    $dateData->period = Carbon::parse($dateData->date)->format('M d, Y');
                    $allDates->push($dateData);
                }

                $currentDate->addDay();
            }

            return [
                'labels' => $allDates->pluck('period')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Pending',
                        'data' => $allDates->pluck('pending_reports')->toArray(),
                        'backgroundColor' => '#FFA726',
                        'borderColor' => '#FB8C00',
                        'borderWidth' => 2,
                        'borderRadius' => 6,
                    ],
                    [
                        'label' => 'Processed',
                        'data' => $allDates->pluck('processed_reports')->toArray(),
                        'backgroundColor' => '#66BB6A',
                        'borderColor' => '#43A047',
                        'borderWidth' => 2,
                        'borderRadius' => 6,
                    ],
                    [
                        'label' => 'Dismissed',
                        'data' => $allDates->pluck('dismissed_reports')->toArray(),
                        'backgroundColor' => '#EF5350',
                        'borderColor' => '#E53935',
                        'borderWidth' => 2,
                        'borderRadius' => 6,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format provided'], 400);
        }
    }

    private function getTopConcerns($startDate = null, $endDate = null)
    {
        $query = UserReport::select('concern', DB::raw('COUNT(*) as count'))
            ->groupBy('concern')
            ->orderBy('count', 'desc')
            ->limit(3);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        return $query->get();
    }
}
