<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('sku')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->unsignedInteger('stock')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('product_variants', function (Blueprint $table) {
            $colorEnabled = config('product-variants.features.color', true);
            $hasColor = Schema::hasColumn('product_variants', 'color_id');

            if ($colorEnabled && !$hasColor) {
                $table->foreignId('color_id')->nullable()->after('product_id')->constrained('colors')->nullOnDelete();
            } elseif (!$colorEnabled && $hasColor) {
                try {
                    $table->dropForeign(['color_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('color_id');
            }

            $sizeEnabled = config('product-variants.features.size', true);
            $hasSize = Schema::hasColumn('product_variants', 'size_id');

            if ($sizeEnabled && !$hasSize) {
                $table->foreignId('size_id')->nullable()->after('product_id')->constrained('sizes')->nullOnDelete();
            } elseif (!$sizeEnabled && $hasSize) {
                try {
                    $table->dropForeign(['size_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('size_id');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
