<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $tax      = round($subtotal * 0.18, 2);
        $shipping = $this->faker->randomFloat(2, 0, 200);
        $discount = $this->faker->randomFloat(2, 0, $subtotal * 0.1);
        $total    = $subtotal + $tax + $shipping - $discount;

        return [
            'order_number'  => 'ORD-' . strtoupper($this->faker->unique()->bothify('####??')),
            'customer_id'   => null,
            'first_name'    => $this->faker->firstName(),
            'last_name'     => $this->faker->lastName(),
            'email'         => $this->faker->safeEmail(),
            'phone'         => $this->faker->phoneNumber(),
            'address'       => $this->faker->streetAddress(),
            'city'          => $this->faker->city(),
            'state'         => $this->faker->state(),
            'zip'           => $this->faker->postcode(),
            'country'       => 'India',
            'subtotal'      => $subtotal,
            'tax'           => $tax,
            'tax_method'    => 'inclusive',
            'shipping_charge' => $shipping,
            'shipping_method' => 'Standard',
            'discount'      => $discount,
            'total'         => $total,
            'status'        => $this->faker->randomElement(['Processing', 'Shipped', 'Delivered', 'Cancelled']),
        ];
    }
}
