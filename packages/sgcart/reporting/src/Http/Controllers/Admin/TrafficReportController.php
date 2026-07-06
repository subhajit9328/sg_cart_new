<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\AdvancedReportsQuery;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
use SGCart\Reporting\Http\Requests\ExportReportRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrafficReportController extends Controller
{
    public function __construct(
        protected AdvancedReportsQuery $advancedQuery
    ) {}

    public function index(OrderReportRequest $request)
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $traffic = $this->advancedQuery->trafficAnalysisMetrics($from, $to);

        // Paginated list of page views
        $query = \Illuminate\Support\Facades\DB::table('traffic_logs')
            ->leftJoin('customers', 'traffic_logs.customer_id', '=', 'customers.id')
            ->whereBetween('traffic_logs.created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ])
            ->select('traffic_logs.id', 'traffic_logs.session_id', 'customers.name as customer_name', 'traffic_logs.path', 'traffic_logs.referrer', 'traffic_logs.ip_address', 'traffic_logs.created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('traffic_logs.path', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%")
                  ->orWhere('traffic_logs.ip_address', 'like', "%{$search}%");
            });
        }

        $sortDir = strtolower($request->input('sort_dir') ?? $request->input('sort_order') ?? '') === 'asc' ? 'asc' : 'desc';

        if ($request->input('sort_by') === 'created_at') {
            $query->orderBy('traffic_logs.created_at', $sortDir);
        } else {
            $query->orderByDesc('traffic_logs.created_at');
        }

        $pageViews = $query->paginate(config('reporting.per_page', 25))
            ->withQueryString();

        return view('reporting::admin.traffic.index', [
            'date_from' => $from,
            'date_to' => $to,
            'days_in_range' => max(1, $from->diffInDays($to) + 1),
            'traffic' => $traffic,
            'page_views' => $pageViews,
        ]);
    }

    public function export(ExportReportRequest $request): StreamedResponse
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $fileName = "traffic-log-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return new StreamedResponse(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

            fputcsv($handle, ['View ID', 'Session ID', 'Customer Name', 'Page Path', 'Referrer Host', 'IP Address', 'Viewed At']);

            \Illuminate\Support\Facades\DB::table('traffic_logs')
                ->leftJoin('customers', 'traffic_logs.customer_id', '=', 'customers.id')
                ->whereBetween('traffic_logs.created_at', [
                    $from->copy()->startOfDay(),
                    $to->copy()->endOfDay(),
                ])
                ->select('traffic_logs.id', 'traffic_logs.session_id', 'customers.name as customer_name', 'traffic_logs.path', 'traffic_logs.referrer', 'traffic_logs.ip_address', 'traffic_logs.created_at')
                ->orderByDesc('traffic_logs.created_at')
                ->lazy()
                ->each(function ($row) use ($handle) {
                    fputcsv($handle, [
                        $row->id,
                        $row->session_id,
                        $row->customer_name ?? 'Guest Visitor',
                        $row->path,
                        $row->referrer ?? 'Direct / Search',
                        $row->ip_address ?? '—',
                        $row->created_at,
                    ]);
                });

            fclose($handle);
        }, 200, $headers);
    }
}
