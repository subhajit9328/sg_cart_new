<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_transaction_id')->nullable()->after('payment_method');
            $table->string('payment_gateway')->nullable()->after('payment_transaction_id');
            $table->string('tracking_number')->nullable()->after('shipping_method');
            $table->string('shipping_carrier')->nullable()->after('tracking_number');
            $table->string('tracking_url')->nullable()->after('shipping_carrier');
            $table->timestamp('estimated_delivery_at')->nullable()->after('tracking_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_transaction_id',
                'payment_gateway',
                'tracking_number',
                'shipping_carrier',
                'tracking_url',
                'estimated_delivery_at',
            ]);
        });
    }
};
