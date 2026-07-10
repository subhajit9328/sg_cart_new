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
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('media_path');
            $table->string('media_type'); // 'image' or 'video'
            $table->string('order_number')->nullable();
            $table->string('product_sku')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->text('caption')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
