<?php

namespace SGCart\LogisticTracking\Actions;

use App\Actions\LogActivity;
use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Support\Carbon;
use SGCart\LogisticTracking\DTO\LogisticTrackingData;
use SGCart\LogisticTracking\Models\ShippingCourier;

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

        $courier = $shippingCourierId ? ShippingCourier::find($shippingCourierId) : null;
        if ($courier) {
            $shippingCarrier = $courier->name;
        }

        $oldValues = $this->getOldTrackingValues($order->tracking);

        // Auto-generate details if order is Shipped and tracking number is empty
        $this->autoGenerateShippedDetails($data->order_status, $trackingNumber, $shippingCarrier, $estimatedDeliveryAt);

        // Generate tracking URL if empty but courier / carrier is known
        $trackingUrl = $this->generateTrackingUrl($trackingUrl, $trackingNumber, $courier, $shippingCarrier);

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

        $newValues = [
            'tracking_number' => $trackingNumber,
            'shipping_courier_id' => $shippingCourierId,
            'shipping_carrier' => $shippingCarrier,
            'tracking_url' => $trackingUrl,
            'estimated_delivery_at' => $this->normalizeDate($estimatedDeliveryAt),
        ];

        // Log activity if the LogActivity action class is available in the main application and there are changes
        if ($this->hasChanges($oldValues, $newValues) && class_exists(LogActivity::class)) {
            app(LogActivity::class)->capture(
                description: 'Updated logistics tracking for order: '.$order->order_number,
                event: 'order.logistics_updated',
                subject: $order,
                attributeChanges: [
                    'old' => $oldValues,
                    'new' => $newValues,
                ]
            );
        }
    }

    /**
     * Get the old tracking values normalized for comparison.
     */
    private function getOldTrackingValues($oldTracking): array
    {
        return $oldTracking ? [
            'tracking_number' => $oldTracking->tracking_number,
            'shipping_courier_id' => $oldTracking->shipping_courier_id,
            'shipping_carrier' => $oldTracking->shipping_carrier,
            'tracking_url' => $oldTracking->tracking_url,
            'estimated_delivery_at' => $this->normalizeDate($oldTracking->estimated_delivery_at),
        ] : [
            'tracking_number' => null,
            'shipping_courier_id' => null,
            'shipping_carrier' => null,
            'tracking_url' => null,
            'estimated_delivery_at' => null,
        ];
    }

    /**
     * Auto-generate tracking details if order is Shipped and tracking number is empty.
     */
    private function autoGenerateShippedDetails(string $orderStatus, ?string &$trackingNumber, ?string &$shippingCarrier, &$estimatedDeliveryAt): void
    {
        if ($orderStatus === 'Shipped' && empty($trackingNumber)) {
            $trackingNumber = 'SG-TRK-'.rand(10000000, 99999999);
            $shippingCarrier = $shippingCarrier ?? 'Delhivery Express';
            $estimatedDeliveryAt = $estimatedDeliveryAt ?? now()->addDays(5)->format('Y-m-d H:i:s');
        }
    }

    /**
     * Generate the tracking URL based on carrier/courier template if empty.
     */
    private function generateTrackingUrl(?string $trackingUrl, ?string $trackingNumber, $courier, ?string $shippingCarrier): ?string
    {
        if (! empty($trackingUrl) || empty($trackingNumber)) {
            return $trackingUrl;
        }

        if ($courier && $courier->url) {
            if (str_contains($courier->url, '{tracking_number}')) {
                return str_replace('{tracking_number}', $trackingNumber, $courier->url);
            }

            return rtrim($courier->url, '/').'/'.$trackingNumber;
        }

        if ($shippingCarrier === 'Delhivery Express') {
            return 'https://www.delhivery.com/track/package/'.$trackingNumber;
        }

        return null;
    }

    /**
     * Check if there are differences between old and new tracking values.
     */
    private function hasChanges(array $oldValues, array $newValues): bool
    {
        foreach ($newValues as $key => $val) {
            $oldVal = $oldValues[$key] ?? null;
            $newVal = $val ?? null;

            // Normalize empty string to null
            if ($oldVal === '') {
                $oldVal = null;
            }
            if ($newVal === '') {
                $newVal = null;
            }

            if ($oldVal != $newVal) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize date to Y-m-d format.
     */
    private function normalizeDate($date): ?string
    {
        if (! $date) {
            return null;
        }
        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format activity log for logistics events.
     */
    public static function formatActivityLog(ActivityLog $activity): string
    {
        if ($activity->event === 'order.logistics_updated') {
            $changes = [];
            $old = $activity->attribute_changes['old'] ?? [];
            $new = $activity->attribute_changes['new'] ?? [];

            $fields = [
                'tracking_number' => 'Tracking number',
                'tracking_url' => 'Tracking URL',
                'shipping_carrier' => 'Shipping carrier',
                'estimated_delivery_at' => 'Estimated delivery date',
            ];

            foreach ($fields as $key => $label) {
                $oldVal = $old[$key] ?? null;
                $newVal = $new[$key] ?? null;

                if ($oldVal !== $newVal) {
                    $oldDisplay = $oldVal;
                    $newDisplay = $newVal;

                    // Format date nicely if it's the estimated delivery date
                    if ($key === 'estimated_delivery_at') {
                        if ($oldVal) {
                            $oldDisplay = date('M d, Y', strtotime($oldVal));
                        }
                        if ($newVal) {
                            $newDisplay = date('M d, Y', strtotime($newVal));
                        }
                    }

                    if (empty($oldVal) && ! empty($newVal)) {
                        $changes[] = "Added {$label}: <span class='font-semibold text-slate-805 dark:text-slate-200'>{$newDisplay}</span>";
                    } elseif (! empty($oldVal) && empty($newVal)) {
                        $changes[] = "Removed {$label}";
                    } else {
                        $changes[] = "{$label} changed from <span class='font-semibold text-slate-805 dark:text-slate-200'>{$oldDisplay}</span> to <span class='font-semibold text-slate-805 dark:text-slate-200'>{$newDisplay}</span>";
                    }
                }
            }

            if (empty($changes)) {
                return 'Logistics details updated';
            }

            return implode(', ', $changes);
        }

        return $activity->description;
    }

    /**
     * Get timeline-specific data for logistics events.
     */
    public static function getTimelineData(\App\Models\ActivityLog $activity): array
    {
        if ($activity->event === 'order.logistics_updated') {
            $old = $activity->attribute_changes['old'] ?? [];
            $new = $activity->attribute_changes['new'] ?? [];

            $carrier = $new['shipping_carrier'] ?? $old['shipping_carrier'] ?? 'Courier';
            $trackingNo = $new['tracking_number'] ?? $old['tracking_number'] ?? '';

            $description = "Logistics details updated.";
            if ($trackingNo) {
                $description = "Dispatched via {$carrier} with Tracking ID: <span class='font-semibold'>{$trackingNo}</span>.";
            }

            $changes = [];
            $fields = [
                'tracking_number' => 'Tracking number',
                'tracking_url' => 'Tracking URL',
                'shipping_carrier' => 'Shipping carrier',
                'estimated_delivery_at' => 'Estimated delivery date',
            ];

            foreach ($fields as $key => $label) {
                $oldVal = $old[$key] ?? null;
                $newVal = $new[$key] ?? null;

                if ($oldVal !== $newVal) {
                    $oldDisplay = $oldVal;
                    $newDisplay = $newVal;

                    if ($key === 'estimated_delivery_at') {
                        if ($oldVal) $oldDisplay = date('M d, Y', strtotime($oldVal));
                        if ($newVal) $newDisplay = date('M d, Y', strtotime($newVal));
                    }

                    if (empty($oldVal) && !empty($newVal)) {
                        $changes[] = "Added {$label}: <span class='font-semibold text-slate-800 dark:text-slate-200'>{$newDisplay}</span>";
                    } elseif (!empty($oldVal) && empty($newVal)) {
                        $changes[] = "Removed {$label}";
                    } else {
                        $changes[] = "{$label} changed from <span class='font-semibold text-slate-800 dark:text-slate-200'>{$oldDisplay}</span> to <span class='font-semibold text-slate-800 dark:text-slate-200'>{$newDisplay}</span>";
                    }
                }
            }

            return [
                'title' => 'Order Shipped (Transit Started)',
                'description' => $description,
                'icon' => 'fa-truck',
                'icon_color' => 'bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/30',
                'extra_details' => !empty($changes) ? implode('<br>', $changes) : null,
            ];
        }

        return [
            'title' => 'Logistics Update',
            'description' => $activity->description,
            'icon' => 'fa-truck',
            'icon_color' => 'bg-slate-50 text-slate-600 border border-slate-200/50 dark:bg-slate-900/50 dark:text-slate-300 dark:border-slate-800/60',
        ];
    }
}
