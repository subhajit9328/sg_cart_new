<?php

namespace SGCart\DashboardAnalytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use SGCart\DashboardAnalytics\Models\SearchLog;

class AnalyticsController extends Controller
{
    /**
     * Display the merged Admin Dashboard & Analytics.
     */
    public function index(Request $request)
    {
        // 1. Fetch standard admin dashboard statistics (always loaded for all users)
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        
        $recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        // 2. Check if user is authorized to view analytics
        $showAnalytics = auth()->user()->can('view analytics');

        // Initialize analytics variables
        $preset = $request->input('date_preset', '7days');
        $start = null;
        $end = null;
        $totalOrdersCount = 0;
        $totalOrdersRevenue = 0.0;
        $rejectedOrdersCount = 0;
        $totalSearchesCount = 0;
        $topCoupon = null;
        $timeSlots = [];
        $peakSlotName = 'None';
        $topProducts = collect();
        $topSearches = collect();
        $trendList = [];

        if ($showAnalytics) {
            // 3. Process Date Ranges
            $now = Carbon::now()->endOfDay();
            $todayStart = Carbon::today();

            if ($preset === 'custom') {
                $startDateStr = $request->input('start_date');
                $endDateStr = $request->input('end_date');
                
                if (!$startDateStr || !$endDateStr) {
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'Custom date range requires both start and end dates.');
                }
                
                try {
                    $start = Carbon::parse($startDateStr)->startOfDay();
                    $end = Carbon::parse($endDateStr)->endOfDay();
                } catch (\Exception $e) {
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'Invalid date format.');
                }
                
                // Compare start of day to avoid timezone/time future date bugs
                if ($start->startOfDay()->gt($todayStart) || $end->startOfDay()->gt($todayStart)) {
                    return redirect()->route('admin.dashboard', ['date_preset' => '7days'])
                        ->with('error', 'Future dates cannot be selected.');
                }
                
                if ($start->gt($end)) {
                    return redirect()->route('admin.dashboard', ['date_preset' => '7days'])
                        ->with('error', 'Start date cannot be after end date.');
                }
                
                if ($start->diffInDays($end) > 180) {
                    return redirect()->route('admin.dashboard', ['date_preset' => '7days'])
                        ->with('error', 'The custom date range cannot exceed 6 months (180 days).');
                }
            } else {
                $end = $now;
                if ($preset === '15days') {
                    $start = Carbon::now()->subDays(14)->startOfDay();
                } elseif ($preset === '30days') {
                    $start = Carbon::now()->subDays(29)->startOfDay();
                } elseif ($preset === '6months') {
                    $start = Carbon::now()->subMonths(6)->startOfDay();
                } else {
                    // Default 1 week
                    $start = Carbon::now()->subDays(6)->startOfDay();
                    $preset = '7days';
                }
            }

            // 4. Fetch KPIs
            $totalOrdersCount = Order::whereBetween('created_at', [$start, $end])->count();
            $totalOrdersRevenue = (float) Order::whereBetween('created_at', [$start, $end])
                ->where('status', '!=', \App\Enums\OrderStatus::CANCELLED)
                ->sum('total');

            $rejectedOrdersCount = Order::whereBetween('created_at', [$start, $end])
                ->where('status', \App\Enums\OrderStatus::CANCELLED)
                ->count();

            $totalSearchesCount = SearchLog::whereBetween('created_at', [$start, $end])->count();

            // 5. Max Used Coupon
            $topCoupon = Order::selectRaw('coupon_code, COUNT(*) as count, SUM(discount) as total_discount')
                ->whereNotNull('coupon_code')
                ->where('coupon_code', '!=', '')
                ->where('status', '!=', \App\Enums\OrderStatus::CANCELLED)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('coupon_code')
                ->orderBy('count', 'desc')
                ->first();

            // 6. Maximum Sales Time Slot (Peak Sales hour periods)
            $ordersByHour = Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(total) as revenue')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('hour')
                ->get();

            $timeSlots = [
                'Morning (06:00 - 12:00)' => ['count' => 0, 'revenue' => 0.0],
                'Afternoon (12:00 - 18:00)' => ['count' => 0, 'revenue' => 0.0],
                'Evening (18:00 - 00:00)' => ['count' => 0, 'revenue' => 0.0],
                'Night (00:00 - 06:00)' => ['count' => 0, 'revenue' => 0.0],
            ];

            foreach ($ordersByHour as $item) {
                $hour = (int) $item->hour;
                if ($hour >= 6 && $hour < 12) {
                    $timeSlots['Morning (06:00 - 12:00)']['count'] += $item->count;
                    $timeSlots['Morning (06:00 - 12:00)']['revenue'] += (float) $item->revenue;
                } elseif ($hour >= 12 && $hour < 18) {
                    $timeSlots['Afternoon (12:00 - 18:00)']['count'] += $item->count;
                    $timeSlots['Afternoon (12:00 - 18:00)']['revenue'] += (float) $item->revenue;
                } elseif ($hour >= 18 && $hour < 24) {
                    $timeSlots['Evening (18:00 - 00:00)']['count'] += $item->count;
                    $timeSlots['Evening (18:00 - 00:00)']['revenue'] += (float) $item->revenue;
                } else {
                    $timeSlots['Night (00:00 - 06:00)']['count'] += $item->count;
                    $timeSlots['Night (00:00 - 06:00)']['revenue'] += (float) $item->revenue;
                }
            }

            $peakSlotName = 'None';
            $maxRevenue = -1.0;
            foreach ($timeSlots as $name => $data) {
                if ($data['revenue'] > $maxRevenue && $data['count'] > 0) {
                    $maxRevenue = $data['revenue'];
                    $peakSlotName = $name;
                }
            }

            // 7. Top 10 High Demand Products customer buy
            $topProducts = OrderItem::selectRaw('product_id, product_name, SUM(quantity) as qty, SUM(price * quantity) as revenue')
                ->whereHas('order', function ($q) use ($start, $end) {
                    $q->where('status', '!=', \App\Enums\OrderStatus::CANCELLED)
                      ->whereBetween('created_at', [$start, $end]);
                })
                ->groupBy('product_id', 'product_name')
                ->orderBy('qty', 'desc')
                ->take(10)
                ->get();

            // 8. Top 10 search queries
            $topSearches = SearchLog::selectRaw('term, COUNT(*) as count')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('term')
                ->orderBy('count', 'desc')
                ->take(10)
                ->get();

            // 9. Orders Trend
            $ordersTrend = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $period = new \DatePeriod(
                $start->toDateTime(),
                new \DateInterval('P1D'),
                $end->copy()->addDay()->toDateTime()
            );

            $trendData = [];
            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $trendData[$formattedDate] = [
                    'date' => $date->format('M d'),
                    'count' => 0,
                    'revenue' => 0.0
                ];
            }

            foreach ($ordersTrend as $trend) {
                $dbDate = $trend->date;
                if (isset($trendData[$dbDate])) {
                    $trendData[$dbDate]['count'] = $trend->count;
                    $trendData[$dbDate]['revenue'] = round((float) $trend->revenue, 2);
                }
            }
            $trendList = array_values($trendData);
        }

        return view('dashboard-analytics::index', compact(
            'totalUsers',
            'totalRoles',
            'totalPermissions',
            'recentUsers',
            'showAnalytics',
            'preset',
            'start',
            'end',
            'totalOrdersCount',
            'totalOrdersRevenue',
            'rejectedOrdersCount',
            'totalSearchesCount',
            'topCoupon',
            'timeSlots',
            'peakSlotName',
            'topProducts',
            'topSearches',
            'trendList'
        ));
    }
}
