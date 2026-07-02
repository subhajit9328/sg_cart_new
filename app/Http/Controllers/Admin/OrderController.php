<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Payment Status filter
        if ($request->filled('payment_status')) {
            $query->whereHas('payments', function ($q) use ($request) {
                $q->where('status', $request->input('payment_status'));
            });
        }

        // Calculate KPI Metrics (overall database state)
        $totalOrders = Order::count();
        $processingOrdersCount = Order::where('status', 'Processing')->count();
        $deliveredOrdersCount = Order::where('status', 'Delivered')->count();
        $deliveredValue = Order::where('status', 'Delivered')->sum('total');
        $cancelledOrders = Order::where('status', 'Cancelled')->count();

        // Sorting logic
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSortFields = ['order_number', 'first_name', 'status', 'total', 'created_at'];
        if ($sortBy === 'payment_status') {
            $query->orderBy(
                \App\Models\Payment::select('status')
                    ->whereColumn('order_id', 'orders.id')
                    ->latest()
                    ->take(1),
                $sortOrder === 'asc' ? 'asc' : 'desc'
            );
        } elseif (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'processingOrdersCount',
            'deliveredOrdersCount',
            'deliveredValue',
            'cancelledOrders'
        ));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['items', 'customer', 'payments', 'activities.causer']);

        $couriers = [];
        if (class_exists(\SGCart\LogisticTracking\Models\ShippingCourier::class)) {
            $couriers = \SGCart\LogisticTracking\Models\ShippingCourier::all();
        }

        return view('admin.orders.show', compact('order', 'couriers'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validationRules = [
            'status' => 'required|in:Processing,Shipped,Delivered,Cancelled',
            'payment_status' => 'required|in:Pending,Paid,Failed',
        ];

        $packageInstalled = class_exists(\SGCart\LogisticTracking\Actions\UpdateLogisticTrackingAction::class);

        if ($packageInstalled) {
            $isShipped = $request->input('status') === 'Shipped';
            $validationRules = array_merge($validationRules, [
                'tracking_number' => $isShipped ? 'required|string|max:100' : 'nullable|string|max:100',
                'shipping_courier_id' => $isShipped ? 'required' : 'nullable',
                'tracking_url' => $isShipped ? 'required|url|max:255' : 'nullable|url|max:255',
                'estimated_delivery_at' => $isShipped ? 'required|date' : 'nullable|date',
            ]);
        }

        $data = $request->validate($validationRules, [], [
            'tracking_number' => 'tracking number',
            'shipping_courier_id' => 'shipping carrier',
            'tracking_url' => 'tracking URL',
            'estimated_delivery_at' => 'estimated delivery date',
        ]);

        $order->update(['status' => $data['status']]);

        // Update payment status in payments ledger (create a new entry if status changed)
        $latestPayment = $order->payments()->latest()->first();
        if (!$latestPayment || ($latestPayment->status->value ?? $latestPayment->status) !== $data['payment_status']) {
            $order->payments()->create([
                'payment_method' => $latestPayment ? $latestPayment->payment_method : 'Manual Update',
                'amount' => $order->total,
                'status' => $data['payment_status'],
                'transaction_id' => $latestPayment ? $latestPayment->transaction_id : null,
                'card_name' => $latestPayment ? $latestPayment->card_name : null,
                'card_number_masked' => $latestPayment ? $latestPayment->card_number_masked : null,
            ]);
        }

        if ($packageInstalled) {
            $shippingCourierIdInput = $data['shipping_courier_id'] ?? null;
            $shippingCourierId = null;
            $shippingCarrier = null;

            if ($shippingCourierIdInput === '__KEEP__') {
                $shippingCarrier = $order->shipping_carrier;
                $shippingCourierId = null;
            } elseif (!empty($shippingCourierIdInput)) {
                $shippingCourierId = (int)$shippingCourierIdInput;
            }

            $dto = new \SGCart\LogisticTracking\DTO\LogisticTrackingData(
                order_status: $data['status'],
                tracking_number: $data['tracking_number'] ?? null,
                shipping_carrier: $shippingCarrier,
                tracking_url: $data['tracking_url'] ?? null,
                estimated_delivery_at: $data['estimated_delivery_at'] ?? null,
                shipping_courier_id: $shippingCourierId
            );

            app(\SGCart\LogisticTracking\Actions\UpdateLogisticTrackingAction::class)->execute($order, $dto);
        }

        return redirect()->back()->with('success', 'Order status has been updated successfully.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order has been deleted successfully.');
    }

    /**
     * Stream the PDF Invoice in the browser.
     */
    public function streamInvoice(Order $order)
    {
        $order->load(['items.product']);
        $pdf = Pdf::loadView('store.invoice-pdf', compact('order'));
        return $pdf->stream("invoice-{$order->order_number}.pdf");
    }
}
