<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\AdvancedReportsQuery;
use SGCart\Reporting\Queries\OrderReportQuery;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
use SGCart\Reporting\Http\Requests\ExportReportRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RevenueReportController extends Controller
{
    public function __construct(
        protected AdvancedReportsQuery $advancedQuery,
        protected OrderReportQuery $orderQuery
    ) {}

    public function index(OrderReportRequest $request)
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $byProduct = $this->advancedQuery->revenueByProduct($from, $to, 10);
        $byCategory = $this->advancedQuery->revenueByCategory($from, $to);

        return view('reporting::admin.revenue.index', [
            'date_from' => $from,
            'date_to' => $to,
            'days_in_range' => max(1, $from->diffInDays($to) + 1),
            'by_product' => $byProduct,
            'by_category' => $byCategory,
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

        $fileName = "revenue-by-product-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return new StreamedResponse(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

            fputcsv($handle, ['Product ID', 'Product Name', 'Units Sold', 'Gross Revenue (₹)', 'Total Orders']);

            $data = $this->advancedQuery->revenueByProduct($from, $to, 500);
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->product_id,
                    $row->product_name,
                    $row->units_sold,
                    number_format($row->gross_revenue, 2, '.', ''),
                    $row->total_orders,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
