<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ManufacturerSeeder extends Seeder
{
    public function run(): void
    {
        $manufacturers = [
            [
                'name'        => 'Samsung',
                'website'     => 'https://www.samsung.com',
                'email'       => 'support@samsung.com',
                'phone'       => '+91-1800-3000-8282',
                'address'     => '6th Floor, DLF Centre, Sansad Marg, New Delhi - 110001',
                'description' => 'South Korean multinational electronics corporation.',
            ],
            [
                'name'        => 'Apple',
                'website'     => 'https://www.apple.com',
                'email'       => 'support@apple.com',
                'phone'       => '+91-1800-4250-744',
                'address'     => 'Apple India Pvt Ltd, Bangalore - 560001',
                'description' => 'American multinational technology company.',
            ],
            [
                'name'        => 'OnePlus',
                'website'     => 'https://www.oneplus.in',
                'email'       => 'support@oneplus.in',
                'phone'       => '+91-1800-102-8411',
                'address'     => 'OnePlus Technology India, Bangalore - 560001',
                'description' => 'Chinese consumer electronics manufacturer.',
            ],
            [
                'name'        => 'Nike',
                'website'     => 'https://www.nike.com/in',
                'email'       => 'nike-india@nike.com',
                'phone'       => '+91-1800-102-6453',
                'address'     => 'Nike India Pvt Ltd, Mumbai - 400051',
                'description' => 'American multinational footwear and apparel corporation.',
            ],
            [
                'name'        => 'Adidas',
                'website'     => 'https://www.adidas.co.in',
                'email'       => 'service@adidas.in',
                'phone'       => '+91-1800-419-4300',
                'address'     => 'Adidas India Marketing Pvt Ltd, Gurgaon - 122002',
                'description' => 'German multinational sportswear manufacturer.',
            ],
            [
                'name'        => 'Prestige',
                'website'     => 'https://www.prestigegroup.com',
                'email'       => 'care@prestigesmartlife.com',
                'phone'       => '+91-1800-103-3333',
                'address'     => 'TTK Prestige Ltd, Bangalore - 560016',
                'description' => 'Indian manufacturer of kitchen appliances and cookware.',
            ],
            [
                'name'        => 'Acme Corp',
                'website'     => 'https://www.acmecorp.example.com',
                'email'       => 'info@acmecorp.example.com',
                'phone'       => '+91-9000000000',
                'address'     => '123 Business Park, Kolkata - 700001',
                'description' => 'Generic vendor for testing and demo products.',
            ],
        ];

        foreach ($manufacturers as $data) {
            Manufacturer::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    ...$data,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Manufacturers seeded.');
    }
}