<?php

namespace SGCart\LogisticTracking\Actions;

use App\Actions\LogActivity;
use App\Models\Order;
use SGCart\LogisticTracking\DTO\LogisticTrackingData;

class UpdateLogisticTrackingAction
{
    /**
     * Execute the action to update or create order tracking.
     */
    public function execute(Order $order, LogisticTrackingData $data): void
    {
        $trackingNumber = $data->tracking_number;
        $shippingCourierId = $data->shipping_courier_id;
        $shippingCarrier = $data->shipping_carrier;
        $trackingUrl = $data->tracking_url;
        $estimatedDeliveryAt = $data->estimated_delivery_at;

        $courier = null;
        if ($shippingCourierId) {
            $courier = \SGCart\LogisticTracking\Models\ShippingCourier::find($shippingCourierId);
            if ($courier) {
                $shippingCarrier = $courier->name;
            }
        }

        // Capture old values for activity log
        $oldTracking = $order->tracking;
        $oldValues = $oldTracking ? [
            'tracking_number' => $oldTracking->tracking_number,
            'shipping_courier_id' => $oldTracking->shipping_courier_id,
            'shipping_carrier' => $oldTracking->shipping_carrier,
            'tracking_url' => $oldTracking->tracking_url,
            'estimated_delivery_at' => $oldTracking->estimated_delivery_at?->format('Y-m-d H:i:s'),
        ] : [
            'tracking_number' => null,
            'shipping_courier_id' => null,
            'shipping_carrier' => null,
            'tracking_url' => null,
            'estimated_delivery_at' => null,
        ];

        // If status is Shipped and tracking number is empty, auto-generate details (mirroring original controller logic)
        if ($data->order_status === 'Shipped' && empty($trackingNumber)) {
            $trackingNumber = 'SG-TRK-'.rand(10000000, 99999999);
            $shippingCarrier = $shippingCarrier ?? 'Delhivery Express';
            $estimatedDeliveryAt = $estimatedDeliveryAt ?? now()->addDays(5)->format('Y-m-d H:i:s');
        }

        // Generate tracking URL if empty but courier / carrier is known
        if (empty($trackingUrl) && !empty($trackingNumber)) {
            if ($courier && $courier->url) {
                if (str_contains($courier->url, '{tracking_number}')) {
                    $trackingUrl = str_replace('{tracking_number}', $trackingNumber, $courier->url);
                } else {
                    $trackingUrl = rtrim($courier->url, '/') . '/' . $trackingNumber;
                }
            } elseif ($shippingCarrier === 'Delhivery Express') {
                $trackingUrl = 'https://www.delhivery.com/track/package/'.$trackingNumber;
            }
        }

        // Update or create the tracking record
        $order->tracking()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'tracking_number' => $trackingNumber,
                'shipping_courier_id' => $shippingCourierId,
                'shipping_carrier' => $shippingCarrier,
                'tracking_url' => $trackingUrl,
                'estimated_delivery_at' => $estimatedDeliveryAt,
            ]
        );

        // Log activity if the LogActivity action class is available in the main application
        if (class_exists(LogActivity::class)) {
            app(LogActivity::class)->capture(
                description: 'Updated logistics tracking for order: '.$order->order_number,
                event: 'order.logistics_updated',
                subject: $order,
                attributeChanges: [
                    'old' => $oldValues,
                    'new' => [
                        'tracking_number' => $trackingNumber,
                        'shipping_courier_id' => $shippingCourierId,
                        'shipping_carrier' => $shippingCarrier,
                        'tracking_url' => $trackingUrl,
                        'estimated_delivery_at' => $estimatedDeliveryAt instanceof \DateTimeInterface ? $estimatedDeliveryAt->format('Y-m-d H:i:s') : $estimatedDeliveryAt,
                    ],
                ]
            );
        }
    }
}
