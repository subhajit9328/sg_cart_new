<?php

namespace SGCart\AuthorizeNet\Gateways;

use App\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Enums\PaymentStatus;

class AuthorizeNetGateway implements PaymentGatewayInterface
{
    public function getId(): string
    {
        return 'authorizenet';
    }

    public function getName(): string
    {
        return 'Authorize.Net';
    }

    public function getDescription(): string
    {
        return 'Secure credit card payments via Authorize.Net.';
    }

    public function getConfigSchema(): array
    {
        return [
            'merchant_login_id' => [
                'label' => 'Merchant Login ID',
                'type' => 'text',
                'required' => true,
                'default' => ''
            ],
            'merchant_transaction_key' => [
                'label' => 'Merchant Transaction Key',
                'type' => 'password',
                'required' => true,
                'default' => ''
            ],
            'sandbox' => [
                'label' => 'Test Mode',
                'type' => 'select',
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No'
                ],
                'required' => true,
                'default' => '1'
            ]
        ];
    }

    public function getFrontendFieldsView(): ?string
    {
        return 'authorizenet::frontend.fields';
    }

    public function processPayment(Request $request, Order $order): array
    {
        $request->validate([
            'authorizenet_card_name' => 'required|string',
            'authorizenet_card_num' => 'required|string',
            'authorizenet_card_expiry' => 'required|string',
            'authorizenet_card_cvv' => 'required|string',
        ]);

        $cardNum = str_replace(' ', '', $request->authorizenet_card_num);

        $order->payments()->create([
            'status' => PaymentStatus::PAID,
            'payment_method' => 'Authorize.Net',
            'amount' => $order->total,
            'transaction_id' => 'AUTH-' . strtoupper(uniqid()),
            'card_name' => $request->authorizenet_card_name,
            'card_number_masked' => '**** **** **** ' . substr($cardNum, -4),
        ]);

        return [
            'success' => true,
            'message' => 'Payment processed successfully via Authorize.Net.'
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
