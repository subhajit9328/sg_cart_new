<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index();
                $table->enum('type', ['flat', 'percent'])->default('flat');
                $table->decimal('value', 8, 2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('coupons', function (Blueprint $table) {
            $minCartEnabled = config('coupons.features.min_cart_total', true);
            $hasMinCart = Schema::hasColumn('coupons', 'min_cart_total');

            if ($minCartEnabled && !$hasMinCart) {
                $table->decimal('min_cart_total', 8, 2)->default(0.00)->after('value');
            } elseif (!$minCartEnabled && $hasMinCart) {
                $table->dropColumn('min_cart_total');
            }

            $expiresEnabled = config('coupons.features.expires_at', true);
            $hasExpires = Schema::hasColumn('coupons', 'expires_at');

            if ($expiresEnabled && !$hasExpires) {
                $table->timestamp('expires_at')->nullable()->after($hasMinCart ? 'min_cart_total' : 'value');
            } elseif (!$expiresEnabled && $hasExpires) {
                $table->dropColumn('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
