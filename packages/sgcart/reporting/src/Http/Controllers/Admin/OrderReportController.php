<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Actions\GetOrderReportDataAction;
use SGCart\Reporting\Actions\ExportOrderReportCsvAction;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
use SGCart\Reporting\Http\Requests\ExportReportRequest;
use SGCart\Reporting\Queries\OrderReportQuery;

class OrderReportController extends Controller
{
    public function __construct(
        protected GetOrderReportDataAction $action,
        protected OrderReportQuery $query
    ) {}

    /**
     * Display the Orders Report page.
     */
    public function index(OrderReportRequest $request)
    {
        // Resolve date range — default to current calendar month
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $perPage = config('reporting.per_page', 25);

        $data = $this->action->execute(
            $from,
            $to,
            $perPage,
            $request->input('search'),
            $request->input('sort_by'),
            $request->input('sort_dir') ?? $request->input('sort_order')
        );

        return view('reporting::admin.orders.index', $data);
    }

    /**
     * Export all order details inside the date range as CSV.
     */
    public function export(ExportReportRequest $request, ExportOrderReportCsvAction $exportAction)
    {
        // Resolve date range — default to current calendar month
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        return $exportAction->execute($from, $to);
    }
}
