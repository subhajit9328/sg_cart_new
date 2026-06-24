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
            $query->where('payment_status', $request->input('payment_status'));
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

        $allowedSortFields = ['order_number', 'first_name', 'status', 'payment_status', 'total', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
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
        $order->load(['items', 'customer']);
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
        ]);

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
