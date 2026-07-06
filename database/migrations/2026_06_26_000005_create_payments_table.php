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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->string('payment_method');
                $table->decimal('amount', 10, 2);
                $table->enum('status', ['Pending', 'Paid', 'Failed'])->default('Pending');
                $table->string('transaction_id')->nullable();
                $table->string('card_name')->nullable();
                $table->string('card_number_masked')->nullable();
                $table->json('payload')->nullable(); // Optional raw JSON response metadata
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
