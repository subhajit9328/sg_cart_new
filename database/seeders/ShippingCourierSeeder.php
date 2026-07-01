<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ShippingCourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (class_exists(\SGCart\LogisticTracking\Models\ShippingCourier::class)) {
            $couriers = [
                [
                    'name' => 'DHL Express',
                    'url' => 'https://www.dhl.com/us-en/home/tracking.html',
                    'support_email' => 'support@dhl.com',
                ],
                [
                    'name' => 'Delhivery Express',
                    'url' => 'https://www.delhivery.com/tracking',
                    'support_email' => 'support@delhivery.com',
                ],
                [
                    'name' => 'Blue Dart',
                    'url' => 'https://bluedart.com/tracking',
                    'support_email' => 'customerservice@bluedart.com',
                ],
            ];

            foreach ($couriers as $courier) {
                \SGCart\LogisticTracking\Models\ShippingCourier::firstOrCreate(
                    ['name' => $courier['name']],
                    $courier
                );
            }
        }
    }
}
