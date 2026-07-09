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
        Schema::create('social_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('influencer_id')->constrained('customers')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['follower_id', 'influencer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_follows');
    }
};
