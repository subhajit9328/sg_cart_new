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
        Schema::create('shipping_couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('support_email')->nullable();
            $table->timestamps();
        });

        Schema::create('order_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('shipping_courier_id')->nullable()->constrained('shipping_couriers')->onDelete('set null');
            $table->string('tracking_number')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_trackings');
        Schema::dropIfExists('shipping_couriers');
    }
};
