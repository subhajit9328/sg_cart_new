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
                'name'        => 'Nike',
                'website'     => 'https://www.nike.com/in',
                'email'       => 'nike-india@nike.com',
                'phone'       => '+91-1800-102-6453',
                'address'     => 'Nike India Pvt Ltd, Dalamal House, Nariman Point, Mumbai - 400021',
                'description' => 'American multinational footwear and apparel corporation known for athletic wear and sportswear.',
            ],
            [
                'name'        => 'Adidas',
                'website'     => 'https://www.adidas.co.in',
                'email'       => 'service@adidas.in',
                'phone'       => '+91-1800-419-4300',
                'address'     => 'Adidas India Marketing Pvt Ltd, Sector 33, Gurugram - 122002',
                'description' => 'German multinational sportswear manufacturer of athletic clothing and footwear.',
            ],
            [
                'name'        => 'H&M',
                'website'     => 'https://www2.hm.com/en_in',
                'email'       => 'india.customerservice@hm.com',
                'phone'       => '+91-1800-120-2999',
                'address'     => 'H & M Hennes & Mauritz India Pvt Ltd, DLF Cyber City, Gurugram - 122002',
                'description' => 'Swedish multinational fast-fashion clothing retailer offering trendy, affordable apparel.',
            ],
            [
                'name'        => 'Zara',
                'website'     => 'https://www.zara.com/in',
                'email'       => 'india@zara.com',
                'phone'       => '+91-1800-123-9272',
                'address'     => 'Inditex Trent Retail India Pvt Ltd, Churchgate, Mumbai - 400020',
                'description' => 'Spanish fast-fashion brand renowned for high-street clothing and accessories.',
            ],
            [
                'name'        => 'Levi\'s',
                'website'     => 'https://www.levi.com/IN',
                'email'       => 'india.customercare@levi.com',
                'phone'       => '+91-1800-102-0935',
                'address'     => 'Levi Strauss India Pvt Ltd, Vasant Kunj, New Delhi - 110070',
                'description' => 'American clothing company known globally for denim jeans and casual apparel.',
            ],
            [
                'name'        => 'Puma',
                'website'     => 'https://in.puma.com',
                'email'       => 'customercare.india@puma.com',
                'phone'       => '+91-1800-102-7862',
                'address'     => 'Puma Sports India Pvt Ltd, Prestige Meridian II, M.G. Road, Bangalore - 560001',
                'description' => 'German multinational company that designs and manufactures athletic and casual footwear, apparel and accessories.',
            ],
            [
                'name'        => 'Raymond',
                'website'     => 'https://www.raymond.in',
                'email'       => 'customercare@raymond.in',
                'phone'       => '+91-1800-209-9966',
                'address'     => 'Raymond Ltd, Plot No. 156/H, Thane - 400606',
                'description' => 'Iconic Indian fashion and fabric brand specialising in fine suiting, ethnic wear and formal clothing.',
            ],
            [
                'name'        => 'Fabindia',
                'website'     => 'https://www.fabindia.com',
                'email'       => 'customercare@fabindia.net',
                'phone'       => '+91-1800-425-4448',
                'address'     => 'Fabindia Overseas Pvt Ltd, 14 N Block, Greater Kailash - I, New Delhi - 110048',
                'description' => 'Indian retail chain selling handloom, handcrafted and organic products including ethnic clothing and accessories.',
            ],
            [
                'name'        => 'Myntra',
                'website'     => 'https://www.myntra.com',
                'email'       => 'support@myntra.com',
                'phone'       => '+91-1800-102-3466',
                'address'     => 'Myntra Designs Pvt Ltd, Embassy TechVillage, Outer Ring Road, Bangalore - 560103',
                'description' => 'India\'s leading fashion and lifestyle e-commerce platform featuring multiple private-label brands.',
            ],
            [
                'name'        => 'Louis Philippe',
                'website'     => 'https://www.louisphilippe.com',
                'email'       => 'customercare@louisphilippe.in',
                'phone'       => '+91-1800-103-3999',
                'address'     => 'Madura Fashion & Lifestyle, Bangalore House, 1 Ali Asker Road, Bangalore - 560052',
                'description' => 'Premium Indian menswear brand offering formal shirts, trousers, suits and accessories.',
            ],
            [
                'name'        => 'W for Woman',
                'website'     => 'https://www.wforwoman.com',
                'email'       => 'care@wforwoman.com',
                'phone'       => '+91-1800-120-3569',
                'address'     => 'TCNS Clothing Co Ltd, Okhla Industrial Area, Phase III, New Delhi - 110020',
                'description' => 'Indian women\'s fashion brand specialising in contemporary ethnic and fusion wear.',
            ],
            [
                'name'        => 'United Colors of Benetton',
                'website'     => 'https://www.benetton.com/in',
                'email'       => 'india.cs@benetton.com',
                'phone'       => '+91-1800-266-0203',
                'address'     => 'DCM Benetton India Ltd, Shivaji Marg, New Delhi - 110015',
                'description' => 'Italian fashion brand known for colourful casual wear, knitwear and accessories for all ages.',
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

        $this->command->info('✅ Clothing brand manufacturers seeded.');
    }
}