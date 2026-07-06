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
        Schema::create('seller_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method'); // bank_transfer, upi, cash, other
            $table->string('transaction_reference')->nullable(); // UTR/Txn ID
            $table->text('admin_notes')->nullable();
            $table->timestamp('payout_date');
            $table->timestamps();
        });

        Schema::create('seller_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->decimal('product_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('seller_earning', 10, 2);
            $table->string('status')->default('pending'); // pending, allocated, paid, cancelled
            $table->foreignId('seller_payout_id')->nullable()->constrained('seller_payouts')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_commissions');
        Schema::dropIfExists('seller_payouts');
    }
};
