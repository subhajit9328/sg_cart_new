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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('order_number')->unique()->index();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->enum('address_type', ['office', 'work', 'other'])->default('work');
            $table->string('landmark')->nullable();
            $table->boolean('shipping_and_billing_same')->default(true);
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_zip')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('zip');
            $table->string('country')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['New Order', 'Processed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Processing'])->default('New Order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
