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
        $order->load(['items', 'customer', 'payments']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:Processing,Shipped,Delivered,Cancelled',
            'payment_status' => 'required|in:Pending,Paid,Failed',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_carrier' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:255',
            'estimated_delivery_at' => 'nullable|date',
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
        if ($data['status'] === 'Shipped' && empty($data['tracking_number'])) {
            $data['tracking_number'] = 'SG-TRK-' . rand(10000000, 99999999);
            $data['shipping_carrier'] = $data['shipping_carrier'] ?? 'Delhivery Express';
            $data['tracking_url'] = $data['tracking_url'] ?? 'https://www.delhivery.com/track/package/' . $data['tracking_number'];
            $data['estimated_delivery_at'] = $data['estimated_delivery_at'] ?? now()->addDays(5)->format('Y-m-d H:i:s');
        }

        $order->update($data);

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
