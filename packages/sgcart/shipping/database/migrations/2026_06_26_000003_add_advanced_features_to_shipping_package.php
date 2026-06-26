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
        Schema::table('shipping_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_rates', 'type')) {
                $table->string('type')->default('flat')->after('name'); // 'flat' or 'percent'
            }
        });

        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');

        Schema::table('shipping_rates', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_rates', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
