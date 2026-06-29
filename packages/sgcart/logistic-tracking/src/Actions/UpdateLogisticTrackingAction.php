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
        $shippingCarrier = $data->shipping_carrier;
        $trackingUrl = $data->tracking_url;
        $estimatedDeliveryAt = $data->estimated_delivery_at;

        // Capture old values for activity log
        $oldTracking = $order->tracking;
        $oldValues = $oldTracking ? [
            'tracking_number' => $oldTracking->tracking_number,
            'shipping_carrier' => $oldTracking->shipping_carrier,
            'tracking_url' => $oldTracking->tracking_url,
            'estimated_delivery_at' => $oldTracking->estimated_delivery_at?->format('Y-m-d H:i:s'),
        ] : [
            'tracking_number' => null,
            'shipping_carrier' => null,
            'tracking_url' => null,
            'estimated_delivery_at' => null,
        ];

        // If status is Shipped and tracking number is empty, auto-generate details (mirroring original controller logic)
        if ($data->order_status === 'Shipped' && empty($trackingNumber)) {
            $trackingNumber = 'SG-TRK-'.rand(10000000, 99999999);
            $shippingCarrier = $shippingCarrier ?? 'Delhivery Express';
            $trackingUrl = $trackingUrl ?? 'https://www.delhivery.com/track/package/'.$trackingNumber;
            $estimatedDeliveryAt = $estimatedDeliveryAt ?? now()->addDays(5)->format('Y-m-d H:i:s');
        }

        // Update or create the tracking record
        $order->tracking()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'tracking_number' => $trackingNumber,
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
                        'shipping_carrier' => $shippingCarrier,
                        'tracking_url' => $trackingUrl,
                        'estimated_delivery_at' => $estimatedDeliveryAt instanceof \DateTimeInterface ? $estimatedDeliveryAt->format('Y-m-d H:i:s') : $estimatedDeliveryAt,
                    ],
                ]
            );
        }
    }
}
