<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    /**
     * Show admin payment configurations.
     */
    public function index()
    {
        $gateways = collect(app('payment.manager')->getRegisteredGateways())
            ->sortBy(fn($gateway) => strtolower($gateway->getName()))
            ->all();

        // Get DB configuration/states for all registered gateways
        $dbMethods = PaymentMethod::all()->keyBy('id');

        return view('admin.payments.settings', compact('gateways', 'dbMethods'));
    }

    /**
     * Update configuration details in DB.
     */
    public function update(Request $request)
    {
        $gatewayId = $request->input('gateway_id');

        if ($gatewayId) {
            // Check if this is a toggle action
            if ($request->input('action') === 'toggle') {
                $method = PaymentMethod::firstOrNew(['id' => $gatewayId]);
                $method->name = $method->name ?? ucfirst($gatewayId);
                $method->is_installed = true;
                $method->is_enabled = !$method->is_enabled;
                $method->save();

                $status = $method->is_enabled ? 'enabled' : 'disabled';
                return redirect()->back()->with('success', "{$method->name} has been {$status} successfully!");
            }

            // Single gateway configuration update
            $data = $request->input("settings.{$gatewayId}", []);
            $method = PaymentMethod::firstOrNew(['id' => $gatewayId]);
            $method->name = $data['name'] ?? $method->name ?? ucfirst($gatewayId);
            $method->description = $data['description'] ?? $method->description ?? null;
            $method->is_enabled = isset($data['is_enabled']);
            $method->is_installed = true;
            $method->config = $data['config'] ?? [];
            $method->save();

            return redirect()->back()->with('success', "{$method->name} configuration updated successfully!");
        }

        // Fallback to bulk update
        $settings = $request->input('settings', []);

        foreach ($settings as $id => $data) {
            $method = PaymentMethod::firstOrNew(['id' => $id]);
            $method->name = $data['name'] ?? $method->name ?? ucfirst($id);
            $method->description = $data['description'] ?? null;
            $method->is_enabled = isset($data['is_enabled']);
            $method->is_installed = true;
            $method->config = $data['config'] ?? [];
            $method->save();
        }

        // Handle disabled checkboxes for bulk update
        $paymentManager = app('payment.manager');
        foreach ($paymentManager->getRegisteredGateways() as $gateway) {
            if (!isset($settings[$gateway->getId()])) {
                $method = PaymentMethod::find($gateway->getId());
                if ($method) {
                    $method->update(['is_enabled' => false]);
                }
            }
        }

        return redirect()->back()->with('success', 'Payment configurations updated successfully!');
    }
}
