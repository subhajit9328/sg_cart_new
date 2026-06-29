<?php

namespace SGCart\LogisticTracking\DTO;

class LogisticTrackingData
{
    /**
     * Create a new DTO instance.
     */
    public function __construct(
        public readonly string $order_status,
        public readonly ?string $tracking_number = null,
        public readonly ?string $shipping_carrier = null,
        public readonly ?string $tracking_url = null,
        public readonly mixed $estimated_delivery_at = null,
    ) {}

    /**
     * Create a DTO instance from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            order_status: $data['order_status'],
            tracking_number: $data['tracking_number'] ?? null,
            shipping_carrier: $data['shipping_carrier'] ?? null,
            tracking_url: $data['tracking_url'] ?? null,
            estimated_delivery_at: $data['estimated_delivery_at'] ?? null,
        );
    }
}
