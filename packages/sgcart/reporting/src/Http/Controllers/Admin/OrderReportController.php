<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Actions\GetOrderReportDataAction;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
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

        $data = $this->action->execute($from, $to, $perPage);

        return view('reporting::admin.orders.index', $data);
    }
}
