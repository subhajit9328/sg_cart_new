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
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->string('id')->primary(); // e.g. 'cod', 'razorpay', 'authorizenet'
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_installed')->default(false);
                $table->boolean('is_enabled')->default(false);
                $table->json('config')->nullable(); // Config params (API keys, test mode, etc.)
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
