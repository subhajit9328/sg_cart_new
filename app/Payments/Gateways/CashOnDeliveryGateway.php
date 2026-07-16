<?php

namespace App\Payments\Gateways;

use App\Models\PaymentMethod;
use App\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Enums\PaymentStatus;

class CashOnDeliveryGateway implements PaymentGatewayInterface
{
    public function getId(): string
    {
        return 'cod';
    }

    public function getName(): string
    {
        return PaymentMethod::query()
            ->where('id', $this->getId())
            ->value('name');
    }

    public function getDescription(): string
    {
        return 'Pay with cash upon delivery of your order.';
    }

    public function getConfigSchema(): array
    {
        return [
            'instructions' => [
                'label' => 'Payment Instructions',
                'type' => 'textarea',
                'required' => false,
                'default' => 'Please have the exact amount of cash ready upon delivery.'
            ]
        ];
    }

    public function getFrontendFieldsView(): ?string
    {
        return 'store.payments.cod';
    }

    public function processPayment(Request $request, Order $order): array
    {
        $order->payments()->create([
            'status' => PaymentStatus::PENDING,
            'payment_method' => 'Cash on Delivery',
            'amount' => $order->total,
            'transaction_id' => 'COD-' . strtoupper(uniqid()),
        ]);

        return [
            'success' => true,
            'message' => 'Order placed with Cash on Delivery successfully.'
        ];
    }

    public function getIdentifier(): string
    {
        return $this->getId();
    }

    public function getTitle(): string
    {
        return $this->getName();
    }

    public function renderPaymentFields(): string
    {
        $view = $this->getFrontendFieldsView();
        return $view ? view($view, ['gateway' => $this])->render() : '';
    }
}
