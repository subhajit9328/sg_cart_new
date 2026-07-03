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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('phone_no')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('shop_slug')->nullable()->unique();
            $table->text('shop_description')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('pending'); // pending, approved, suspended, inactive
            $table->text('suspension_reason')->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable(); // Overrides default commission if set
            $table->text('bank_details')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
