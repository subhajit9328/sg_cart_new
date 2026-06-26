<?php

namespace App\Payments\Contracts;

use Illuminate\Http\Request;
use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Get the unique identifier of the payment gateway.
     */
    public function getId(): string;

    /**
     * Get the display name of the payment gateway.
     */
    public function getName(): string;

    /**
     * Get the description of the payment gateway.
     */
    public function getDescription(): string;

    /**
     * Get the configurations schema required by this gateway.
     * E.g., [
     *   'public_key' => ['label' => 'Stripe Public Key', 'type' => 'text', 'required' => true],
     *   'secret_key' => ['label' => 'Stripe Secret Key', 'type' => 'password', 'required' => true]
     * ]
     */
    public function getConfigSchema(): array;

    /**
     * Get the frontend blade view name (or null) to render payment inputs on checkout.
     */
    public function getFrontendFieldsView(): ?string;

    public function processPayment(Request $request, Order $order): array;

    /**
     * Get the unique identifier of the payment gateway (compatibility alias for getId).
     */
    public function getIdentifier(): string;

    /**
     * Get the display name of the payment gateway (compatibility alias for getName).
     */
    public function getTitle(): string;

    /**
     * Render the frontend payment input fields HTML (compatibility helper).
     */
    public function renderPaymentFields(): string;
}
