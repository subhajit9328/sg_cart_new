<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Actions\GetCustomerReportAction;
use SGCart\Reporting\Actions\ExportCustomerReportCsvAction;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
use SGCart\Reporting\Http\Requests\ExportReportRequest;

class CustomerReportController extends Controller
{
    public function __construct(
        protected GetCustomerReportAction $action,
        protected ExportCustomerReportCsvAction $exportAction
    ) {}

    /**
     * Display Customers Report dashboard.
     */
    public function index(OrderReportRequest $request)
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $perPage = config('reporting.per_page', 25);

        $data = $this->action->execute($from, $to, $perPage);

        return view('reporting::admin.customers.index', $data);
    }

    /**
     * Export Customer Report CSV.
     */
    public function export(ExportReportRequest $request)
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        return $this->exportAction->execute($from, $to);
    }
}
