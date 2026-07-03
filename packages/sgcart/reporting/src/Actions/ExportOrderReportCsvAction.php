<?php

namespace SGCart\Reporting\Actions;

use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\OrderReportQuery;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportOrderReportCsvAction
{
    public function __construct(
        protected OrderReportQuery $query
    ) {}

    /**
     * Generate a streamed CSV response of orders in the date range.
     */
    public function execute(Carbon $from, Carbon $to): StreamedResponse
    {
        $fileName = sprintf(
            'orders-report-%s-to-%s.csv',
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

            // Add UTF-8 BOM for proper Indian Rupee symbol & character decoding in Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write CSV headers
            fputcsv($handle, [
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Order Date',
                'Order Status',
                'Payment Status',
                'Payment Method',
                'Subtotal (₹)',
                'Discount (₹)',
                'Tax (₹)',
                'Shipping Charge (₹)',
                'Total Amount (₹)',
                'Net Amount (₹)',
            ]);

            // Query orders lazily to keep memory footprint extremely low
            $this->query->baseQuery($from, $to)
                ->with(['customer', 'payments' => fn($q) => $q->latest()->limit(1)])
                ->orderBy('created_at', 'desc')
                ->lazy()
                ->each(function ($order) use ($handle) {
                    $netAmount = $order->total - ($order->discount ?? 0);
                    $latestPayment = $order->payments->first();
                    $paymentStatus = $latestPayment?->status?->value ?? $latestPayment?->status ?? 'Pending';
                    $paymentMethod = $latestPayment?->payment_method ?? '—';

                    fputcsv($handle, [
                        $order->order_number,
                        $order->first_name . ' ' . $order->last_name,
                        $order->email ?? '—',
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->status?->value ?? $order->status,
                        $paymentStatus,
                        $paymentMethod,
                        number_format($order->subtotal, 2, '.', ''),
                        number_format($order->discount ?? 0, 2, '.', ''),
                        number_format($order->tax ?? 0, 2, '.', ''),
                        number_format($order->shipping_charge ?? 0, 2, '.', ''),
                        number_format($order->total, 2, '.', ''),
                        number_format($netAmount, 2, '.', ''),
                    ]);
                });

            fclose($handle);
        }, 200, $headers);
    }
}
