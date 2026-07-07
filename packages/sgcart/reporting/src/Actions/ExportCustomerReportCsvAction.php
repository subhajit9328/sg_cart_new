<?php

namespace SGCart\Reporting\Actions;

use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\CustomerReportQuery;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCustomerReportCsvAction
{
    public function __construct(
        protected CustomerReportQuery $query
    ) {}

    /**
     * Generate streamed CSV response for Customers Report.
     */
    public function execute(Carbon $from, Carbon $to): StreamedResponse
    {
        $fileName = sprintf(
            'customers-report-%s-to-%s.csv',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers
            fputcsv($handle, [
                'Customer Name',
                'Email',
                'Phone',
                'Total Orders in Period',
                'Total Purchase Amount (₹)',
                'Average Order Value (₹)',
                'Last Order Date',
                'Customer Status',
            ]);

            // Query lazily
            $this->query->customersDetailedQueryBuilder($from, $to)
                ->lazy()
                ->each(function ($row) use ($from, $to, $handle) {
                    // Check if they registered during range (New) or before (Returning)
                    $customer = \App\Models\Customer::find($row->customer_id);
                    $status = 'Returning';
                    if ($customer && $customer->created_at->between($from->copy()->startOfDay(), $to->copy()->endOfDay())) {
                        $status = 'New';
                    }

                    fputcsv($handle, [
                        $row->first_name . ' ' . $row->last_name,
                        $row->email ?? '—',
                        $row->phone ?? '—',
                        $row->total_orders,
                        number_format($row->total_spent, 2, '.', ''),
                        number_format($row->aov, 2, '.', ''),
                        Carbon::parse($row->last_order_date)->format('Y-m-d H:i:s'),
                        $status,
                    ]);
                });

            fclose($handle);
        }, 200, $headers);
    }
}
