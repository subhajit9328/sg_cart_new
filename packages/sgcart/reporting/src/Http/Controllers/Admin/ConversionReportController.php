<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\AdvancedReportsQuery;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
use SGCart\Reporting\Http\Requests\ExportReportRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversionReportController extends Controller
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

        $funnel = $this->advancedQuery->conversionFunnelMetrics($from, $to);

        // Get detailed abandoned carts
        $abandonedCarts = $this->advancedQuery->abandonedCartsQuery($from, $to)
            ->paginate(config('reporting.per_page', 25))
            ->withQueryString();

        return view('reporting::admin.conversion.index', [
            'date_from' => $from,
            'date_to' => $to,
            'days_in_range' => max(1, $from->diffInDays($to) + 1),
            'funnel' => $funnel,
            'abandoned_carts' => $abandonedCarts,
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

        $fileName = "abandoned-carts-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return new StreamedResponse(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

            fputcsv($handle, ['Cart ID', 'Session ID', 'Customer Name', 'Items Count', 'Created At']);

            $this->advancedQuery->abandonedCartsQuery($from, $to)
                ->lazy()
                ->each(function ($row) use ($handle) {
                    fputcsv($handle, [
                        $row->id,
                        $row->session_id,
                        $row->customer_name ?? 'Guest Visitor',
                        $row->items_count ?? 0,
                        $row->created_at,
                    ]);
                });

            fclose($handle);
        }, 200, $headers);
    }
}
