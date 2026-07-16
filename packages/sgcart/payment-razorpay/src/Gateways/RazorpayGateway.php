<?php

namespace SGCart\Razorpay\Gateways;

use App\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayGateway implements PaymentGatewayInterface
{
    public function getId(): string
    {
        return 'razorpay';
    }

    public function getName(): string
    {
        return PaymentMethod::query()
            ->where('id', $this->getId())
            ->value('name');
    }

    public function getDescription(): string
    {
        return 'Pay securely using Razorpay (Cards, UPI, Netbanking).';
    }

    public function getConfigSchema(): array
    {
        return [
            'key_id' => [
                'label' => 'Razorpay Key ID',
                'type' => 'text',
                'required' => true,
                'default' => ''
            ],
            'key_secret' => [
                'label' => 'Razorpay Key Secret',
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
        return 'razorpay::frontend.fields';
    }

    public function processPayment(Request $request, Order $order): array
    {
        // Get config credentials from the payment method
        $paymentMethod = PaymentMethod::find('razorpay');
        $config = $paymentMethod ? ($paymentMethod->config ?? []) : [];
        $keyId = $config['key_id'] ?? '';
        $keySecret = $config['key_secret'] ?? '';

        if (empty($keyId) || empty($keySecret)) {
            throw new \Exception("Payment gateway is temporarily unavailable. Please try again or select another payment method.");
        }

        // Amount in paise (INR sub-units)
        $amountInPaise = (int) round($order->total * 100);

        try {
            $customerPayload = [
                'name' => trim($order->first_name . ' ' . $order->last_name),
            ];
            if (!empty($order->email)) {
                $customerPayload['email'] = $order->email;
            }
            if (!empty($order->phone)) {
                $customerPayload['contact'] = $order->phone;
            }

            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post('https://api.razorpay.com/v1/payment_links', [
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'accept_partial' => false,
                    'reference_id' => $order->order_number,
                    'description' => 'Payment for Order #' . $order->order_number,
                    'customer' => $customerPayload,
                    'notify' => [
                        'sms' => false,
                        'email' => false,
                    ],
                    'reminder_enable' => false,
                    'callback_url' => route('razorpay.callback', ['order_ulid' => $order->ulid]),
                    'callback_method' => 'get'
                ]);

            if ($response->failed()) {
                Log::error('Razorpay API Error', ['response' => $response->body()]);
                if ($response->status() === 401) {
                    throw new \Exception("Payment gateway is temporarily unavailable. Please try again or select another payment method.");
                }
                throw new \Exception("Payment gateway is temporarily unavailable. Please try again or select another payment method.");
            }

            $shortUrl = $response->json('short_url');
            $paymentLinkId = $response->json('id');

            // Log pending payment in local ledger
            $order->payments()->create([
                'status' => PaymentStatus::PENDING,
                'payment_method' => 'Razorpay',
                'amount' => $order->total,
                'transaction_id' => $paymentLinkId,
                'payload' => $response->json()
            ]);

            return [
                'success' => true,
                'redirect_url' => $shortUrl,
                'message' => 'Redirecting to Razorpay payment gateway.'
            ];

        } catch (\Exception $e) {
            Log::error('Razorpay processPayment exception: ' . $e->getMessage());
            throw $e;
        }
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
