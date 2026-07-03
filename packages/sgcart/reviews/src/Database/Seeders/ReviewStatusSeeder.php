<?php

namespace SGCart\Reviews\Database\Seeders;

use Illuminate\Database\Seeder;
use SGCart\Reviews\Enums\ReviewStatus as ReviewStatusEnum;
use SGCart\Reviews\Models\ReviewStatus;

class ReviewStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (ReviewStatusEnum::cases() as $case) {
            ReviewStatus::firstOrCreate([
                'name' => $case->value,
            ]);
        }
    }
}
