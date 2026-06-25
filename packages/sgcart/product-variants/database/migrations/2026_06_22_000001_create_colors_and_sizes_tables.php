<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Color schema creation/deletion
        if (config('product-variants.features.color', true)) {
            if (!Schema::hasTable('colors')) {
                Schema::create('colors', function (Blueprint $table) {
                    $table->id();
                    $table->string('name')->unique();
                    $table->string('hex_code');
                    $table->timestamps();
                });
            }
        } else {
            Schema::dropIfExists('colors');
        }

        // Size schema creation/deletion
        if (config('product-variants.features.size', true)) {
            if (!Schema::hasTable('sizes')) {
                Schema::create('sizes', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('code')->unique();
                    $table->timestamps();
                });
            }
        } else {
            Schema::dropIfExists('sizes');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('colors');
    }
};
